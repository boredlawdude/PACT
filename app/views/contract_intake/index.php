<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$flash = $_SESSION['flash_messages'] ?? [];
unset($_SESSION['flash_messages']);

$currentStatus = $status ?? 'pending';
?>

<div class="container-fluid py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h4 mb-0">Contract Intake Requests</h1>
      <p class="text-muted small mb-0">Submitted via the public contract request form</p>
    </div>
    <div>
      <a href="/contract_intake.php" target="_blank" class="btn btn-outline-secondary btn-sm">
        View Public Form ↗
      </a>
    </div>
  </div>

  <?php foreach ($flash as $msg): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= h($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endforeach; ?>

  <!-- Status filter tabs -->
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link <?= $currentStatus === 'pending'  ? 'active' : '' ?>"
         href="?page=contract_intake_list&status=pending">
        Pending
        <?php if ($pendingCount > 0): ?>
          <span class="badge bg-danger ms-1"><?= $pendingCount ?></span>
        <?php endif; ?>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $currentStatus === 'imported' ? 'active' : '' ?>"
         href="?page=contract_intake_list&status=imported">Imported</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $currentStatus === 'rejected' ? 'active' : '' ?>"
         href="?page=contract_intake_list&status=rejected">Rejected</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $currentStatus === 'all'      ? 'active' : '' ?>"
         href="?page=contract_intake_list&status=all">All</a>
    </li>
  </ul>

  <?php if (empty($submissions)): ?>
    <div class="alert alert-light border text-muted">No <?= h($currentStatus) ?> submissions.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-sm align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Contract Name</th>
            <th>Type</th>
            <th>Submitted By</th>
            <th>Department</th>
            <th>Vendor</th>
            <th>Est. Value</th>
            <th>Submitted</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($submissions as $sub): ?>
          <tr>
            <td class="text-muted small"><?= (int)$sub['submission_id'] ?></td>
            <td><a href="?page=contract_intake_show&id=<?= (int)$sub['submission_id'] ?>"><?= h($sub['contract_name']) ?></a></td>
            <td class="small"><?= h($sub['contract_type'] ?? '—') ?></td>
            <td class="small"><?= h($sub['submitter_name']) ?><br><span class="text-muted"><?= h($sub['submitter_email']) ?></span></td>
            <td class="small"><?= h($sub['submitter_department'] ?? '—') ?></td>
            <td class="small"><?= h($sub['counterparty_company'] ?? '—') ?></td>
            <td class="small"><?= $sub['estimated_value'] !== null ? '$' . number_format((float)$sub['estimated_value'], 0) : '—' ?></td>
            <td class="small text-muted"><?= date('m/d/Y', strtotime($sub['created_at'])) ?></td>
            <td>
              <?php
                $badgeClass = match($sub['status']) {
                    'pending'  => 'bg-warning text-dark',
                    'imported' => 'bg-success',
                    'rejected' => 'bg-secondary',
                    default    => 'bg-light text-dark',
                };
              ?>
              <span class="badge <?= $badgeClass ?>"><?= ucfirst(h($sub['status'])) ?></span>
              <?php if ($sub['status'] === 'imported' && $sub['imported_contract_id']): ?>
                <a href="?page=contracts_show&contract_id=<?= (int)$sub['imported_contract_id'] ?>" class="small ms-1">View →</a>
              <?php endif; ?>
            </td>
            <td>
              <a href="?page=contract_intake_show&id=<?= (int)$sub['submission_id'] ?>"
                 class="btn btn-outline-primary btn-sm">Review</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
