<?php
declare(strict_types=1);

if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Contract Milestone Types</h1>
    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary btn-sm">Back to System Settings</a>
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

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-bordered align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th style="width:120px">Sort Order</th>
            <th class="text-end" style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($milestoneTypes as $mt): ?>
          <tr>
            <form method="post" action="/index.php?page=admin_milestone_types_update">
              <input type="hidden" name="milestone_type_id" value="<?= (int)$mt['milestone_type_id'] ?>">
              <td><input type="text" name="name" value="<?= h($mt['name']) ?>" class="form-control form-control-sm" required maxlength="150"></td>
              <td><input type="number" name="sort_order" value="<?= (int)$mt['sort_order'] ?>" class="form-control form-control-sm" style="width:80px"></td>
              <td class="text-end">
                <button type="submit" class="btn btn-sm btn-primary">Save</button>
            </form>
            <form method="post" action="/index.php?page=admin_milestone_types_delete" class="d-inline ms-1">
              <input type="hidden" name="milestone_type_id" value="<?= (int)$mt['milestone_type_id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger"
                      onclick="return confirm('Remove milestone type &quot;<?= h($mt['name']) ?>&quot;?')">Delete</button>
            </form>
              </td>
          </tr>
          <?php endforeach; ?>

          <!-- Add row -->
          <tr>
            <form method="post" action="/index.php?page=admin_milestone_types_store">
              <td><input type="text" name="name" class="form-control form-control-sm" placeholder="New milestone type name" required maxlength="150"></td>
              <td><input type="number" name="sort_order" value="0" class="form-control form-control-sm" style="width:80px"></td>
              <td class="text-end"><button type="submit" class="btn btn-sm btn-success">+ Add</button></td>
            </form>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
