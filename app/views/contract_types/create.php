<?php
declare(strict_types=1);

if (!function_exists('h')) {
    function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Contract Type</h1>
    <a href="/index.php?page=contract_types" class="btn btn-outline-secondary btn-sm">Back to Types</a>
  </div>

  <?php if (!empty($flashErrors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        <?php foreach ($flashErrors as $err): ?>
          <li><?= h($err) ?></li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form method="post" action="/index.php?page=contract_types_store" class="card shadow-sm">
    <div class="card-body">
      <div class="row g-3">

        <div class="col-12">
          <label class="form-label fw-semibold" for="contract_type">Contract Type Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="contract_type" name="contract_type"
                 value="<?= h($flashOld['contract_type'] ?? '') ?>" maxlength="100" required>
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold" for="description">Description</label>
          <textarea class="form-control" id="description" name="description" rows="3"><?= h($flashOld['description'] ?? '') ?></textarea>
        </div>

        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="formal_bidding_required" name="formal_bidding_required"
              <?= !empty($flashOld['formal_bidding_required']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="formal_bidding_required">
              Formal Bidding Required
            </label>
          </div>
        </div>

      </div>

      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Create Contract Type</button>
        <a href="/index.php?page=contract_types" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </div>
  </form>
</div>
