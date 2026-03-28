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
    <h1 class="h3 mb-0">Contract Types</h1>
    <a href="/index.php?page=contracts" class="btn btn-outline-secondary btn-sm">Back to Contracts</a>
  </div>

  <div class="table-responsive">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>Contract Type</th>
          <th>Description</th>
          <th>Formal Bidding Required</th>
          <th>HTML Template</th>
          <th>DOCX Template</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contractTypes as $ct): ?>
        <tr>
          <td>
            <strong><?= h($ct['contract_type']) ?></strong>
          </td>
          <td>
            <small><?= h(substr($ct['description'] ?? '', 0, 50)) ?></small>
          </td>
          <td>
            <?= ($ct['formal_bidding_required'] ?? 0) ? '✓ Yes' : 'No' ?>
          </td>
          <td>
            <?php if (!empty($ct['template_file_html'])): ?>
              <span class="badge bg-success">✓ Uploaded</span>
            <?php else: ?>
              <span class="badge bg-secondary">Not uploaded</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($ct['template_file_docx'])): ?>
              <span class="badge bg-success">✓ Uploaded</span>
            <?php else: ?>
              <span class="badge bg-secondary">Not uploaded</span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <a href="/index.php?page=contract_types_edit&contract_type_id=<?= (int)$ct['contract_type_id'] ?>"
               class="btn btn-primary btn-sm">
              Manage Templates
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (empty($contractTypes)): ?>
  <div class="alert alert-info">
    No contract types found.
  </div>
  <?php endif; ?>
</div>
