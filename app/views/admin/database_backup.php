<?php
require_once APP_ROOT . '/app/views/layouts/header.php';
?>

<div class="container-fluid px-4 py-4" style="max-width:720px">
  <div class="d-flex align-items-center mb-4 gap-3">
    <a href="/index.php?page=system_settings" class="btn btn-sm btn-outline-secondary">&larr; System Settings</a>
    <h1 class="h4 mb-0">Database Backup</h1>
  </div>

  <?php if (!empty($_SESSION['backup_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= htmlspecialchars($_SESSION['backup_success'], ENT_QUOTES, 'UTF-8') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['backup_success']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['backup_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?= htmlspecialchars($_SESSION['backup_error'], ENT_QUOTES, 'UTF-8') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['backup_error']); ?>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Run Backup</div>
    <div class="card-body">
      <p class="text-muted small mb-4">
        Generates a full <code>mysqldump</code> of the <strong><?= htmlspecialchars(defined('DB_NAME') ? DB_NAME : ($_ENV['DB_DATABASE'] ?? 'contract_manager'), ENT_QUOTES, 'UTF-8') ?></strong> database.
        Leave <em>Save path</em> blank to download the file directly to your browser.
        Enter an absolute server path (e.g. <code>/media/usb/backups</code> or <code>/mnt/usb/pact</code>) to write the file there instead.
      </p>

      <form method="post" action="/index.php?page=db_backup_run">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="mb-4">
          <label class="form-label fw-semibold" for="save_path">Save path on server <span class="text-muted fw-normal">(optional — leave blank to download)</span></label>
          <input type="text" id="save_path" name="save_path" class="form-control font-monospace"
                 placeholder="/media/usb/backups"
                 value="<?= htmlspecialchars($_POST['save_path'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div class="form-text">Must be an absolute path the web server can write to. The directory will be created if it does not exist.</div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            &#128190; Run Backup
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mt-4">
    <div class="card-header bg-white fw-semibold">Notes</div>
    <div class="card-body text-muted small">
      <ul class="mb-0">
        <li>The backup includes all tables, routines, and triggers with <code>--single-transaction</code> (no table locks on InnoDB).</li>
        <li>To back up to a USB drive, first mount it on the server (e.g. <code>sudo mount /dev/sdb1 /media/usb</code>), then enter that path above.</li>
        <li>Downloaded files are named <code>pact_backup_YYYY-MM-DD_HHMMSS.sql</code>.</li>
        <li>This page is only accessible to system administrators.</li>
      </ul>
    </div>
  </div>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
