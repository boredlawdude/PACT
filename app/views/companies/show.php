<?php
declare(strict_types=1);

if (!function_exists('h')) {
    function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

$companyId = (int)($company['company_id'] ?? 0);
$companyName = trim((string)($company['name'] ?? 'Company'));
$isActive = (int)($company['is_active'] ?? 0) === 1;
?>

<div class="container py-4">

  <!-- ── Header ── -->
  <div class="d-flex justify-content-between align-items-start mb-4">
    <div>
      <div class="text-muted small mb-1">Company Detail</div>
      <h1 class="h3 mb-1"><?= h($companyName) ?></h1>
      <div>
        <?php if ($isActive): ?>
          <span class="badge text-bg-success">Active</span>
        <?php else: ?>
          <span class="badge text-bg-secondary">Inactive</span>
        <?php endif; ?>
        <?php if (!empty($company['type'])): ?>
          <span class="badge text-bg-light text-dark border ms-1"><?= h(ucfirst($company['type'])) ?></span>
        <?php endif; ?>
        <?php if (!empty($company['company_type_name'])): ?>
          <span class="badge text-bg-light text-dark border ms-1"><?= h($company['company_type_name']) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap justify-content-end">
      <a href="/index.php?page=companies" class="btn btn-outline-secondary btn-sm">Back to Companies</a>
      <a href="/index.php?page=companies_edit&company_id=<?= $companyId ?>" class="btn btn-primary btn-sm">Edit Company</a>
      <a href="/index.php?page=contracts_search&company_id=<?= $companyId ?>" class="btn btn-outline-info btn-sm">See All Contracts</a>
    </div>
  </div>

  <div class="row g-4">

    <!-- ── Left column ── -->
    <div class="col-lg-8">

      <!-- Core Info -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Company Information</h2></div>
        <div class="card-body">
          <div class="row g-3">

            <div class="col-md-6">
              <div class="small text-muted">Company Name</div>
              <div class="fw-semibold"><?= h($company['name'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-3">
              <div class="small text-muted">Category</div>
              <div><?= h(ucfirst($company['type'] ?? '')) ?: '—' ?></div>
            </div>

            <div class="col-md-3">
              <div class="small text-muted">Entity Type</div>
              <div><?= h($company['company_type_name'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">State of Incorporation</div>
              <div><?= h($company['state_of_incorporation'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">Vendor ID</div>
              <div><?= h($company['vendor_id'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">Tax ID</div>
              <div><?= h($company['tax_id'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">NC SOS ID</div>
              <div>
                <?php if (!empty($company['sosid'])): ?>
                  <a href="https://sosnc.gov/online_services/search/by_title/search_Business_Registration"
                     target="_blank" rel="noopener noreferrer">
                    <?= h($company['sosid']) ?>
                  </a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </div>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">Contact Name</div>
              <div><?= h($company['contact_name'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">Phone</div>
              <div>
                <?php if (!empty($company['phone'])): ?>
                  <a href="tel:<?= h($company['phone']) ?>"><?= h($company['phone']) ?></a>
                <?php else: ?>—<?php endif; ?>
              </div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">Email</div>
              <div>
                <?php if (!empty($company['email'])): ?>
                  <a href="mailto:<?= h($company['email']) ?>"><?= h($company['email']) ?></a>
                <?php else: ?>—<?php endif; ?>
              </div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">Verified By</div>
              <div><?= h($company['verified_by'] ?? '') ?: '—' ?></div>
            </div>

          </div>
        </div>
      </div>

      <!-- Address -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Address</h2></div>
        <div class="card-body">
          <div class="row g-3">

            <?php if (!empty($company['address'])): ?>
            <div class="col-12">
              <div class="small text-muted">Address (single line)</div>
              <div><?= h($company['address']) ?></div>
            </div>
            <?php endif; ?>

            <div class="col-md-6">
              <div class="small text-muted">Address Line 1</div>
              <div><?= h($company['address_line1'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">Address Line 2</div>
              <div><?= h($company['address_line2'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">City</div>
              <div><?= h($company['city'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">State / Region</div>
              <div><?= h($company['state_region'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-2">
              <div class="small text-muted">Postal Code</div>
              <div><?= h($company['postal_code'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-2">
              <div class="small text-muted">Country</div>
              <div><?= h($company['country'] ?? '') ?: '—' ?></div>
            </div>

          </div>
        </div>
      </div>

      <!-- COI -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Certificate of Insurance (COI)</h2></div>
        <div class="card-body">
          <div class="row g-3">

            <div class="col-md-4">
              <div class="small text-muted">COI Carrier</div>
              <div><?= h($company['coi_carrier'] ?? '') ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">COI Expiration Date</div>
              <?php
                $coiExp = $company['coi_exp_date'] ?? '';
                $coiClass = '';
                if ($coiExp) {
                    $coiClass = (strtotime($coiExp) < time()) ? 'text-danger fw-semibold' : 'text-success';
                }
              ?>
              <div class="<?= $coiClass ?>"><?= h($coiExp) ?: '—' ?></div>
            </div>

            <div class="col-md-4">
              <div class="small text-muted">COI Verified By</div>
              <div><?= h($company['coi_verified_by_name'] ?? '') ?: '—' ?></div>
            </div>

          </div>
        </div>
      </div>

      <!-- People at this company -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h2 class="h6 mb-0">People at this Company</h2>
          <a href="/index.php?page=companies_edit&company_id=<?= $companyId ?>#people"
             class="btn btn-outline-primary btn-sm">Manage People</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($employees)): ?>
            <div class="p-3 text-muted">No people linked to this company.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-sm table-striped align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Office</th>
                    <th>Cell</th>
                    <th>Dept</th>
                    <th>Active</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($employees as $e): ?>
                    <tr>
                      <td>
                        <a href="/index.php?page=people_edit&person_id=<?= (int)$e['person_id'] ?>">
                          <?= h($e['person_name'] ?? '') ?>
                        </a>
                      </td>
                      <td>
                        <?php if (!empty($e['email'])): ?>
                          <a href="mailto:<?= h($e['email']) ?>"><?= h($e['email']) ?></a>
                        <?php else: ?>—<?php endif; ?>
                      </td>
                      <td><?= h($e['officephone'] ?? '') ?: '—' ?></td>
                      <td><?= h($e['cellphone'] ?? '') ?: '—' ?></td>
                      <td><?= h($e['department_name'] ?? '') ?: '—' ?></td>
                      <td><?= ((int)($e['is_active'] ?? 1) === 1) ? '<span class="badge text-bg-success">Yes</span>' : '<span class="badge text-bg-secondary">No</span>' ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Contracts linked to this company -->
      <?php if (!empty($contracts)): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h2 class="h6 mb-0">Contracts (<?= count($contracts) ?>)</h2>
          <a href="/index.php?page=contracts_search&company_id=<?= $companyId ?>" class="btn btn-outline-secondary btn-sm">View All</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Name</th>
                  <th>Number</th>
                  <th>Status</th>
                  <th>Start</th>
                  <th>End</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($contracts as $ct): ?>
                  <tr>
                    <td>
                      <a href="/index.php?page=contracts_show&contract_id=<?= (int)$ct['contract_id'] ?>">
                        <?= h($ct['name'] ?? '') ?>
                      </a>
                    </td>
                    <td><?= h($ct['contract_number'] ?? '') ?: '—' ?></td>
                    <td><?= h($ct['status_name'] ?? '') ?: '—' ?></td>
                    <td><?= h($ct['start_date'] ?? '') ?: '—' ?></td>
                    <td><?= h($ct['end_date'] ?? '') ?: '—' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /col-lg-8 -->

    <!-- ── Right sidebar ── -->
    <div class="col-lg-4">

      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Quick Info</h2></div>
        <div class="card-body">
          <dl class="row mb-0 small">
            <dt class="col-5 text-muted">Company ID</dt>
            <dd class="col-7"><?= $companyId ?></dd>

            <dt class="col-5 text-muted">Status</dt>
            <dd class="col-7">
              <?= $isActive ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?>
            </dd>

            <dt class="col-5 text-muted">Vendor ID</dt>
            <dd class="col-7"><?= h($company['vendor_id'] ?? '') ?: '—' ?></dd>

            <dt class="col-5 text-muted">Tax ID</dt>
            <dd class="col-7"><?= h($company['tax_id'] ?? '') ?: '—' ?></dd>

            <dt class="col-5 text-muted">SOS ID</dt>
            <dd class="col-7">
              <?= !empty($company['sosid']) ? h($company['sosid']) : '—' ?>
            </dd>

            <dt class="col-5 text-muted">COI Expires</dt>
            <dd class="col-7 <?= (!empty($company['coi_exp_date']) && strtotime($company['coi_exp_date']) < time()) ? 'text-danger fw-semibold' : '' ?>">
              <?= h($company['coi_exp_date'] ?? '') ?: '—' ?>
            </dd>
          </dl>
        </div>
      </div>

      <!-- Comments -->
      <div class="card shadow-sm">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Internal Comments</h2></div>
        <div class="card-body">
          <?php if (empty($comments)): ?>
            <div class="text-muted small">No comments yet.</div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($comments as $cmt): ?>
                <div class="list-group-item px-0">
                  <div class="small text-muted mb-1">
                    <?= h($cmt['created_at']) ?> · <?= h($cmt['author_name']) ?>
                  </div>
                  <div class="small"><?= nl2br(h($cmt['comment_text'])) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="mt-3">
            <a href="/index.php?page=companies_edit&company_id=<?= $companyId ?>#comments"
               class="btn btn-outline-secondary btn-sm w-100">Add / Manage Comments</a>
          </div>
        </div>
      </div>

    </div><!-- /col-lg-4 -->

  </div><!-- /row -->
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
