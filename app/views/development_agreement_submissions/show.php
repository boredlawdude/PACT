<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
$isPending  = ($submission['status'] === 'pending');
$isImported = ($submission['status'] === 'imported');
$isRejected = ($submission['status'] === 'rejected');

$badge = match($submission['status']) {
    'pending'  => '<span class="badge bg-warning text-dark fs-6">Pending Review</span>',
    'imported' => '<span class="badge bg-success fs-6">Imported</span>',
    'rejected' => '<span class="badge bg-secondary fs-6">Rejected</span>',
    default    => '',
};
?>

<div class="d-flex align-items-center mb-3 gap-2">
  <h1 class="h4 me-auto">Intake Submission #<?= (int)$submission['submission_id'] ?></h1>
  <?= $badge ?>
  <a href="/index.php?page=dev_agreement_submissions" class="btn btn-outline-secondary btn-sm">← All Submissions</a>
</div>

<?php if (!empty($flashErrors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($flashErrors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<?php if ($isImported): ?>
  <div class="alert alert-success">
    Imported as Development Agreement.
    <a href="/index.php?page=contracts_show&contract_id=<?php
      // Look up contract_id via dev_agreement_id
      $da = (new DevelopmentAgreement(db()))->find((int)$submission['imported_dev_agreement_id']);
      echo (int)($da['contract_id'] ?? 0);
    ?>">View Agreement →</a>
  </div>
<?php endif; ?>

<?php if ($isRejected && $submission['review_notes']): ?>
  <div class="alert alert-secondary">
    <strong>Rejection notes:</strong> <?= h($submission['review_notes']) ?>
  </div>
<?php endif; ?>

<!-- Submitter info -->
<div class="card shadow-sm mb-4">
  <div class="card-header"><h6 class="mb-0">Submitted By</h6></div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3"><div class="small text-muted">Name</div><div><?= h($submission['submitter_name'] ?? '—') ?></div></div>
      <div class="col-md-3"><div class="small text-muted">Email</div><div><?= $submission['submitter_email'] ? '<a href="mailto:' . h($submission['submitter_email']) . '">' . h($submission['submitter_email']) . '</a>' : '—' ?></div></div>
      <div class="col-md-3"><div class="small text-muted">Phone</div><div><?= h($submission['submitter_phone'] ?? '—') ?></div></div>
      <div class="col-md-3"><div class="small text-muted">Company</div><div><?= h($submission['submitter_company'] ?? '—') ?></div></div>
      <div class="col-md-3"><div class="small text-muted">Submitted</div><div><?= h(date('m/d/Y g:i a', strtotime($submission['submitted_at']))) ?></div></div>
    </div>
  </div>
</div>

<!-- Developer entity -->
<div class="card shadow-sm mb-4">
  <div class="card-header"><h6 class="mb-0">Developer Entity</h6></div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4"><div class="small text-muted">Corporation / Entity Name</div><div><?= h($submission['developer_entity_name'] ?? '') ?: '—' ?></div></div>
      <div class="col-md-4"><div class="small text-muted">Name of Contact</div><div><?= h($submission['developer_contact_name'] ?? '') ?: '—' ?></div></div>
      <div class="col-md-4"><div class="small text-muted">Type of Legal Entity</div><div><?= h($submission['developer_entity_type'] ?? '') ?: '—' ?></div></div>
      <div class="col-md-6"><div class="small text-muted">Address</div><div><?= h($submission['developer_address'] ?? '') ?: '—' ?></div></div>
      <div class="col-md-3"><div class="small text-muted">State of Incorporation</div><div><?= h($submission['developer_state_of_incorporation'] ?? '') ?: '—' ?></div></div>
      <div class="col-md-3"><div class="small text-muted">Phone</div><div><?= h($submission['developer_phone'] ?? '') ?: '—' ?></div></div>
      <div class="col-md-4"><div class="small text-muted">Email</div><div><?= $submission['developer_email'] ? '<a href="mailto:' . h($submission['developer_email']) . '">' . h($submission['developer_email']) . '</a>' : '—' ?></div></div>
      <div class="col-md-4"><div class="small text-muted">Property Owner</div><div><?= h($submission['property_owner_name'] ?? '') ?: '—' ?></div></div>
    </div>
  </div>
</div>

<!-- Project info -->
<div class="card shadow-sm mb-4">
  <div class="card-header"><h6 class="mb-0">Project Information</h6></div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-12"><div class="small text-muted">Project Name</div><div><strong><?= h($submission['project_name'] ?? '—') ?></strong></div></div>
      <?php if ($submission['project_description']): ?>
      <div class="col-12"><div class="small text-muted">Description</div><div style="white-space:pre-wrap"><?= h($submission['project_description']) ?></div></div>
      <?php endif; ?>
      <?php if ($submission['proposed_improvements']): ?>
      <div class="col-12"><div class="small text-muted">Proposed Improvements</div><div style="white-space:pre-wrap"><?= h($submission['proposed_improvements']) ?></div></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Property tracts -->
<div class="card shadow-sm mb-4">
  <div class="card-header"><h6 class="mb-0">Property Tracts</h6></div>
  <div class="card-body p-0">
    <?php if (!empty($tracts)): ?>
    <table class="table table-sm table-bordered mb-0">
      <thead class="table-light">
        <tr><th>#</th><th>PIN</th><th>Real Estate ID</th><th>Address</th><th>Acres</th><th>Owner</th></tr>
      </thead>
      <tbody>
        <?php foreach ($tracts as $i => $t): ?>
        <tr>
          <td class="text-muted"><?= $i + 1 ?></td>
          <td><?= h($t['property_pin'] ?? '—') ?></td>
          <td><?= h($t['property_realestateid'] ?? '—') ?></td>
          <td><?= h($t['property_address'] ?? '—') ?></td>
          <td><?= h($t['property_acerage'] ?? '—') ?></td>
          <td><?= h($t['owner_name'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="p-3 text-muted fst-italic">No tracts submitted.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Zoning & dates -->
<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-header"><h6 class="mb-0">Zoning</h6></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6"><div class="small text-muted">Current Zoning</div><div><?= h($submission['current_zoning'] ?? '—') ?></div></div>
          <div class="col-6"><div class="small text-muted">Proposed Zoning</div><div><?= h($submission['proposed_zoning'] ?? '—') ?></div></div>
          <div class="col-12"><div class="small text-muted">Comp Plan Designation</div><div><?= h($submission['comp_plan_designation'] ?? '—') ?></div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-header"><h6 class="mb-0">Dates</h6></div>
      <div class="card-body">
        <div class="row g-3">
          <?php $df = fn($d) => $d ? date('m/d/Y', strtotime($d)) : '—'; ?>
          <div class="col-6"><div class="small text-muted">Anticipated Start</div><div><?= $df($submission['anticipated_start_date']) ?></div></div>
          <div class="col-6"><div class="small text-muted">Anticipated End</div><div><?= $df($submission['anticipated_end_date']) ?></div></div>
          <div class="col-6"><div class="small text-muted">Agreement Termination</div><div><?= $df($submission['agreement_termination_date']) ?></div></div>
          <div class="col-6"><div class="small text-muted">Planning Board</div><div><?= $df($submission['planning_board_date']) ?></div></div>
          <div class="col-6"><div class="small text-muted">Town Council Hearing</div><div><?= $df($submission['town_council_hearing_date']) ?></div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($isPending): ?>
<!-- Action buttons -->
<div class="card shadow-sm border-primary mb-4">
  <div class="card-body">
    <div class="row g-3 align-items-start">
      <div class="col-md-6">
        <p class="mb-2 fw-semibold">Import this submission</p>
        <p class="text-muted small mb-3">Creates a Development Agreement with all submitted data. You will be taken to the agreement to assign the Applicant and review details.</p>
        <form method="post" action="/index.php?page=dev_agreement_submissions_import"
              onsubmit="return confirm('Import this submission as a new Development Agreement?')">
          <input type="hidden" name="submission_id" value="<?= (int)$submission['submission_id'] ?>">
          <button type="submit" class="btn btn-success">✓ Import as Development Agreement</button>
        </form>
      </div>
      <div class="col-md-6 border-start ps-4">
        <p class="mb-2 fw-semibold text-muted">Reject this submission</p>
        <form method="post" action="/index.php?page=dev_agreement_submissions_reject">
          <input type="hidden" name="submission_id" value="<?= (int)$submission['submission_id'] ?>">
          <div class="mb-2">
            <textarea class="form-control form-control-sm" name="review_notes" rows="2"
                      placeholder="Optional: reason for rejection"></textarea>
          </div>
          <button type="submit" class="btn btn-outline-secondary btn-sm"
                  onclick="return confirm('Mark this submission as rejected?')">Reject</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
