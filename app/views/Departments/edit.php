<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Department</h1>
    <a href="/index.php?page=departments" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Department saved successfully.</div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
          <li><?= h($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post" action="/index.php?page=department_update">
        <input type="hidden" name="department_id" value="<?= (int)$department['department_id'] ?>">

        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Department ID</label>
            <input type="text" class="form-control" value="<?= (int)$department['department_id'] ?>" readonly>
          </div>

          <div class="col-md-4">
            <label for="department_code" class="form-label">Department Code</label>
            <input
              type="text"
              id="department_code"
              name="department_code"
              class="form-control"
              maxlength="50"
              value="<?= h($department['department_code'] ?? '') ?>"
              required
            >
          </div>

          <div class="col-md-5">
            <label for="department_name" class="form-label">Department Name</label>
            <input
              type="text"
              id="department_name"
              name="department_name"
              class="form-control"
              maxlength="255"
              value="<?= h($department['department_name'] ?? '') ?>"
              required
            >
          </div>

          <div class="col-md-3">
            <label for="dept_initials" class="form-label">Dept Initials</label>
            <input
              type="text"
              id="dept_initials"
              name="dept_initials"
              class="form-control"
              maxlength="10"
              value="<?= h($department['dept_initials'] ?? '') ?>"
              required
            >
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check mb-2">
              <input
                class="form-check-input"
                type="checkbox"
                name="is_active"
                id="is_active"
                value="1"
                <?= !empty($department['is_active']) ? 'checked' : '' ?>
              >
              <label class="form-check-label" for="is_active">Active</label>
            </div>
          </div>

          <div class="col-md-6">
            <label for="department_head_id" class="form-label">Department Head</label>
            <select name="department_head_id" id="department_head_id" class="form-select">
              <option value="">-- Select --</option>
              <?php foreach ($people as $person): ?>
                <option value="<?= (int)$person['person_id'] ?>"
                  <?= (string)($department['department_head_id'] ?? '') === (string)$person['person_id'] ? 'selected' : '' ?>>
                  <?= h($person['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label for="assistant_town_manager_id" class="form-label">Assistant Town Manager</label>
            <select name="assistant_town_manager_id" id="assistant_town_manager_id" class="form-select">
              <option value="">-- Select --</option>
              <?php foreach ($people as $person): ?>
                <option value="<?= (int)$person['person_id'] ?>"
                  <?= (string)($department['assistant_town_manager_id'] ?? '') === (string)$person['person_id'] ? 'selected' : '' ?>>
                  <?= h($person['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label for="contract_admin_id" class="form-label">Contract Admin</label>
            <select name="contract_admin_id" id="contract_admin_id" class="form-select">
              <option value="">-- Select --</option>
              <?php foreach ($people as $person): ?>
                <option value="<?= (int)$person['person_id'] ?>"
                  <?= (string)($department['contract_admin_id'] ?? '') === (string)$person['person_id'] ? 'selected' : '' ?>>
                  <?= h($person['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea
              id="notes"
              name="notes"
              class="form-control"
              rows="4"
            ><?= h($department['notes'] ?? '') ?></textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Created At</label>
            <input type="text" class="form-control" value="<?= h($department['created_at'] ?? '') ?>" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Updated At</label>
            <input type="text" class="form-control" value="<?= h($department['updated_at'] ?? '') ?>" readonly>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="/index.php?page=departments" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Department</h1>
    <a href="/index.php?page=departments" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Department saved successfully.</div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post" action="/index.php?page=department_update">
        <input type="hidden" name="department_id" value="<?= (int)$department['department_id'] ?>">

        <?php require APP_ROOT . '/app/views/departments/form.php'; ?>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="/index.php?page=departments" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>