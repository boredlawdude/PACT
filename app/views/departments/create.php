<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">New Department</h1>
    <a href="/index.php?page=departments" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post" action="/index.php?page=departments_store">
        <?php require APP_ROOT . '/app/views/departments/form.php'; ?>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Create Department</button>
          <a href="/index.php?page=departments" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>