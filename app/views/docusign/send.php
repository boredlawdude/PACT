<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$dsFlashError   = $_SESSION['docusign_flash_error']   ?? null;
$dsFlashSuccess = $_SESSION['docusign_flash_success'] ?? null;
unset($_SESSION['docusign_flash_error'], $_SESSION['docusign_flash_success']);

$docId      = (int)($doc['contract_document_id'] ?? 0);
$contractId = (int)($doc['contract_id']          ?? 0);

// Pre-populate first signer from counterparty contact
$defaultSignerName  = trim((string)($doc['counterparty_name']  ?? ''));
$defaultSignerEmail = trim((string)($doc['counterparty_email'] ?? ''));

$contractLabel = trim((string)($doc['contract_name'] ?? ''));
if (!empty($doc['contract_number'])) {
    $contractLabel .= ' (' . $doc['contract_number'] . ')';
}
$defaultSubject = 'Please sign: ' . ($doc['file_name'] ?? 'Contract Document');
?>

<div class="container py-4" style="max-width: 760px;">

  <div class="mb-3">
    <a href="/index.php?page=contracts_show&contract_id=<?= $contractId ?>" class="btn btn-outline-secondary btn-sm">&larr; Back to Contract</a>
  </div>

  <h1 class="h4 mb-1">Send for Signature via DocuSign</h1>
  <p class="text-muted mb-4">
    Document: <strong><?= h($doc['file_name'] ?? '—') ?></strong>
    <?php if ($contractLabel !== ''): ?>
      &mdash; Contract: <strong><?= h($contractLabel) ?></strong>
    <?php endif; ?>
  </p>

  <?php if ($dsFlashError !== null): ?>
    <div class="alert alert-danger"><?= h($dsFlashError) ?></div>
  <?php endif; ?>
  <?php if ($dsFlashSuccess !== null): ?>
    <div class="alert alert-success"><?= h($dsFlashSuccess) ?></div>
  <?php endif; ?>

  <form method="post" action="/index.php?page=docusign_send_envelope" id="ds-send-form">

    <!-- Email Subject -->
    <div class="mb-4">
      <label class="form-label fw-semibold" for="email_subject">Email Subject</label>
      <input type="text" class="form-control" id="email_subject" name="email_subject"
             value="<?= h($defaultSubject) ?>" maxlength="200" required>
      <div class="form-text">This subject line is shown in the DocuSign email sent to all signers.</div>
    </div>

    <!-- Signers -->
    <div class="mb-2 d-flex justify-content-between align-items-center">
      <span class="fw-semibold">Signers</span>
      <button type="button" class="btn btn-outline-secondary btn-sm" id="add-signer-btn">+ Add Signer</button>
    </div>
    <div class="form-text mb-3">Signers receive the document in the order listed. Drag to reorder (or use the move buttons).</div>

    <div id="signers-container">

      <!-- Signer row template (filled in by JS when "Add Signer" is clicked) -->
      <template id="signer-row-template">
        <div class="card mb-2 signer-row">
          <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-center">
              <div class="col-auto text-muted signer-num fw-bold" style="min-width:28px;"></div>
              <div class="col">
                <input type="text" class="form-control form-control-sm" name="signer_name[]"
                       placeholder="Full Name" maxlength="100" required>
              </div>
              <div class="col">
                <input type="email" class="form-control form-control-sm" name="signer_email[]"
                       placeholder="email@example.com" maxlength="200" required>
              </div>
              <div class="col-auto">
                <button type="button" class="btn btn-outline-danger btn-sm remove-signer-btn" title="Remove signer">&times;</button>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- First signer pre-populated from counterparty contact -->
      <div class="card mb-2 signer-row">
        <div class="card-body py-2 px-3">
          <div class="row g-2 align-items-center">
            <div class="col-auto text-muted signer-num fw-bold" style="min-width:28px;">1</div>
            <div class="col">
              <input type="text" class="form-control form-control-sm" name="signer_name[]"
                     value="<?= h($defaultSignerName) ?>" placeholder="Full Name" maxlength="100" required>
            </div>
            <div class="col">
              <input type="email" class="form-control form-control-sm" name="signer_email[]"
                     value="<?= h($defaultSignerEmail) ?>" placeholder="email@example.com" maxlength="200" required>
            </div>
            <div class="col-auto">
              <button type="button" class="btn btn-outline-danger btn-sm remove-signer-btn" title="Remove signer"
                      <?= ($defaultSignerName !== '' || $defaultSignerEmail !== '') ? '' : '' ?>>
                &times;
              </button>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /#signers-container -->

    <div class="alert alert-info mt-3 mb-4 small">
      <strong>Tip:</strong> To position signature fields precisely in your document, add the placeholder text
      <code>**signature_1**</code>, <code>**signature_2**</code>, etc. where you want each signer's signature tab to appear.
      If no placeholder is found, DocuSign will place the tab at the end of the document.
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-success">Send Envelope</button>
      <a href="/index.php?page=contracts_show&contract_id=<?= $contractId ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>

  </form>

</div>

<script>
(function () {
    const container = document.getElementById('signers-container');
    const template  = document.getElementById('signer-row-template');
    const addBtn    = document.getElementById('add-signer-btn');

    function renumberRows() {
        const rows = container.querySelectorAll('.signer-row');
        rows.forEach((row, idx) => {
            const numEl = row.querySelector('.signer-num');
            if (numEl) numEl.textContent = String(idx + 1);
        });
    }

    function attachRemoveHandler(row) {
        const btn = row.querySelector('.remove-signer-btn');
        if (!btn) return;
        btn.addEventListener('click', () => {
            if (container.querySelectorAll('.signer-row').length <= 1) return; // always keep at least 1
            row.remove();
            renumberRows();
        });
    }

    // Attach to existing first row
    container.querySelectorAll('.signer-row').forEach(attachRemoveHandler);

    addBtn.addEventListener('click', () => {
        const clone = template.content.cloneNode(true);
        const row   = clone.querySelector('.signer-row');
        attachRemoveHandler(row);
        container.appendChild(clone);
        renumberRows();
        // Focus the name field of the new row
        const nameInput = container.querySelector('.signer-row:last-child input[name="signer_name[]"]');
        if (nameInput) nameInput.focus();
    });
})();
</script>
