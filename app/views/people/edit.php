<?php
declare(strict_types=1);

$is_superuser = function_exists('person_has_role_key') && person_has_role_key('SUPERUSER');

$pwErrors  = $_SESSION['people_set_password_errors']  ?? [];
$pwSuccess = $_SESSION['people_set_password_success'] ?? false;
unset($_SESSION['people_set_password_errors'], $_SESSION['people_set_password_success']);
?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Person</h1>
    <a href="/index.php?page=people" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Saved successfully.</div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post" action="/index.php?page=people_update">
        <input type="hidden" name="person_id" value="<?= (int)$person['person_id'] ?>">

        <?php require APP_ROOT . '/app/views/people/form.php'; ?>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="/index.php?page=people" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

<?php if ($is_superuser): ?>
  <div class="card shadow-sm mt-4" id="password-section">
    <div class="card-header fw-semibold">Set Password</div>
    <div class="card-body">

      <?php if ($pwSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show">
          Password updated successfully.
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if (!empty($pwErrors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($pwErrors as $e): ?>
              <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="/index.php?page=people_set_password" autocomplete="off">
        <input type="hidden" name="person_id" value="<?= (int)$person['person_id'] ?>">

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control"
                   minlength="8" required autocomplete="new-password">
            <div class="form-text">Minimum 8 characters.</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control"
                   minlength="8" required autocomplete="new-password">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-warning"
                    onclick="return confirm('Set a new password for this user?')">
              Set Password
            </button>
          </div>
        </div>
      </form>

    </div>
  </div>
<?php endif; ?>
</div>