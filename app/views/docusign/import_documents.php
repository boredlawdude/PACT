<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$dsFlashError = $_SESSION['docusign_flash_error'] ?? null;
unset($_SESSION['docusign_flash_error']);

$contractId    = (int)($contract['contract_id'] ?? 0);
$contractLabel = trim((string)($contract['contract_name'] ?? ''));
if (!empty($contract['contract_number'])) {
    $contractLabel .= ' (' . $contract['contract_number'] . ')';
}

$envelopeId   = (string)($envelope['envelopeId']   ?? '');
$emailSubject = (string)($envelope['emailSubject'] ?? '');
$envStatus    = (string)($envelope['status']       ?? '');
?>

<div class="container py-4" style="max-width: 900px;">

  <div class="mb-3">
    <a href="/index.php?page=docusign_import_search" class="btn btn-outline-secondary btn-sm">&larr; Back to Search</a>
  </div>

  <h1 class="h4 mb-1">Choose a Document to Import</h1>
  <p class="text-muted mb-4">
    Contract: <strong><?= h($contractLabel) ?></strong><br>
    Envelope: <strong><?= h($emailSubject ?: $envelopeId) ?></strong>
    &mdash; Status: <span class="badge text-bg-secondary"><?= h(ucfirst($envStatus)) ?></span>
  </p>

  <?php if ($dsFlashError !== null): ?>
    <div class="alert alert-danger"><?= h($dsFlashError) ?></div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <strong>Combined PDF (recommended)</strong>
        <div class="text-muted small">All documents in this envelope merged into a single signed PDF.</div>
      </div>
      <form method="post" action="/index.php?page=docusign_import_confirm">
        <input type="hidden" name="contract_id" value="<?= $contractId ?>">
        <input type="hidden" name="envelope_id" value="<?= h($envelopeId) ?>">
        <input type="hidden" name="document_id" value="combined">
        <input type="hidden" name="description" value="<?= h($emailSubject) ?>">
        <button type="submit" class="btn btn-primary btn-sm">Import Combined PDF</button>
      </form>
    </div>
  </div>

  <?php if (!empty($docs)): ?>
    <p class="fw-semibold">Or import an individual document:</p>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($docs as $d): ?>
            <?php $docId = (string)($d['documentId'] ?? ''); ?>
            <tr>
              <td><?= h($d['name'] ?? '—') ?></td>
              <td><?= h($d['type'] ?? '—') ?></td>
              <td class="text-end">
                <form method="post" action="/index.php?page=docusign_import_confirm">
                  <input type="hidden" name="contract_id" value="<?= $contractId ?>">
                  <input type="hidden" name="envelope_id" value="<?= h($envelopeId) ?>">
                  <input type="hidden" name="document_id" value="<?= h($docId) ?>">
                  <input type="hidden" name="description" value="<?= h($d['name'] ?? $emailSubject) ?>">
                  <button type="submit" class="btn btn-outline-primary btn-sm">Import</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
