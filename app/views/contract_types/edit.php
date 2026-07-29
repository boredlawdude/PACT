<?php
declare(strict_types=1);

if (!function_exists('h')) {
    function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1"><?= h($contractType['contract_type']) ?></h1>
      <p class="text-muted mb-0">Manage templates for this contract type</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/index.php?page=contract_types" class="btn btn-outline-secondary btn-sm">Back to Types</a>
      <a href="/index.php?page=merge_field_reference" class="btn btn-outline-info btn-sm">Merge Field Reference</a>
    </div>
  </div>

  <?php if (!empty($flashMessages)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php foreach ($flashMessages as $msg): ?>
        <div><?= h($msg) ?></div>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($flashErrors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        <?php foreach ($flashErrors as $err): ?>
          <li><?= h($err) ?></li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form method="post" action="/index.php?page=contract_types_update&contract_type_id=<?= (int)$contractType['contract_type_id'] ?>" enctype="multipart/form-data" class="card shadow-sm">
    <div class="card-body">
      <div class="row g-3">

        <div class="col-12">
          <label class="form-label fw-semibold" for="contract_type">Contract Type Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="contract_type" name="contract_type"
                 value="<?= h($contractType['contract_type']) ?>" maxlength="100" required>
        </div>

        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" rows="3"><?= h($contractType['description'] ?? '') ?></textarea>
        </div>

        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="formal_bidding_required" name="formal_bidding_required"
              <?= ($contractType['formal_bidding_required'] ?? 0) ? 'checked' : '' ?>>
            <label class="form-check-label" for="formal_bidding_required">
              Formal Bidding Required
            </label>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0">HTML Template</h5>
              <button type="button" class="btn btn-sm btn-light" id="html-template-edit-toggle">Edit Inline</button>
            </div>
            <div class="card-body">
              <?php if (!empty($contractType['template_file_html'])): ?>
                <div class="alert alert-success alert-sm mb-3">
                  <small>
                    <strong>Current Template:</strong><br>
                    <?= h(basename((string)$contractType['template_file_html'])) ?>
                  </small>
                </div>
              <?php else: ?>
                <div class="alert alert-warning alert-sm mb-3">
                  <small>No HTML template uploaded yet</small>
                </div>
              <?php endif; ?>

              <label class="form-label">Upload HTML Template</label>
              <input type="file" class="form-control" name="template_html" accept=".html,.htm,text/html"
                     help="Upload an HTML file (.html or .htm). Use {{field_name}} for template variables.">
              <small class="form-text text-muted d-block mt-2">
                HTML uses <code>{{field_name}}</code> placeholders.
              </small>

              <div id="html-template-inline-editor" class="mt-3 d-none">
                <hr>
                <label class="form-label fw-semibold" for="html-template-content">Edit Template Content</label>
                <textarea class="form-control font-monospace" id="html-template-content" rows="16" spellcheck="false"></textarea>
                <div class="d-flex align-items-center gap-2 mt-2">
                  <button type="button" class="btn btn-success btn-sm" id="html-template-save">Save Template</button>
                  <button type="button" class="btn btn-outline-secondary btn-sm" id="html-template-cancel">Cancel</button>
                  <span id="html-template-status" class="small text-muted"></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-success">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0">DOCX Template</h5>
              <?php if (!empty($contractType['template_file_docx'])): ?>
                <a href="/index.php?page=contract_types_template_edit_online&contract_type_id=<?= (int)$contractType['contract_type_id'] ?>"
                   class="btn btn-sm btn-light">Edit Inline</a>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <?php if (!empty($contractType['template_file_docx'])): ?>
                <div class="alert alert-success alert-sm mb-3">
                  <small>
                    <strong>Current Template:</strong><br>
                    <?= h(basename((string)$contractType['template_file_docx'])) ?>
                  </small>
                </div>
              <?php else: ?>
                <div class="alert alert-warning alert-sm mb-3">
                  <small>No DOCX template uploaded yet</small>
                </div>
              <?php endif; ?>

              <label class="form-label">Upload DOCX Template</label>
              <input type="file" class="form-control" name="template_docx" accept=".docx"
                     help="Upload a Microsoft Word file (.docx). Use ${field_name} for template variables.">
              <small class="form-text text-muted d-block mt-2">
                DOCX uses <code>${field_name}</code> placeholders.
                <?php if (!empty($contractType['template_file_docx'])): ?>
                  Or use <strong>Edit Inline</strong> above to edit the uploaded template directly in the browser.
                <?php endif; ?>
              </small>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="card mt-2">
            <div class="card-header">
              <h5 class="mb-0">Merge Field Syntax</h5>
            </div>
            <div class="card-body">
              <p class="mb-2"><strong>DOCX templates:</strong> <code>${field_name}</code></p>
              <p class="mb-2"><strong>HTML templates:</strong> <code>{{field_name}}</code></p>
              <p class="mb-0 text-muted small">
                View all available fields and examples on
                <a href="/index.php?page=merge_field_reference">Merge Field Reference</a>.
              </p>
            </div>
          </div>
        </div>

      </div>

      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="/index.php?page=contract_types" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  const toggleBtn = document.getElementById('html-template-edit-toggle');
  const editor = document.getElementById('html-template-inline-editor');
  const textarea = document.getElementById('html-template-content');
  const saveBtn = document.getElementById('html-template-save');
  const cancelBtn = document.getElementById('html-template-cancel');
  const statusEl = document.getElementById('html-template-status');
  const contractTypeId = <?= (int)$contractType['contract_type_id'] ?>;
  const csrfToken = <?= json_encode(csrf_token()) ?>;
  let loaded = false;

  if (!toggleBtn) return;

  async function loadContent() {
    statusEl.textContent = 'Loading...';
    try {
      const response = await fetch('/index.php?page=contract_types_template_content&contract_type_id=' + contractTypeId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const payload = await response.json();
      if (!response.ok || !payload.ok) {
        throw new Error(payload.message || 'Unable to load template.');
      }
      textarea.value = payload.content || '';
      loaded = true;
      statusEl.textContent = '';
    } catch (error) {
      statusEl.textContent = error.message || 'Failed to load template.';
    }
  }

  toggleBtn.addEventListener('click', async function () {
    const isHidden = editor.classList.contains('d-none');
    if (isHidden) {
      editor.classList.remove('d-none');
      toggleBtn.textContent = 'Hide Editor';
      if (!loaded) await loadContent();
    } else {
      editor.classList.add('d-none');
      toggleBtn.textContent = 'Edit Inline';
    }
  });

  cancelBtn.addEventListener('click', function () {
    editor.classList.add('d-none');
    toggleBtn.textContent = 'Edit Inline';
  });

  saveBtn.addEventListener('click', async function () {
    if (saveBtn.dataset.saving === '1') return;
    saveBtn.dataset.saving = '1';
    saveBtn.disabled = true;
    statusEl.textContent = 'Saving...';

    try {
      const response = await fetch('/index.php?page=contract_types_template_update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          contract_type_id: String(contractTypeId),
          content: textarea.value,
          csrf_token: csrfToken
        })
      });
      const payload = await response.json();
      if (!response.ok || !payload.ok) {
        throw new Error(payload.message || 'Unable to save template.');
      }
      statusEl.textContent = payload.message || 'Saved.';
    } catch (error) {
      statusEl.textContent = error.message || 'Save failed.';
    } finally {
      saveBtn.disabled = false;
      saveBtn.dataset.saving = '0';
    }
  });
})();
</script>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
