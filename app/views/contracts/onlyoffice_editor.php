<?php
declare(strict_types=1);
?>

<style>
  /* Pin the editor directly to the browser viewport (position: fixed ignores the
     shared .container's max-width/padding entirely, unlike the old vw-based hack). */
  #onlyoffice-editor-topbar {
    position: relative;
    z-index: 20;
    background: #fff;
  }
  #onlyoffice-editor {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    border: none;
    background: #fff;
    z-index: 10;
  }
</style>

<div id="onlyoffice-editor-topbar" class="d-flex justify-content-between align-items-center mb-0 py-2">
  <div class="d-flex align-items-center gap-2">
    <h1 class="h6 mb-0">Inline Contract Editor</h1>
    <span class="text-muted small">&mdash; <?= h((string)($editorConfig['document']['title'] ?? 'Document')) ?></span>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span id="onlyoffice-status" class="badge bg-secondary">Initializing...</span>
    <a href="/index.php?page=contracts_show&contract_id=<?= (int)$contractId ?>" class="btn btn-outline-secondary btn-sm">Back to Contract</a>
  </div>
</div>

<div id="onlyoffice-editor"></div>

<script src="<?= h($documentServerUrl) ?>/web-apps/apps/api/documents/api.js"></script>
<script>
  function positionEditor() {
    var topbar = document.getElementById('onlyoffice-editor-topbar');
    var editor = document.getElementById('onlyoffice-editor');
    if (!topbar || !editor) return;
    var rect = topbar.getBoundingClientRect();
    editor.style.top = Math.max(0, rect.bottom) + 'px';
  }
  window.addEventListener('resize', positionEditor);
  window.addEventListener('load', positionEditor);
  positionEditor();

  const statusEl = document.getElementById('onlyoffice-status');
  function setStatus(kind, message) {
    if (!statusEl) return;
    statusEl.className = 'badge ' + (kind === 'ok' ? 'bg-success' : kind === 'warn' ? 'bg-warning text-dark' : kind === 'error' ? 'bg-danger' : 'bg-secondary');
    statusEl.textContent = message;
    if (kind === 'ok') {
      clearTimeout(setStatus._hideTimer);
      setStatus._hideTimer = setTimeout(function () {
        statusEl.style.display = 'none';
      }, 4000);
    } else {
      statusEl.style.display = '';
    }
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
