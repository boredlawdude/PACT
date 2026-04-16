<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$statusBadge = function(string $s): string {
    return match($s) {
        'pending'  => '<span class="badge bg-warning text-dark">Pending</span>',
        'imported' => '<span class="badge bg-success">Imported</span>',
        'rejected' => '<span class="badge bg-secondary">Rejected</span>',
        default    => '<span class="badge bg-light text-dark">' . h($s) . '</span>',
    };
};
?>

<div class="d-flex align-items-center mb-3">
  <h1 class="h4 me-auto">Developer Intake Submissions</h1>
  <a href="/dev_agreement_intake.php" target="_blank" class="btn btn-outline-info btn-sm me-2">
    View Public Form ↗
  </a>
</div>

<?php if ($flashSuccess): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?= h($flashSuccess) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if (empty($submissions)): ?>
  <div class="alert alert-light border text-muted">No submissions yet. Share the public intake link with developers to get started.</div>
<?php else: ?>
<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Submitted</th>
          <th>Project Name</th>
          <th>Submitter</th>
          <th>Company</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($submissions as $sub): ?>
        <tr>
          <td class="text-muted small"><?= (int)$sub['submission_id'] ?></td>
          <td class="text-nowrap small"><?= h(date('m/d/Y g:i a', strtotime($sub['submitted_at']))) ?></td>
          <td><strong><?= h($sub['project_name'] ?: '—') ?></strong></td>
          <td>
            <?= h($sub['submitter_name'] ?? '—') ?>
            <?php if ($sub['submitter_email']): ?>
              <br><small class="text-muted"><?= h($sub['submitter_email']) ?></small>
            <?php endif; ?>
          </td>
          <td><?= h($sub['submitter_company'] ?? '—') ?></td>
          <td><?= $statusBadge($sub['status']) ?></td>
          <td>
            <a href="/index.php?page=dev_agreement_submissions_show&submission_id=<?= (int)$sub['submission_id'] ?>"
               class="btn btn-sm <?= $sub['status'] === 'pending' ? 'btn-primary' : 'btn-outline-secondary' ?>">
              <?= $sub['status'] === 'pending' ? 'Review' : 'View' ?>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="mt-4 p-3 bg-light border rounded text-muted small">
  <strong>Share this link with external developers:</strong><br>
  <code><?= h((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'your-domain.com') . '/dev_agreement_intake.php') ?></code>
</div>
