<?php
// app/views/admin_settings/organization.php
?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Organization Profile</h1>
    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary btn-sm">&#8592; Back to Settings</a>
  </div>

  <?php if (!empty($_GET['saved'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      Organization profile saved successfully.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= h($_SESSION['flash_error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <form method="post" action="/index.php?page=admin_organization_save" enctype="multipart/form-data">
    <?php $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); ?>
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="existing_logo_path" value="<?= h($org['logo_path'] ?? '') ?>">

    <!-- Identity -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold small text-uppercase text-muted">
        Organization Identity
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-7">
            <label class="form-label fw-semibold" for="org_name">Organization Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="org_name" name="org_name"
                   value="<?= h($org['org_name'] ?? '') ?>" required>
            <div class="form-text">e.g. Town of Springfield, City of Shelbyville</div>
          </div>
          <div class="col-md-5">
            <label class="form-label fw-semibold" for="org_type">Organization Type</label>
            <select class="form-select" id="org_type" name="org_type">
              <option value="">&#8212; Select &#8212;</option>
              <?php foreach (['city' => 'City', 'county' => 'County', 'town' => 'Town'] as $val => $label): ?>
                <option value="<?= h($val) ?>" <?= ($org['org_type'] ?? '') === $val ? 'selected' : '' ?>>
                  <?= h($label) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="website_url">Website URL</label>
            <input type="url" class="form-control" id="website_url" name="website_url"
                   value="<?= h($org['website_url'] ?? '') ?>" placeholder="https://www.springfield.gov">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="fiscal_year_start_month">Fiscal Year Start Month</label>
            <select class="form-select" id="fiscal_year_start_month" name="fiscal_year_start_month">
              <?php
                $months  = ['January','February','March','April','May','June',
                             'July','August','September','October','November','December'];
                $current = (int)($org['fiscal_year_start_month'] ?? 7);
                foreach ($months as $i => $name):
                  $val = $i + 1;
              ?>
                <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>><?= h($name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Logo -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold small text-uppercase text-muted">
        Logo
      </div>
      <div class="card-body">
        <?php if (!empty($org['logo_path'])): ?>
          <div class="mb-3">
            <p class="form-label fw-semibold mb-1">Current Logo</p>
            <img src="/<?= h($org['logo_path']) ?>"
                 alt="Organization Logo" class="img-thumbnail" style="max-height:100px;">
          </div>
        <?php endif; ?>
        <label class="form-label fw-semibold" for="logo">
          <?= !empty($org['logo_path']) ? 'Replace Logo' : 'Upload Logo' ?>
        </label>
        <input type="file" class="form-control" id="logo" name="logo" accept=".png,.jpg,.jpeg,.gif,.svg,.webp">
        <div class="form-text">PNG, JPG, GIF, SVG, or WebP. Displayed on login screen and page banner.</div>
      </div>
    </div>

    <!-- Key Personnel -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light fw-semibold small text-uppercase text-muted">
        Key Personnel
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="mayor_or_exec_name">Mayor / Chief Executive</label>
            <input type="text" class="form-control" id="mayor_or_exec_name" name="mayor_or_exec_name"
                   value="<?= h($org['mayor_or_exec_name'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="finance_director_name">Finance Director</label>
            <input type="text" class="form-control" id="finance_director_name" name="finance_director_name"
                   value="<?= h($org['finance_director_name'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="primary_contact_name">Primary Contact Name</label>
            <input type="text" class="form-control" id="primary_contact_name" name="primary_contact_name"
                   value="<?= h($org['primary_contact_name'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="primary_contact_email">Primary Contact Email</label>
            <input type="email" class="form-control" id="primary_contact_email" name="primary_contact_email"
                   value="<?= h($org['primary_contact_email'] ?? '') ?>">
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 mb-4">
      <button type="submit" class="btn btn-primary">Save Organization Profile</button>
      <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
