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

<div id="onlyoffice-editor" style="height: calc(100vh - 260px); min-height: 640px; border: 1px solid #dce3ea; border-radius: 8px; overflow: hidden;"></div>

<script src="<?= h($documentServerUrl) ?>/web-apps/apps/api/documents/api.js"></script>
<script>
  const cfg = <?= json_encode($editorConfig, JSON_UNESCAPED_SLASHES) ?>;
  <?php if (!empty($editorToken)): ?>
  cfg.token = <?= json_encode($editorToken, JSON_UNESCAPED_SLASHES) ?>;
  <?php endif; ?>

  new DocsAPI.DocEditor('onlyoffice-editor', cfg);
</script>
