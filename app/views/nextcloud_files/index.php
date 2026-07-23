<?php
declare(strict_types=1);

$isConfigured = !empty($isConfigured);
$currentPath = (string)($listing['current_path'] ?? '');
$parentPath = (string)($listing['parent_path'] ?? '');
$entries = (array)($listing['entries'] ?? []);
$nextcloudWebUrl = (string)($listing['web_url'] ?? '');

if (!function_exists('format_nextcloud_size')) {
    function format_nextcloud_size(?int $bytes): string
    {
        if ($bytes === null || $bytes < 0) {
            return '—';
        }
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes;
        foreach ($units as $unit) {
            $value /= 1024;
            if ($value < 1024) {
                return number_format($value, $value < 10 ? 1 : 0) . ' ' . $unit;
            }
        }
        return number_format($value, 1) . ' PB';
    }
}

if (!function_exists('format_nextcloud_datetime')) {
    function format_nextcloud_datetime(?string $value): string
    {
        if (!$value) {
            return '—';
        }
        try {
            $dt = new DateTimeImmutable($value, new DateTimeZone('UTC'));
            return $dt->setTimezone(new DateTimeZone('America/New_York'))->format('m/d/Y g:i A');
        } catch (Throwable $e) {
            return h($value);
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h1 class="h4 mb-1">Nextcloud Files</h1>
    <div class="text-muted small">Browse and download files from files.schifano.com</div>
  </div>
  <?php if ($nextcloudWebUrl !== ''): ?>
    <a href="<?= h($nextcloudWebUrl) ?>" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">Open Full Nextcloud</a>
  <?php endif; ?>
</div>

<?php if (!$isConfigured): ?>
  <div class="alert alert-warning">
    <strong>Nextcloud is not configured.</strong>
    Add your credentials in <code>people.nextcloud_username</code> and <code>people.nextcloud_password</code>, or use
    fallback env values <code>NEXTCLOUD_USERNAME</code> and <code>NEXTCLOUD_APP_PASSWORD</code>.
  </div>
<?php else: ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= h((string)$error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div class="small text-muted">
        Current folder: <strong>/<?= h($currentPath === '' ? '' : $currentPath) ?></strong>
      </div>
      <div class="d-flex gap-2">
        <a href="/index.php?page=nextcloud_files" class="btn btn-outline-primary btn-sm">Root</a>
        <?php if ($currentPath !== ''): ?>
          <a href="/index.php?page=nextcloud_files&amp;path=<?= urlencode($parentPath) ?>" class="btn btn-outline-primary btn-sm">Up One Level</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th style="width:120px">Type</th>
            <th style="width:130px">Size</th>
            <th style="width:190px">Modified</th>
            <th class="text-end" style="width:220px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($entries)): ?>
            <tr>
              <td colspan="5" class="text-muted">This folder is empty.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($entries as $entry): ?>
              <?php $entryPath = (string)($entry['path'] ?? ''); ?>
              <?php $isDir = !empty($entry['is_dir']); ?>
              <tr>
                <td>
                  <?php if ($isDir): ?>
                    <a href="/index.php?page=nextcloud_files&amp;path=<?= urlencode($entryPath) ?>">📁 <?= h((string)($entry['name'] ?? 'Folder')) ?></a>
                  <?php else: ?>
                    <span>📄 <?= h((string)($entry['name'] ?? 'File')) ?></span>
                  <?php endif; ?>
                </td>
                <td><?= $isDir ? 'Folder' : 'File' ?></td>
                <td><?= $isDir ? '—' : h(format_nextcloud_size(isset($entry['size']) ? (int)$entry['size'] : null)) ?></td>
                <td><?= h(format_nextcloud_datetime($entry['modified_at'] ?? null)) ?></td>
                <td class="text-end">
                  <?php if ($isDir): ?>
                    <a href="/index.php?page=nextcloud_files&amp;path=<?= urlencode($entryPath) ?>" class="btn btn-outline-secondary btn-sm">Open</a>
                  <?php else: ?>
                    <a href="/index.php?page=nextcloud_files_download&amp;path=<?= urlencode($entryPath) ?>" class="btn btn-outline-primary btn-sm">Download</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
