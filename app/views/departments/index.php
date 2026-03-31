<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Departments</h1>
    <a href="/index.php?page=departments_create" class="btn btn-primary btn-sm">New Department</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Department</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($departments)): ?>
              <?php foreach ($departments as $department): ?>
                <tr>
                  <td><?= (int)$department['department_id'] ?></td>
                  <td><?= h($department['department_name'] ?? '') ?></td>
                  <td><?= !empty($department['is_active']) ? 'Active' : 'Inactive' ?></td>
                  <td class="text-end">
                    <a href="/index.php?page=department_edit&id=<?= (int)$department['department_id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-4">No departments found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>