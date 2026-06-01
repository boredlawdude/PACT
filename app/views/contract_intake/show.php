<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
$sub = $submission; // alias for readability

function row(string $label, mixed $value, bool $money = false): void {
    $display = ($value !== null && $value !== '')
        ? ($money ? '$' . number_format((float)$value, 2) : htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'))
        : '<span class="text-muted">—</span>';
    echo "<div class=\"col-md-6 mb-2\"><div class=\"small text-muted\">{$label}</div><div>{$display}</div></div>";
}
?>

<div class="container py-4" style="max-width: 880px;">

  <div class="mb-3">
    <a href="/index.php?page=contract_intake_list" class="btn btn-outline-secondary btn-sm">&larr; Back to List</a>
  </div>

  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h1 class="h4 mb-0"><?= h($sub['contract_name']) ?></h1>
      <p class="text-muted small mb-0">Submission #<?= (int)$sub['submission_id'] ?> &mdash; received <?= date('F j, Y g:i a', strtotime($sub['created_at'])) ?></p>
    </div>
    <?php
      $badgeClass = match($sub['status']) {
          'pending'  => 'bg-warning text-dark',
          'imported' => 'bg-success',
          'rejected' => 'bg-secondary',
          default    => 'bg-light text-dark',
      };
    ?>
    <span class="badge <?= $badgeClass ?> fs-6"><?= ucfirst(h($sub['status'])) ?></span>
  </div>

  <!-- ── Submitter ──────────────────────────────────────────────────────────── -->
  <div class="card mb-3">
    <div class="card-header small fw-semibold text-muted">Submitted By</div>
    <div class="card-body">
      <div class="row">
        <?php row('Name',       $sub['submitter_name']); ?>
        <?php row('Email',      $sub['submitter_email']); ?>
        <?php row('Phone',      $sub['submitter_phone']); ?>
        <?php row('Department', $sub['submitter_department']); ?>
      </div>
    </div>
  </div>

  <!-- ── Contract Details ──────────────────────────────────────────────────── -->
  <div class="card mb-3">
    <div class="card-header small fw-semibold text-muted">Contract Details</div>
    <div class="card-body">
      <div class="row">
        <?php row('Contract Type',   $sub['contract_type']); ?>
        <?php row('Estimated Value', $sub['estimated_value'], true); ?>
        <?php row('Start Date',      $sub['start_date'] ? date('m/d/Y', strtotime($sub['start_date'])) : null); ?>
        <?php row('End Date',        $sub['end_date']   ? date('m/d/Y', strtotime($sub['end_date']))   : null); ?>
        <?php row('PO Number',       $sub['po_number']); ?>
        <?php row('Account Number',  $sub['account_number']); ?>
      </div>
      <?php if (!empty($sub['contract_description'])): ?>
        <div class="mt-2">
          <div class="small text-muted">Description / Scope of Work</div>
          <div class="mt-1"><?= nl2br(h($sub['contract_description'])) ?></div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Vendor / Counterparty ─────────────────────────────────────────────── -->
  <div class="card mb-3">
    <div class="card-header small fw-semibold text-muted">Vendor / Counterparty</div>
    <div class="card-body">
      <div class="row">
        <?php row('Company',       $sub['counterparty_company']); ?>
        <?php row('Contact Name',  $sub['counterparty_contact']); ?>
        <?php row('Contact Email', $sub['counterparty_email']); ?>
        <?php row('Contact Phone', $sub['counterparty_phone']); ?>
      </div>
    </div>
  </div>

  <!-- ── Authorized Signers ────────────────────────────────────────────────── -->
  <div class="card mb-3 <?= !$sub['esign_consent'] ? 'border-warning' : '' ?>">
    <div class="card-header small fw-semibold text-muted d-flex justify-content-between align-items-center">
      <span>Authorized Signers (Vendor)</span>
      <?php if ($sub['esign_consent']): ?>
        <span class="badge bg-success">E-sign consent given</span>
      <?php else: ?>
        <span class="badge bg-warning text-dark">No e-sign consent</span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <?php if (!$sub['esign_consent']): ?>
        <div class="alert alert-warning mb-3 py-2">
          <strong>&#9888; E-Sign Consent Not Confirmed</strong> — The submitter did not indicate that the vendor has consented to electronic signing. You should confirm with the vendor before sending via DocuSign.
        </div>
      <?php endif; ?>
      <?php
        $hasAnySigner = false;
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($sub['counterparty_signer'.$i.'_name']) || !empty($sub['counterparty_signer'.$i.'_email'])) {
                $hasAnySigner = true;
                break;
            }
        }
      ?>
      <?php if ($hasAnySigner): ?>
        <table class="table table-sm mb-0">
          <thead><tr><th>#</th><th>Name</th><th>Title</th><th>Email</th></tr></thead>
          <tbody>
            <?php for ($i = 1; $i <= 3; $i++):
              $sName  = $sub['counterparty_signer'.$i.'_name']  ?? '';
              $sTitle = $sub['counterparty_signer'.$i.'_title'] ?? '';
              $sEmail = $sub['counterparty_signer'.$i.'_email'] ?? '';
              if ($sName === '' && $sEmail === '') continue;
            ?>
            <tr>
              <td class="text-muted"><?= $i ?></td>
              <td><?= h($sName) ?: '<span class="text-muted">—</span>' ?></td>
              <td><?= h($sTitle) ?: '<span class="text-muted">—</span>' ?></td>
              <td><?= h($sEmail) ?: '<span class="text-muted">—</span>' ?></td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted mb-0">No signers provided.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Notes ─────────────────────────────────────────────────────────────── -->
  <?php if (!empty($sub['notes'])): ?>
  <div class="card mb-3">
    <div class="card-header small fw-semibold text-muted">Additional Notes</div>
    <div class="card-body">
      <?= nl2br(h($sub['notes'])) ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Exhibits / Attachments ───────────────────────────────────────────── -->
  <div class="card mb-3">
    <div class="card-header small fw-semibold text-muted d-flex justify-content-between align-items-center">
      <span>Attached Documents</span>
      <?php if (!empty($exhibits)): ?>
        <span class="badge bg-secondary"><?= count($exhibits) ?></span>
      <?php endif; ?>
    </div>
    <div class="card-body p-0">
      <?php if (empty($exhibits)): ?>
        <p class="text-muted px-3 py-3 mb-0">No files attached.</p>
      <?php else: ?>
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr>
              <th>Filename</th>
              <th>Size</th>
              <th>Type</th>
              <th>Scan</th>
              <th>Uploaded</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($exhibits as $ex):
              $scanBadge = match($ex['scan_status']) {
                  'clean'    => ['bg-success',   'Clean'],
                  'infected' => ['bg-danger',    'Infected'],
                  'error'    => ['bg-warning text-dark', 'Scan Error'],
                  default    => ['bg-secondary', 'Pending Scan'],
              };
              $canDownload = $ex['scan_status'] !== 'infected';
              $kb = number_format($ex['file_size'] / 1024, 1);
            ?>
            <tr>
              <td class="align-middle"><?= h($ex['original_filename']) ?></td>
              <td class="align-middle text-muted small"><?= $kb ?>&nbsp;KB</td>
              <td class="align-middle text-muted small"><?= h($ex['mime_type']) ?></td>
              <td class="align-middle">
                <span class="badge <?= $scanBadge[0] ?>"><?= $scanBadge[1] ?></span>
              </td>
              <td class="align-middle text-muted small"><?= date('m/d/Y g:i a', strtotime($ex['uploaded_at'])) ?></td>
              <td class="align-middle">
                <?php if ($canDownload): ?>
                  <a href="/index.php?page=intake_exhibit_download&id=<?= (int)$ex['exhibit_id'] ?>"
                     class="btn btn-outline-secondary btn-sm">Download</a>
                <?php else: ?>
                  <span class="text-danger small">Quarantined</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php
          $pendingCount = count(array_filter($exhibits, fn($e) => $e['scan_status'] === 'pending'));
          if ($pendingCount > 0):
        ?>
        <div class="alert alert-warning mb-0 rounded-0 rounded-bottom py-2 px-3 small">
          <strong>&#9888; <?= $pendingCount ?> file(s) have not been virus-scanned yet.</strong>
          ClamAV is not installed on this server. Install it to enable automatic scanning on upload:<br>
          <span class="text-muted">
            Ubuntu/Debian: <code>apt-get install -y clamav clamav-daemon &amp;&amp; freshclam</code><br>
            RHEL/CentOS 8+: <code>dnf install -y clamav clamd clamav-update &amp;&amp; freshclam</code><br>
            RHEL/CentOS 7: <code>yum install -y clamav clamd clamav-update &amp;&amp; freshclam</code>
          </span>
        </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Actions ───────────────────────────────────────────────────────────── -->
  <?php if ($sub['status'] === 'pending'): ?>
  <div class="card border-primary mb-3">
    <div class="card-header fw-semibold">Actions</div>
    <div class="card-body d-flex gap-3 flex-wrap">

      <!-- Import to Contract -->
      <form method="post" action="/index.php?page=contract_intake_import">
        <input type="hidden" name="submission_id" value="<?= (int)$sub['submission_id'] ?>">
        <button type="submit" class="btn btn-success"
                onclick="return confirm('This will open a new contract pre-filled with this data. Continue?')">
          Import to New Contract
        </button>
      </form>

      <!-- Reject -->
      <form method="post" action="/index.php?page=contract_intake_reject">
        <input type="hidden" name="submission_id" value="<?= (int)$sub['submission_id'] ?>">
        <button type="submit" class="btn btn-outline-danger"
                onclick="return confirm('Mark this submission as rejected?')">
          Reject
        </button>
      </form>

    </div>
  </div>
  <?php elseif ($sub['status'] === 'imported' && $sub['imported_contract_id']): ?>
  <div class="alert alert-success">
    Imported to <a href="/index.php?page=contracts_show&contract_id=<?= (int)$sub['imported_contract_id'] ?>">Contract #<?= (int)$sub['imported_contract_id'] ?></a>
    on <?= date('m/d/Y', strtotime($sub['reviewed_at'])) ?>.
  </div>
  <?php elseif ($sub['status'] === 'rejected'): ?>
  <div class="alert alert-secondary">
    Rejected on <?= date('m/d/Y', strtotime($sub['reviewed_at'])) ?>.
  </div>
  <?php endif; ?>

</div>
