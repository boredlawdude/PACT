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
            <label class="form-label fw-semibold" for="town_manager_person_id">Town Manager</label>
            <select class="form-select" id="town_manager_person_id" name="town_manager_person_id">
              <option value="">&#8212; Select &#8212;</option>
              <?php foreach (($people ?? []) as $p): ?>
                <option value="<?= (int)$p['person_id'] ?>"
                  <?= ((string)($org['town_manager_person_id'] ?? '') === (string)$p['person_id']) ? 'selected' : '' ?>>
                  <?= h($p['display_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="town_clerk_person_id">Town Clerk</label>
            <select class="form-select" id="town_clerk_person_id" name="town_clerk_person_id">
              <option value="">&#8212; Select &#8212;</option>
              <?php foreach (($people ?? []) as $p): ?>
                <option value="<?= (int)$p['person_id'] ?>"
                  <?= ((string)($org['town_clerk_person_id'] ?? '') === (string)$p['person_id']) ? 'selected' : '' ?>>
                  <?= h($p['display_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="town_attorney_person_id">Town Attorney</label>
            <select class="form-select" id="town_attorney_person_id" name="town_attorney_person_id">
              <option value="">&#8212; Select &#8212;</option>
              <?php foreach (($people ?? []) as $p): ?>
                <option value="<?= (int)$p['person_id'] ?>"
                  <?= ((string)($org['town_attorney_person_id'] ?? '') === (string)$p['person_id']) ? 'selected' : '' ?>>
                  <?= h($p['display_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="finance_director_person_id">Finance Director</label>
            <select class="form-select" id="finance_director_person_id" name="finance_director_person_id">
              <option value="">&#8212; Select &#8212;</option>
              <?php foreach (($people ?? []) as $p): ?>
                <option value="<?= (int)$p['person_id'] ?>"
                  <?= ((string)($org['finance_director_person_id'] ?? '') === (string)$p['person_id']) ? 'selected' : '' ?>>
                  <?= h($p['display_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
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

  <!-- Town Locations -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-semibold small text-uppercase text-muted">
      Town Locations
    </div>
    <div class="card-body">
      <p class="text-muted">
        Town-owned addresses (Town Hall, Public Works, Water Plant, Parks &amp; Rec, etc.) that a contract's
        deliverables or on-site work can be assigned to. Selectable on the Contract Edit form as
        &ldquo;Town Location for Shipping or Work&rdquo;.
      </p>

      <?php if (!empty($townLocationSuccess)): ?>
        <div class="alert alert-success">Saved successfully.</div>
      <?php endif; ?>
      <?php if (!empty($townLocationErrors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($townLocationErrors as $err): ?>
              <li><?= h($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
          <thead>
            <tr>
              <th>Name</th>
              <th>Address Line 1</th>
              <th>Address Line 2</th>
              <th>City</th>
              <th>State</th>
              <th>Zip</th>
              <th>Active</th>
              <th style="width:120px">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($townLocations ?? []) as $loc): ?>
              <tr>
                <form method="post" action="/index.php?page=admin_town_locations_update">
                  <input type="hidden" name="location_id" value="<?= (int)$loc['location_id'] ?>">
                  <input type="hidden" name="redirect_to" value="admin_organization">
                  <td><input type="text" name="location_name" value="<?= h($loc['location_name']) ?>" class="form-control form-control-sm" required></td>
                  <td><input type="text" name="address_line1" value="<?= h($loc['address_line1'] ?? '') ?>" class="form-control form-control-sm"></td>
                  <td><input type="text" name="address_line2" value="<?= h($loc['address_line2'] ?? '') ?>" class="form-control form-control-sm"></td>
                  <td><input type="text" name="city" value="<?= h($loc['city'] ?? '') ?>" class="form-control form-control-sm"></td>
                  <td><input type="text" name="state_region" value="<?= h($loc['state_region'] ?? '') ?>" class="form-control form-control-sm" style="width:70px"></td>
                  <td><input type="text" name="postal_code" value="<?= h($loc['postal_code'] ?? '') ?>" class="form-control form-control-sm" style="width:90px"></td>
                  <td class="text-center">
                    <input type="checkbox" name="is_active" value="1" <?= !empty($loc['is_active']) ? 'checked' : '' ?>>
                  </td>
                  <td>
                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                </form>
                <button type="button" class="btn btn-sm btn-danger ms-1"
                        onclick="if(confirm('Delete this location?')){var f=document.createElement('form');f.method='post';f.action='/index.php?page=admin_town_locations_delete';var i=document.createElement('input');i.type='hidden';i.name='location_id';i.value='<?= (int)$loc['location_id'] ?>';f.appendChild(i);var r=document.createElement('input');r.type='hidden';r.name='redirect_to';r.value='admin_organization';f.appendChild(r);document.body.appendChild(f);f.submit();}">Delete</button>
                  </td>
              </tr>
            <?php endforeach; ?>
            <tr>
              <form method="post" action="/index.php?page=admin_town_locations_create">
                <input type="hidden" name="redirect_to" value="admin_organization">
                <td><input type="text" name="location_name" class="form-control form-control-sm" placeholder="New location name" required></td>
                <td><input type="text" name="address_line1" class="form-control form-control-sm" placeholder="Street address"></td>
                <td><input type="text" name="address_line2" class="form-control form-control-sm" placeholder="Suite / unit"></td>
                <td><input type="text" name="city" class="form-control form-control-sm" placeholder="City"></td>
                <td><input type="text" name="state_region" class="form-control form-control-sm" placeholder="ST" style="width:70px"></td>
                <td><input type="text" name="postal_code" class="form-control form-control-sm" placeholder="Zip" style="width:90px"></td>
                <td class="text-center"><input type="checkbox" name="is_active" value="1" checked></td>
                <td><button type="submit" class="btn btn-sm btn-success">Add</button></td>
              </form>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
