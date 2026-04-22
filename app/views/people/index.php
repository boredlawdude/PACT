<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">People</h1>
    <a href="/index.php?page=people_create" class="btn btn-primary btn-sm">New Person</a>
  </div>

  <?php if (!empty($_SESSION['flash_messages'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars(implode(' ', $_SESSION['flash_messages']), ENT_QUOTES, 'UTF-8') ?></div>
    <?php unset($_SESSION['flash_messages']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_errors'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $_SESSION['flash_errors']), ENT_QUOTES, 'UTF-8') ?></div>
    <?php unset($_SESSION['flash_errors']); ?>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Title</th>
              <th>Department</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($people as $person): ?>
              <tr>
                <td><?= htmlspecialchars(trim(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($person['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($person['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($person['department_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= !empty($person['is_active']) ? 'Active' : 'Inactive' ?></td>
                <td class="text-end">
                  <a href="/index.php?page=people_edit&id=<?= (int)$person['person_id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <?php if (is_system_admin() && (int)$person['person_id'] !== (int)current_person_id()): ?>
                  <form method="post" action="/index.php?page=people_delete" class="d-inline ms-1"
                        onsubmit="return confirm('Delete <?= htmlspecialchars(trim(($person['first_name']??'').' '.($person['last_name']??'')), ENT_QUOTES) ?>? This cannot be undone.')">
                    <input type="hidden" name="person_id" value="<?= (int)$person['person_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>

            <?php if (empty($people)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">No people found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>