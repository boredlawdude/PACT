<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$dsFlashError   = $_SESSION['docusign_flash_error']   ?? null;
$dsFlashSuccess = $_SESSION['docusign_flash_success'] ?? null;
unset($_SESSION['docusign_flash_error'], $_SESSION['docusign_flash_success']);

$contractLabel = trim((string)($contract['contract_name'] ?? ''));
if (!empty($contract['contract_number'])) {
    $contractLabel .= ' (' . $contract['contract_number'] . ')';
}

$statusOptions = [
    'completed' => 'Completed',
    'sent'      => 'Sent',
    'delivered' => 'Delivered',
    'declined'  => 'Declined',
    'voided'    => 'Voided',
    'any'       => 'Any status',
];
?>

<div class="container py-4" style="max-width: 1000px;">

  <div class="mb-3">
    <a href="/index.php?page=contracts_show&contract_id=<?= (int)$contract['contract_id'] ?>" class="btn btn-outline-secondary btn-sm">&larr; Back to Contract</a>
  </div>

  <h1 class="h4 mb-1">Import a Signed Document from DocuSign</h1>
  <p class="text-muted mb-4">
    Contract: <strong><?= h($contractLabel) ?></strong>
  </p>

  <?php if ($dsFlashError !== null): ?>
    <div class="alert alert-danger"><?= h($dsFlashError) ?></div>
  <?php endif; ?>
  <?php if ($dsFlashSuccess !== null): ?>
    <div class="alert alert-success"><?= h($dsFlashSuccess) ?></div>
  <?php endif; ?>
  <?php if (!empty($searchError)): ?>
    <div class="alert alert-danger"><?= h($searchError) ?></div>
  <?php endif; ?>

  <form method="get" action="/index.php" class="card card-body mb-4">
    <input type="hidden" name="page" value="docusign_import_search">
    <input type="hidden" name="search" value="1">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small fw-semibold" for="search_text">Search text</label>
        <input type="text" class="form-control" id="search_text" name="search_text"
               value="<?= h($searchText) ?>" placeholder="Vendor name, subject, recipient email…">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold" for="status">Status</label>
        <select class="form-select" id="status" name="status">
          <?php foreach ($statusOptions as $val => $label): ?>
            <option value="<?= h($val) ?>" <?= $statusFilter === $val ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold" for="from_date">From date</label>
        <input type="date" class="form-control" id="from_date" name="from_date" value="<?= h($fromDate) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold" for="to_date">To date</label>
        <input type="date" class="form-control" id="to_date" name="to_date" value="<?= h($toDate) ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Search DocuSign</button>
      </div>
    </div>
    <div class="form-text mt-2">Searches your DocuSign account's envelopes (sender or recipient). Leave "Search text" blank to list recent envelopes.</div>
  </form>

  <?php if ($didSearch): ?>
    <?php if (empty($envelopes)): ?>
      <div class="text-muted">No envelopes matched your search.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Subject</th>
              <th>Status</th>
              <th>Sent</th>
              <th>Completed</th>
              <th>Recipients</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($envelopes as $env): ?>
              <?php
                $envelopeId = (string)($env['envelopeId'] ?? '');
                $status     = (string)($env['status'] ?? '');
                $badgeMap   = [
                    'completed' => 'success',
                    'sent'      => 'warning',
                    'delivered' => 'info',
                    'declined'  => 'danger',
                    'voided'    => 'secondary',
                ];
                $badge = $badgeMap[$status] ?? 'light';

                $recipientNames = [];
                foreach ((array)($env['recipients']['signers'] ?? []) as $signer) {
                    $rn = trim((string)($signer['name'] ?? ''));
                    if ($rn !== '') $recipientNames[] = $rn;
                }
              ?>
              <tr>
                <td><?= h($env['emailSubject'] ?? '—') ?></td>
                <td><span class="badge text-bg-<?= h($badge) ?>"><?= h(ucfirst($status)) ?></span></td>
                <td><?= h(substr((string)($env['sentDateTime'] ?? ''), 0, 10)) ?></td>
                <td><?= h(substr((string)($env['completedDateTime'] ?? ''), 0, 10)) ?></td>
                <td class="small"><?= h(implode(', ', $recipientNames)) ?></td>
                <td class="text-end">
                  <a class="btn btn-outline-primary btn-sm"
                     href="/index.php?page=docusign_import_documents&envelope_id=<?= urlencode($envelopeId) ?>">
                    View Documents
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>
