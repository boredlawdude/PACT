<?php
declare(strict_types=1);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h4 mb-1">Inline Contract Editor</h1>
    <div class="text-muted small">OnlyOffice editing for <?= h((string)($editorConfig['document']['title'] ?? 'Document')) ?></div>
  </div>
  <a href="/index.php?page=contracts_show&contract_id=<?= (int)$contractId ?>" class="btn btn-outline-secondary btn-sm">Back to Contract</a>
</div>

<div class="alert alert-info py-2 small">
  Save inside the editor. Changes are written back to your stored contract document via callback.
</div>

<div id="onlyoffice-status" class="alert alert-secondary py-2 small">
  Initializing OnlyOffice editor...
</div>

<div id="onlyoffice-editor" style="height: calc(100vh - 260px); min-height: 640px; border: 1px solid #dce3ea; border-radius: 8px; overflow: hidden;"></div>

<script src="<?= h($documentServerUrl) ?>/web-apps/apps/api/documents/api.js"></script>
<script>
  const statusEl = document.getElementById('onlyoffice-status');
  function setStatus(kind, message) {
    if (!statusEl) return;
    statusEl.className = 'alert py-2 small ' + (kind === 'ok' ? 'alert-success' : kind === 'warn' ? 'alert-warning' : kind === 'error' ? 'alert-danger' : 'alert-secondary');
    statusEl.textContent = message;
  }

  const cfg = <?= json_encode($editorConfig, JSON_UNESCAPED_SLASHES) ?>;
  <?php if (!empty($editorToken)): ?>
  cfg.token = <?= json_encode($editorToken, JSON_UNESCAPED_SLASHES) ?>;
  <?php endif; ?>

  cfg.events = Object.assign({}, cfg.events || {}, {
    onAppReady: function () {
      setStatus('ok', 'OnlyOffice editor is ready.');
    },
    onDocumentReady: function () {
      setStatus('ok', 'Document loaded successfully.');
    },
    onError: function (event) {
      const code = event && typeof event.data !== 'undefined' ? event.data.errorCode : 'unknown';
      const desc = event && event.data && event.data.errorDescription ? event.data.errorDescription : 'No description from OnlyOffice.';
      setStatus('error', 'OnlyOffice error ' + code + ': ' + desc);
      console.error('OnlyOffice onError', event);
    }
  });

  window.addEventListener('error', function (e) {
    setStatus('error', 'Browser error: ' + (e.message || 'Unknown JavaScript error'));
  });

  if (typeof DocsAPI === 'undefined' || typeof DocsAPI.DocEditor !== 'function') {
    setStatus('error', 'OnlyOffice API script did not load. Check ONLYOFFICE_DOCUMENT_SERVER_URL and office proxy.');
    console.error('DocsAPI is unavailable.');
  } else {
    try {
      setStatus('warn', 'OnlyOffice API loaded. Starting editor...');
      new DocsAPI.DocEditor('onlyoffice-editor', cfg);
    } catch (err) {
      setStatus('error', 'Editor startup failed: ' + (err && err.message ? err.message : 'Unknown error'));
      console.error('OnlyOffice startup exception', err);
    }
  }
</script>
