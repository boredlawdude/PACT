<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_once APP_ROOT . '/app/models/ContractIntakeSubmission.php';

// ── AJAX: vendor/company lookup for the counterparty typeahead ──────────────
// Public endpoint (this form has no login) — only exposes non-sensitive,
// already-public-facing vendor contact fields, and only for active companies.
if (($_GET['ajax'] ?? '') === 'vendor_lookup') {
    header('Content-Type: application/json; charset=utf-8');
    $q = trim((string)($_GET['q'] ?? ''));
    $results = [];
    if (strlen($q) >= 2) {
        $stmt = db()->prepare(
            "SELECT company_id, name, contact_name, email, phone
             FROM companies
             WHERE is_active = 1 AND name LIKE :q
             ORDER BY name
             LIMIT 10"
        );
        $stmt->execute([':q' => '%' . $q . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($results, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Load contract types for dropdown ─────────────────────────────────────────
$contractTypes = db()->query(
    "SELECT contract_type_id, contract_type FROM contract_types WHERE is_active = 1 ORDER BY contract_type"
)->fetchAll(PDO::FETCH_ASSOC);

// ── File upload helpers ──────────────────────────────────────────────────────
define('INTAKE_EXHIBIT_DIR', APP_ROOT . '/storage/intake_exhibits/');
define('INTAKE_EXHIBIT_MAX_BYTES', 10 * 1024 * 1024); // 10 MB
define('INTAKE_EXHIBIT_ALLOWED_MIMES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'image/gif',
    'text/plain',
]);

// The 3 upload boxes shown on the form: field name => [category key, label, max files]
define('INTAKE_EXHIBIT_FIELDS', [
    'exhibit_sow'   => ['category' => 'sow_proposal',             'label' => 'Scope of Work / Proposal',  'max' => 1],
    'exhibit_coi'   => ['category' => 'certificate_of_insurance', 'label' => 'Certificate of Insurance',  'max' => 1],
    'exhibit_other' => ['category' => 'other',                    'label' => 'Other Documents',           'max' => 2],
]);

/**
 * Validate the 3 upload fields (exhibit_sow, exhibit_coi, exhibit_other[]).
 * Returns ['errors' => [...], 'valid_files' => [['tmp_name','name','size','mime','category'], ...]]
 */
function validateIntakeExhibits(): array {
    $errors = [];
    $valid  = [];
    $finfo  = new finfo(FILEINFO_MIME_TYPE);

    foreach (INTAKE_EXHIBIT_FIELDS as $field => $cfg) {
        if (empty($_FILES[$field]) || empty($_FILES[$field]['name'])) continue;

        // Single-file inputs come back as scalars; the "other" input is multi.
        $names = (array)$_FILES[$field]['name'];
        $tmps  = (array)$_FILES[$field]['tmp_name'];
        $sizes = (array)$_FILES[$field]['size'];
        $errs  = (array)$_FILES[$field]['error'];

        $count = 0;
        foreach ($names as $i => $name) {
            if ($name === '' || $errs[$i] === UPLOAD_ERR_NO_FILE) continue;
            $count++;
        }
        if ($count > $cfg['max']) {
            $errors[] = 'You may attach a maximum of ' . $cfg['max'] . ' file(s) for "' . $cfg['label'] . '".';
            continue;
        }

        foreach ($names as $i => $name) {
            if ($name === '' || $errs[$i] === UPLOAD_ERR_NO_FILE) continue;
            $safeName = htmlspecialchars(basename($name), ENT_QUOTES, 'UTF-8');
            if ($errs[$i] !== UPLOAD_ERR_OK) {
                $errors[] = "Upload error for \u201c{$safeName}\u201d (code {$errs[$i]}).";
                continue;
            }
            if ($sizes[$i] > INTAKE_EXHIBIT_MAX_BYTES) {
                $errors[] = "\u201c{$safeName}\u201d exceeds the 10\u00a0MB size limit.";
                continue;
            }
            $mime = $finfo->file($tmps[$i]);
            if (!in_array($mime, INTAKE_EXHIBIT_ALLOWED_MIMES, true)) {
                $errors[] = "\u201c{$safeName}\u201d is not an allowed file type ({$mime}).";
                continue;
            }
            $valid[] = [
                'tmp_name' => $tmps[$i],
                'name'     => $name,
                'size'     => $sizes[$i],
                'mime'     => $mime,
                'category' => $cfg['category'],
            ];
        }
    }
    return ['errors' => $errors, 'valid_files' => $valid];
}

/**
 * Run ClamAV on a file. Returns [status, output_text].
 * status: 'clean' | 'infected' | 'error' | 'pending'
 * Tries clamdscan (daemon, fast) first, then falls back to clamscan (CLI).
 */
function scanFileWithClamAV(string $path): array {
    // Try clamdscan (daemon) first — preferred on Linux servers
    $which = [];
    exec('which clamdscan 2>/dev/null', $which, $wc);
    if ($wc === 0 && !empty($which[0])) {
        $scanner = trim($which[0]);
        $out = [];
        exec(escapeshellcmd($scanner) . ' --no-summary --stdout ' . escapeshellarg($path) . ' 2>&1', $out, $ec);
        $output = implode("\n", $out);
        if ($ec === 0) return ['clean',    $output];
        if ($ec === 1) return ['infected', $output];
        // ec=2 means daemon not running — fall through to clamscan
        if ($ec !== 2) return ['error',    $output];
    }
    // Fall back to clamscan (standalone CLI)
    $which2 = [];
    exec('which clamscan 2>/dev/null', $which2, $wc2);
    if ($wc2 === 0 && !empty($which2[0])) {
        $scanner = trim($which2[0]);
        $out = [];
        exec(escapeshellcmd($scanner) . ' --no-summary --stdout ' . escapeshellarg($path) . ' 2>&1', $out, $ec);
        $output = implode("\n", $out);
        if ($ec === 0) return ['clean',    $output];
        if ($ec === 1) return ['infected', $output];
        return               ['error',    $output];
    }
    return ['pending', 'ClamAV not installed — file awaiting scan.'];
}

/**
 * Move validated upload to storage dir, scan it, insert DB record.
 */
function saveIntakeExhibit(int $submissionId, array $file, PDO $db): void {
    $storedName = bin2hex(random_bytes(16)) . '.bin';
    $dest = INTAKE_EXHIBIT_DIR . $storedName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return; // silently skip — tmp file already gone or dir unwritable
    }
    [$scanStatus, $scanOutput] = scanFileWithClamAV($dest);
    $db->prepare("
        INSERT INTO contract_intake_exhibits
            (submission_id, original_filename, stored_filename, file_size, mime_type, doc_category, scan_status, scan_output)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([$submissionId, basename($file['name']), $storedName, $file['size'], $file['mime'], $file['category'], $scanStatus, $scanOutput]);
}

// ── Handle submission ─────────────────────────────────────────────────────────
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot check
    if (!empty($_POST['_hp_field'])) {
        $success = true; // silent discard
    } else {
        $submitterName  = trim((string)($_POST['submitter_name']  ?? ''));
        $submitterEmail = trim((string)($_POST['submitter_email'] ?? ''));
        $contractName   = trim((string)($_POST['contract_name']   ?? ''));

        if ($submitterName  === '') $errors[] = 'Your name is required.';
        if ($submitterEmail === '') $errors[] = 'Your email address is required.';
        elseif (!filter_var($submitterEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if ($contractName   === '') $errors[] = 'Contract / project name is required.';

        // Validate counterparty email if provided
        $counterpartyEmail = trim((string)($_POST['counterparty_email'] ?? ''));
        if ($counterpartyEmail !== '' && !filter_var($counterpartyEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Vendor/counterparty email address is not valid.';
        }

        // Validate signer emails if provided
        for ($i = 1; $i <= 3; $i++) {
            $se = trim((string)($_POST['counterparty_signer'.$i.'_email'] ?? ''));
            if ($se !== '' && !filter_var($se, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Signer ' . $i . ' email address is not valid.';
            }
        }

        $esignConsent = isset($_POST['esign_consent']) ? 1 : 0;

        // Sanitize numeric fields
        $estimatedValue = null;
        $rawValue = trim((string)($_POST['estimated_value'] ?? ''));
        if ($rawValue !== '') {
            $cleaned = preg_replace('/[^0-9.]/', '', $rawValue);
            if (is_numeric($cleaned)) $estimatedValue = (float)$cleaned;
        }

        $contractTypeId = null;
        $rawType = trim((string)($_POST['contract_type_id'] ?? ''));
        if ($rawType !== '' && ctype_digit($rawType)) $contractTypeId = (int)$rawType;

        $startDate = null;
        $rawStart = trim((string)($_POST['start_date'] ?? ''));
        if ($rawStart !== '' && strtotime($rawStart) !== false) $startDate = $rawStart;

        $endDate = null;
        $rawEnd = trim((string)($_POST['end_date'] ?? ''));
        if ($rawEnd !== '' && strtotime($rawEnd) !== false) $endDate = $rawEnd;

        // Validate file uploads
        $uploadResult  = validateIntakeExhibits();
        $errors        = array_merge($errors, $uploadResult['errors']);
        $validUploads  = $uploadResult['valid_files'];

        if (empty($errors)) {
            $model = new ContractIntakeSubmission(db());
            $submissionId = $model->create([
                'submitter_name'       => $submitterName,
                'submitter_email'      => $submitterEmail,
                'submitter_phone'      => trim((string)($_POST['submitter_phone']      ?? '')),
                'submitter_department' => trim((string)($_POST['submitter_department'] ?? '')),
                'contract_name'        => $contractName,
                'contract_description' => trim((string)($_POST['contract_description'] ?? '')),
                'contract_type_id'     => $contractTypeId,
                'counterparty_company' => trim((string)($_POST['counterparty_company'] ?? '')),
                'counterparty_contact' => trim((string)($_POST['counterparty_contact'] ?? '')),
                'counterparty_email'   => $counterpartyEmail,
                'counterparty_phone'   => trim((string)($_POST['counterparty_phone']   ?? '')),
                'estimated_value'      => $estimatedValue,
                'start_date'           => $startDate,
                'end_date'             => $endDate,
                'po_number'            => substr(trim((string)($_POST['po_number']      ?? '')), 0, 20),
                'account_number'       => substr(trim((string)($_POST['account_number'] ?? '')), 0, 20),
                'notes'                => trim((string)($_POST['notes'] ?? '')),
                'counterparty_signer1_name'  => substr(trim((string)($_POST['counterparty_signer1_name']  ?? '')), 0, 100),
                'counterparty_signer1_title' => substr(trim((string)($_POST['counterparty_signer1_title'] ?? '')), 0, 100),
                'counterparty_signer1_email' => trim((string)($_POST['counterparty_signer1_email'] ?? '')),
                'counterparty_signer2_name'  => substr(trim((string)($_POST['counterparty_signer2_name']  ?? '')), 0, 100),
                'counterparty_signer2_title' => substr(trim((string)($_POST['counterparty_signer2_title'] ?? '')), 0, 100),
                'counterparty_signer2_email' => trim((string)($_POST['counterparty_signer2_email'] ?? '')),
                'counterparty_signer3_name'  => substr(trim((string)($_POST['counterparty_signer3_name']  ?? '')), 0, 100),
                'counterparty_signer3_title' => substr(trim((string)($_POST['counterparty_signer3_title'] ?? '')), 0, 100),
                'counterparty_signer3_email' => trim((string)($_POST['counterparty_signer3_email'] ?? '')),
                'esign_consent'        => $esignConsent,
            ]);
            // Save any uploaded exhibits
            foreach ($validUploads as $upload) {
                saveIntakeExhibit($submissionId, $upload, db());
            }
            $success = true;
        }
    }
}

$appName = defined('APP_NAME') ? APP_NAME : 'Contracts';

if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$old = (!$success && $_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Contract Request — <?= h($appName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; }
    .intake-header { background: linear-gradient(90deg, #1e3a5f, #2c5d8a); color: #fff; padding: 1.2rem 0; margin-bottom: 2rem; }
    .intake-header h1 { font-size: 1.4rem; font-weight: 600; margin: 0; }
    .intake-header p  { margin: 0; font-size: .9rem; opacity: .85; }
    .section-label { font-size: .7rem; text-transform: uppercase; font-weight: 600; letter-spacing: .05em; color: #6c757d; border-bottom: 1px solid #dee2e6; padding-bottom: .4rem; margin-bottom: 1rem; }
  </style>
</head>
<body>

<div class="intake-header">
  <div class="container">
    <h1><?= h($appName) ?> — Contract Request</h1>
    <p>Complete this form to submit a contract request for Town staff to review and process.</p>
  </div>
</div>

<div class="container mb-5" style="max-width:860px">

<?php if ($success): ?>
  <div class="card shadow-sm border-success mb-5">
    <div class="card-body text-center py-5">
      <div class="display-3 mb-3">✓</div>
      <h2 class="h4 text-success mb-2">Request Submitted</h2>
      <p class="text-muted mb-0">Thank you. Your contract request has been received and will be reviewed by Town staff. You will be contacted if additional information is needed.</p>
    </div>
  </div>
<?php else: ?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form method="post" action="/contract_intake.php" enctype="multipart/form-data">

  <!-- Honeypot -->
  <div style="display:none" aria-hidden="true">
    <input type="text" name="_hp_field" tabindex="-1" autocomplete="off">
  </div>

  <!-- ── Your Information ──────────────────────────────────────────────────── -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <p class="section-label">Your Information</p>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Your Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="submitter_name" required maxlength="100"
                 value="<?= h($old['submitter_name'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Your Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control" name="submitter_email" required maxlength="200"
                 value="<?= h($old['submitter_email'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input type="tel" class="form-control" name="submitter_phone" maxlength="30"
                 value="<?= h($old['submitter_phone'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Department</label>
          <input type="text" class="form-control" name="submitter_department" maxlength="200"
                 placeholder="e.g. Public Works, Finance…"
                 value="<?= h($old['submitter_department'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- ── Contract Information ──────────────────────────────────────────────── -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <p class="section-label">Contract Information</p>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Contract / Project Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="contract_name" required maxlength="200"
                 value="<?= h($old['contract_name'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Description / Scope of Work</label>
          <textarea class="form-control" name="contract_description" rows="3" maxlength="2000"><?= h($old['contract_description'] ?? '') ?></textarea>
          <div class="form-text">Briefly describe what services, goods, or work this contract covers.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Contract Type</label>
          <select class="form-select" name="contract_type_id">
            <option value="">— Select if known —</option>
            <?php foreach ($contractTypes as $ct): ?>
              <option value="<?= h((string)$ct['contract_type_id']) ?>"
                <?= ($old['contract_type_id'] ?? '') == $ct['contract_type_id'] ? 'selected' : '' ?>>
                <?= h($ct['contract_type']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Estimated Value ($)</label>
          <input type="text" class="form-control" name="estimated_value" maxlength="20"
                 placeholder="e.g. 25000"
                 value="<?= h($old['estimated_value'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Anticipated Start Date</label>
          <input type="date" class="form-control" name="start_date"
                 value="<?= h($old['start_date'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Anticipated End Date</label>
          <input type="date" class="form-control" name="end_date"
                 value="<?= h($old['end_date'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">PO Number <span class="text-muted small">(if known)</span></label>
          <input type="text" class="form-control" name="po_number" maxlength="20"
                 value="<?= h($old['po_number'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Account Number <span class="text-muted small">(if known)</span></label>
          <input type="text" class="form-control" name="account_number" maxlength="20"
                 value="<?= h($old['account_number'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- ── Vendor / Counterparty ─────────────────────────────────────────────── -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <p class="section-label">Vendor / Counterparty</p>
      <div class="row g-3">
        <div class="col-md-6 position-relative">
          <label class="form-label">Company Name</label>
          <input type="text" class="form-control" name="counterparty_company" id="counterparty_company" maxlength="200"
                 autocomplete="off"
                 value="<?= h($old['counterparty_company'] ?? '') ?>">
          <div id="vendorLookupResults" class="list-group shadow-sm" style="display:none; position:absolute; z-index:1050; width:100%; max-height:220px; overflow-y:auto;"></div>
          <div class="form-text">Start typing to search existing vendors, or enter a new company name.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Contact Name</label>
          <input type="text" class="form-control" name="counterparty_contact" id="counterparty_contact" maxlength="100"
                 value="<?= h($old['counterparty_contact'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Contact Email</label>
          <input type="email" class="form-control" name="counterparty_email" id="counterparty_email" maxlength="200"
                 value="<?= h($old['counterparty_email'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Contact Phone</label>
          <input type="tel" class="form-control" name="counterparty_phone" id="counterparty_phone" maxlength="30"
                 value="<?= h($old['counterparty_phone'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- ── Authorized Signers ────────────────────────────────────────────────── -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <p class="section-label">Authorized Signers (Vendor / Counterparty)</p>
      <p class="text-muted small mb-3">Provide the name(s) and title(s) of the person(s) who will sign this contract on behalf of the vendor. At least one signer is recommended if you know who will sign.</p>
      <?php foreach ([1, 2, 3] as $n): ?>
      <div class="row g-2 mb-3 align-items-end">
        <div class="col-12 mb-1" style="color:#6c757d;font-size:.8rem;font-weight:600;">Signer <?= $n ?><?= $n === 1 ? ' (Primary)' : ' <span class="fw-normal">(optional)</span>' ?></div>
        <div class="col-md-4">
          <label class="form-label form-label-sm">Full Name</label>
          <input type="text" class="form-control form-control-sm" name="counterparty_signer<?= $n ?>_name" maxlength="100"
                 value="<?= h($old['counterparty_signer'.$n.'_name'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label form-label-sm">Title / Role</label>
          <input type="text" class="form-control form-control-sm" name="counterparty_signer<?= $n ?>_title" maxlength="100"
                 placeholder="e.g. CEO, President, Owner"
                 value="<?= h($old['counterparty_signer'.$n.'_title'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label form-label-sm">Email</label>
          <input type="email" class="form-control form-control-sm" name="counterparty_signer<?= $n ?>_email" maxlength="200"
                 value="<?= h($old['counterparty_signer'.$n.'_email'] ?? '') ?>">
        </div>
      </div>
      <?php endforeach; ?>

      <div class="mt-3 pt-3 border-top">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="esign_consent" name="esign_consent" value="1"
                 <?= !empty($old['esign_consent']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="esign_consent">
            <strong>The vendor/counterparty has indicated they consent to electronic signing (DocuSign).</strong>
          </label>
        </div>
        <div class="form-text mt-1 text-muted">If the vendor has not yet confirmed they can sign electronically, leave unchecked and staff will follow up.</div>
      </div>
    </div>
  </div>

  <!-- ── Additional Notes ──────────────────────────────────────────────────── -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <p class="section-label">Additional Notes</p>
      <textarea class="form-control" name="notes" rows="4" maxlength="3000"
                placeholder="Any other relevant details — deadlines, related bids, documents you plan to provide, etc."><?= h($old['notes'] ?? '') ?></textarea>
    </div>
  </div>

  <!-- ── Exhibits / Attachments ────────────────────────────────────────────── -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <p class="section-label">Supporting Documents <span class="fw-normal text-muted">(optional)</span></p>
      <p class="text-muted small mb-3">
        Accepted formats: PDF, Word, Excel, images, or plain text &mdash; max 10&nbsp;MB each.
      </p>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Scope of Work / Proposal</label>
          <input class="form-control" type="file" name="exhibit_sow" id="exhibit_sow"
                 accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt">
          <div class="form-text">1 file</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Certificate of Insurance</label>
          <input class="form-control" type="file" name="exhibit_coi" id="exhibit_coi"
                 accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt">
          <div class="form-text">1 file</div>
        </div>
        <div class="col-12">
          <label class="form-label">Other Documents</label>
          <input class="form-control" type="file" name="exhibit_other[]" id="exhibit_other"
                 multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt">
          <div class="form-text mt-1">Up to 2 additional files. Hold <kbd>Ctrl</kbd> (Windows) or <kbd>&#8984;</kbd> (Mac) to select multiple files at once.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 mb-5">
    <button type="submit" class="btn btn-primary px-4" id="submitBtn">
      <span id="submitSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
      <span id="submitBtnText">Submit Contract Request</span>
    </button>
  </div>

</form>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function () {
    var form = document.querySelector('form[action="/contract_intake.php"]');
    if (!form) return;
    form.addEventListener('submit', function () {
      // Let native required/validation checks run first; if the form is
      // actually invalid the browser won't fire this 'submit' event at all.
      var btn = document.getElementById('submitBtn');
      var spinner = document.getElementById('submitSpinner');
      var text = document.getElementById('submitBtnText');
      if (!btn) return;
      btn.disabled = true;
      if (spinner) spinner.classList.remove('d-none');
      if (text) text.textContent = 'Submitting\u2026';
    });
  })();
</script>
<script>
  (function () {
    var input   = document.getElementById('counterparty_company');
    var results = document.getElementById('vendorLookupResults');
    var contactInput = document.getElementById('counterparty_contact');
    var emailInput   = document.getElementById('counterparty_email');
    var phoneInput   = document.getElementById('counterparty_phone');
    if (!input || !results) return;

    var debounceTimer = null;
    var activeRequest = null;

    function hideResults() {
      results.style.display = 'none';
      results.innerHTML = '';
    }

    function renderResults(vendors) {
      results.innerHTML = '';
      if (!vendors.length) { hideResults(); return; }
      vendors.forEach(function (v) {
        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action py-2';
        item.textContent = v.name;
        item.addEventListener('click', function () {
          input.value = v.name;
          if (contactInput && !contactInput.value && v.contact_name) contactInput.value = v.contact_name;
          if (emailInput && !emailInput.value && v.email) emailInput.value = v.email;
          if (phoneInput && !phoneInput.value && v.phone) phoneInput.value = v.phone;
          hideResults();
        });
        results.appendChild(item);
      });
      results.style.display = 'block';
    }

    input.addEventListener('input', function () {
      var q = input.value.trim();
      clearTimeout(debounceTimer);
      if (q.length < 2) { hideResults(); return; }
      debounceTimer = setTimeout(function () {
        if (activeRequest) activeRequest.abort();
        var controller = new AbortController();
        activeRequest = controller;
        fetch('/contract_intake.php?ajax=vendor_lookup&q=' + encodeURIComponent(q), { signal: controller.signal })
          .then(function (r) { return r.json(); })
          .then(renderResults)
          .catch(function (err) { if (err.name !== 'AbortError') hideResults(); });
      }, 250);
    });

    document.addEventListener('click', function (e) {
      if (e.target !== input && !results.contains(e.target)) hideResults();
    });
  })();
</script>
</body>
</html>
