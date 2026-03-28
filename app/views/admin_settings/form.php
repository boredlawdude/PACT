<?php
// app/views/admin_settings/form.php

?>
<div class="container mt-4">
    <h1 class="h3 mb-4">System Settings – Paths & Templates</h1>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= implode('<br>', array_map('h', $messages)) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= h($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="/index.php?page=admin_settings_update">
                <input type="hidden" name="action" value="update_settings">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <div class="row g-4">
                    <?php foreach ($settings as $key => $row): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <?= h(ucwords(str_replace('_', ' ', $key))) ?>
                            </label>
                            <?php if ($row['description']): ?>
                                <div class="form-text text-muted mb-2"><?= h($row['description']) ?></div>
                            <?php endif; ?>
                            <input type="text" 
                                   class="form-control <?= in_array($key, ['storage_base_dir','docx_template_dir','html_template_dir']) ? 'font-monospace' : '' ?>"
                                   name="<?= h($key) ?>"
                                   value="<?= h($row['setting_value']) ?>"
                                   required>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-5">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        Save Changes
                    </button>
                    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary ms-3">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <div class="alert alert-info mt-4 small">
        <strong>Note:</strong> Changes take effect immediately for new generations/downloads.<br>
        Existing files are not moved — update paths carefully.
    </div>
</div>
<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
