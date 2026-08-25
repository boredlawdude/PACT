<?php
declare(strict_types=1);

if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Tasks</h1>
    <a href="/index.php?page=tasks_create" class="btn btn-primary btn-sm">+ Assign Task</a>
  </div>

  <?php if (!empty($flashMessages)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php foreach ($flashMessages as $msg): ?><div><?= h($msg) ?></div><?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($flashErrors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        <?php foreach ($flashErrors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <div class="btn-group btn-group-sm" role="group">
      <a href="/index.php?page=tasks&view=mine&status=<?= h($_GET['status'] ?? 'open') ?>"
         class="btn <?= $view === 'mine' ? 'btn-primary' : 'btn-outline-primary' ?>">My Tasks</a>
      <?php if (function_exists('is_system_admin') && is_system_admin()): ?>
        <a href="/index.php?page=tasks&view=all&status=<?= h($_GET['status'] ?? 'open') ?>"
           class="btn <?= $view === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">All Tasks</a>
      <?php endif; ?>
    </div>

    <div class="btn-group btn-group-sm" role="group">
      <?php $curStatus = $_GET['status'] ?? 'open'; ?>
      <a href="/index.php?page=tasks&view=<?= h($view) ?>&status=open"
         class="btn <?= $curStatus === 'open' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Open</a>
      <a href="/index.php?page=tasks&view=<?= h($view) ?>&status=done"
         class="btn <?= $curStatus === 'done' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Done</a>
      <a href="/index.php?page=tasks&view=<?= h($view) ?>&status=all"
         class="btn <?= $curStatus === 'all' ? 'btn-secondary' : 'btn-outline-secondary' ?>">All</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <?php if (empty($tasks)): ?>
        <div class="text-muted p-4">No tasks found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>Title</th>
                <?php if ($view === 'all'): ?><th>Assigned To</th><?php endif; ?>
                <th>Contract</th>
                <th>Due</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tasks as $t): ?>
                <tr class="<?= !empty($t['is_stale']) ? 'table-danger' : '' ?>">
                  <td>
                    <a href="/index.php?page=tasks_edit&task_id=<?= (int)$t['task_id'] ?>"><?= h($t['title']) ?></a>
                    <?php if (!empty($t['is_stale'])): ?>
                      <span class="badge text-bg-danger ms-1" title="No update in over <?= (int)\TasksController::STALE_DAYS ?> days">Stale</span>
                    <?php endif; ?>
                  </td>
                  <?php if ($view === 'all'): ?>
                    <td><?= h($t['assigned_to_name']) ?></td>
                  <?php endif; ?>
                  <td>
                    <?php if (!empty($t['contract_id'])): ?>
                      <a href="/index.php?page=contracts_show&contract_id=<?= (int)$t['contract_id'] ?>">
                        <?= h($t['contract_number'] ?? ('#' . $t['contract_id'])) ?>
                      </a>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-nowrap">
                    <?= !empty($t['due_date']) ? date('m/d/Y', strtotime($t['due_date'])) : '—' ?>
                  </td>
                  <td>
                    <?php
                      $statusBadge = ['open' => 'secondary', 'in_progress' => 'info', 'done' => 'success'][$t['status']] ?? 'secondary';
                    ?>
                    <span class="badge text-bg-<?= $statusBadge ?>"><?= h(str_replace('_', ' ', $t['status'])) ?></span>
                  </td>
                  <td class="text-end text-nowrap">
                    <?php if ($t['status'] !== 'done'): ?>
                      <form method="post" action="/index.php?page=tasks_set_status" class="d-inline">
                        <input type="hidden" name="task_id" value="<?= (int)$t['task_id'] ?>">
                        <input type="hidden" name="status" value="done">
                        <input type="hidden" name="redirect" value="/index.php?page=tasks&view=<?= h($view) ?>&status=<?= h($curStatus) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-success">Complete</button>
                      </form>
                    <?php else: ?>
                      <form method="post" action="/index.php?page=tasks_set_status" class="d-inline">
                        <input type="hidden" name="task_id" value="<?= (int)$t['task_id'] ?>">
                        <input type="hidden" name="status" value="open">
                        <input type="hidden" name="redirect" value="/index.php?page=tasks&view=<?= h($view) ?>&status=<?= h($curStatus) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Reopen</button>
                      </form>
                    <?php endif; ?>
                    <form method="post" action="/index.php?page=tasks_delete" class="d-inline"
                          onsubmit="return confirm('Delete this task?');">
                      <input type="hidden" name="task_id" value="<?= (int)$t['task_id'] ?>">
                      <input type="hidden" name="redirect" value="/index.php?page=tasks&view=<?= h($view) ?>&status=<?= h($curStatus) ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
