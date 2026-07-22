

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $error): ?>
        <li><?= h($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row g-3">
  <?php if (!empty($person['company_id'])): ?>
    <input type="hidden" name="company_id" value="<?= (int)$person['company_id'] ?>">
  <?php endif; ?>
  <div class="col-md-6">
    <label class="form-label">First Name</label>
    <input type="text" name="first_name" class="form-control" value="<?= h($person['first_name'] ?? '') ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">Last Name</label>
    <input type="text" name="last_name" class="form-control" value="<?= h($person['last_name'] ?? '') ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">Display Name</label>
    <input type="text" name="display_name" class="form-control" value="<?= h($person['display_name'] ?? '') ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="<?= h($person['email'] ?? '') ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">Office Phone</label>
    <input type="text" name="office_phone" class="form-control" value="<?= h($person['office_phone'] ?? ($person['officephone'] ?? '')) ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">Cell Phone</label>
    <input type="text" name="cell_phone" class="form-control" value="<?= h($person['cell_phone'] ?? ($person['cellphone'] ?? '')) ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" value="<?= h($person['title'] ?? '') ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">Department</label>
    <select name="department_id" class="form-select">
      <option value="">-- Select --</option>
      <?php foreach ($departments as $dept): ?>
        <option value="<?= (int)$dept['department_id'] ?>"
          <?= (string)($person['department_id'] ?? '') === (string)$dept['department_id'] ? 'selected' : '' ?>>
          <?= h($dept['department_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12">
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="is_active" value="1"
        <?= !empty($person['is_active']) ? 'checked' : '' ?>>
      <label class="form-check-label">Active</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_town_employee" value="1"
        <?= !empty($person['is_town_employee']) ? 'checked' : '' ?>>
      <label class="form-check-label">Town employee</label>
    </div>
  </div>

<?php if (!empty($can_edit_nextcloud)): ?>
  <div class="col-12"><hr class="my-2"></div>
  <div class="col-12">
    <h3 class="h6 mb-1">Nextcloud Access</h3>
    <div class="text-muted small mb-2">Used for the in-app Nextcloud file explorer on your account.</div>
  </div>

  <div class="col-md-6">
    <label class="form-label">Nextcloud Username</label>
    <input type="text" name="nextcloud_username" class="form-control"
           value="<?= h($person['nextcloud_username'] ?? '') ?>" autocomplete="off">
  </div>

  <div class="col-md-6">
    <label class="form-label">Nextcloud App Password</label>
    <input type="password" name="nextcloud_password" class="form-control" value="" autocomplete="new-password">
    <div class="form-text">
      Leave blank to keep current value.
      <?php if (!empty($person['nextcloud_password'])): ?>
        A password is currently saved.
      <?php endif; ?>
    </div>
  </div>

  <div class="col-12">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="clear_nextcloud_password" value="1" id="clear_nextcloud_password">
      <label class="form-check-label" for="clear_nextcloud_password">Clear saved Nextcloud app password</label>
    </div>
  </div>
<?php endif; ?>

<?php if (isset($can_edit_roles) && $can_edit_roles && !empty($roles)): ?>
  <div class="col-12">
    <label class="form-label">User Roles</label>
    <div class="row">
      <?php foreach ($roles as $role): ?>
        <div class="col-md-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="role_ids[]" value="<?= (int)$role['role_id'] ?>"
              <?= in_array((int)$role['role_id'], $assigned_role_ids ?? []) ? 'checked' : '' ?>>
            <label class="form-check-label">
              <?= h($role['role_name'] ?: $role['role_key']) ?>
            </label>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
</div>