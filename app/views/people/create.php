<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">New Person</h1>
    <a href="/index.php?page=people" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post" action="/index.php?page=people_store">
        <?php require APP_ROOT . '/app/views/people/form.php'; ?>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Create Person</button>
          <a href="/index.php?page=people" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>