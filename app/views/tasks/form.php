<?php
declare(strict_types=1);

if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$isEdit = $mode === 'edit';
$action = $isEdit ? '/index.php?page=tasks_update&task_id=' . (int)$task['task_id'] : '/index.php?page=tasks_store';
?>

<div class="container py-4" style="max-width: 700px;">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/index.php?page=tasks">Tasks</a></li>
      <li class="breadcrumb-item active"><?= $isEdit ? 'Edit Task' : 'Assign Task' ?></li>
    </ol>
  </nav>

  <h1 class="h4 mb-4"><?= $isEdit ? 'Edit Task' : 'Assign Task' ?></h1>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($isEdit && !empty($_SESSION['flash_messages'])): ?>
    <div class="alert alert-success"><?php foreach ($_SESSION['flash_messages'] as $msg): ?><div><?= h($msg) ?></div><?php endforeach; ?></div>
    <?php unset($_SESSION['flash_messages']); ?>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post" action="<?= h($action) ?>">
        <div class="mb-3">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" required maxlength="255"
                 value="<?= h($task['title'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= h($task['description'] ?? '') ?></textarea>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Assign To <span class="text-danger">*</span></label>
            <select name="assigned_to_person_id" class="form-select" required>
              <option value="">— Select —</option>
              <?php foreach ($people as $person): ?>
                <option value="<?= (int)$person['person_id'] ?>"
                  <?= (int)($task['assigned_to_person_id'] ?? 0) === (int)$person['person_id'] ? 'selected' : '' ?>>
                  <?= h($person['display_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Due Date <span class="fw-normal text-muted">(optional)</span></label>
            <input type="date" name="due_date" class="form-control"
                   value="<?= h($task['due_date'] ?? '') ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Linked Contract <span class="fw-normal text-muted">(optional)</span></label>
          <?php if ($linkedContract): ?>
            <div class="form-control-plaintext">
              <a href="/index.php?page=contracts_show&contract_id=<?= (int)$linkedContract['contract_id'] ?>">
                <?= h($linkedContract['contract_number'] ?? '') ?> — <?= h($linkedContract['name'] ?? '') ?>
              </a>
            </div>
            <input type="hidden" name="contract_id" value="<?= (int)$linkedContract['contract_id'] ?>">
          <?php else: ?>
            <input type="number" name="contract_id" class="form-control" placeholder="Contract ID (optional)"
                   value="<?= h($task['contract_id'] ?? '') ?>">
          <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between">
          <a href="/index.php?page=tasks" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Assign Task' ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
