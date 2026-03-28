<?php
declare(strict_types=1);


if (function_exists('current_person')) {
  $me = current_person();
  echo "<div class='alert alert-info'>Logged in as: <b>" . htmlspecialchars($me['email'] ?? 'unknown') . "</b></div>";
  if (function_exists('person_has_role_key')) {
    echo "<div class='alert alert-info'>Superuser? " . (person_has_role_key('superuser') ? 'YES' : 'NO') . " | Admin? " . (person_has_role_key('admin') ? 'YES' : 'NO') . "</div>";
  }
}
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
</div>