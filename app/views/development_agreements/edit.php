<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$isEdit       = ($mode ?? 'create') === 'edit';
$agrId        = (int)($agreement['dev_agreement_id'] ?? 0);
$action       = $isEdit
    ? '/index.php?page=development_agreements_update&dev_agreement_id=' . $agrId
    : '/index.php?page=development_agreements_store';
$v = fn($field) => h($agreement[$field] ?? '');
?>

<div class="d-flex align-items-center mb-3">
    <h1 class="h4 me-auto"><?= $isEdit ? 'Edit Development Agreement' : 'New Development Agreement' ?></h1>
    <a href="/index.php?page=development_agreements" class="btn btn-outline-secondary btn-sm">Back to List</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="<?= h($action) ?>" class="card shadow-sm">
  <?php if ($isEdit): ?>
    <input type="hidden" name="dev_agreement_id" value="<?= $agrId ?>">
  <?php endif; ?>

  <div class="card-body">

    <!-- Project Info -->
    <h6 class="text-muted text-uppercase fw-semibold mb-3 border-bottom pb-1">Project Information</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-8">
        <label class="form-label" for="project_name">Project Name <span class="text-danger">*</span></label>
        <input class="form-control" type="text" id="project_name" name="project_name" required
               value="<?= $v('project_name') ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="project_description">Project Description</label>
        <textarea class="form-control" id="project_description" name="project_description"
                  rows="4"><?= $v('project_description') ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label" for="proposed_improvements">Proposed Improvements</label>
        <textarea class="form-control" id="proposed_improvements" name="proposed_improvements"
                  rows="4"><?= $v('proposed_improvements') ?></textarea>
      </div>
    </div>

    <!-- Developer Entity -->
    <h6 class="text-muted text-uppercase fw-semibold mb-3 border-bottom pb-1">Developer Entity</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label" for="developer_entity_name">Corporation / Entity Name</label>
        <input class="form-control" type="text" id="developer_entity_name" name="developer_entity_name" maxlength="200"
               value="<?= $v('developer_entity_name') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="developer_contact_name">Name of Contact</label>
        <input class="form-control" type="text" id="developer_contact_name" name="developer_contact_name" maxlength="200"
               value="<?= $v('developer_contact_name') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="developer_entity_type">Type of Legal Entity</label>
        <select class="form-select" id="developer_entity_type" name="developer_entity_type">
          <option value="">— Select —</option>
          <?php foreach (['Individual', 'Corporation', 'LLC', 'Non-Profit'] as $et): ?>
            <option value="<?= h($et) ?>" <?= ($v('developer_entity_type') === $et) ? 'selected' : '' ?>><?= h($et) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="developer_address">Address</label>
        <input class="form-control" type="text" id="developer_address" name="developer_address" maxlength="255"
               value="<?= $v('developer_address') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="developer_state_of_incorporation">State of Incorporation</label>
        <input class="form-control" type="text" id="developer_state_of_incorporation" name="developer_state_of_incorporation" maxlength="100"
               value="<?= $v('developer_state_of_incorporation') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="developer_phone">Phone</label>
        <input class="form-control" type="tel" id="developer_phone" name="developer_phone" maxlength="50"
               value="<?= $v('developer_phone') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="developer_email">Email</label>
        <input class="form-control" type="email" id="developer_email" name="developer_email" maxlength="200"
               value="<?= $v('developer_email') ?>">
      </div>
    </div>

    <!-- Other Parties -->
    <h6 class="text-muted text-uppercase fw-semibold mb-3 border-bottom pb-1">Other Parties</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label" for="property_owner_name">Property Owner</label>
        <input class="form-control" type="text" id="property_owner_name" name="property_owner_name" maxlength="200"
               value="<?= $v('property_owner_name') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="attorney_id">Attorney</label>
        <select class="form-select" id="attorney_id" name="attorney_id">
          <option value="">— Select —</option>
          <?php foreach ($people as $p): ?>
            <option value="<?= (int)$p['person_id'] ?>"
              <?= ((string)($agreement['attorney_id'] ?? '') === (string)$p['person_id']) ? 'selected' : '' ?>>
              <?= h($p['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Property / Tracts -->
    <h6 class="text-muted text-uppercase fw-semibold mb-3 border-bottom pb-1">Property Tracts</h6>
    <p class="text-muted small mb-3">Add one or more parcels. Enter a Wake County PIN and click <strong>Lookup</strong> to auto-fill the address, acreage, and real estate ID from IMAPS.</p>

    <div id="tracts-container">
      <?php
        // Render existing tracts (edit mode) or one blank row (create mode)
        $existingTracts = $agreement['tracts'] ?? [];
        if (empty($existingTracts)) {
            $existingTracts = [['tract_id' => '', 'property_pin' => '', 'property_realestateid' => '', 'property_address' => '', 'property_acerage' => '', 'owner_name' => '', 'sort_order' => 0]];
        }
      ?>
      <?php foreach ($existingTracts as $ti => $tract): ?>
      <div class="tract-row card mb-2 border" data-index="<?= $ti ?>">
        <div class="card-body p-3">
          <input type="hidden" name="tracts[<?= $ti ?>][tract_id]" value="<?= h($tract['tract_id'] ?? '') ?>">
          <div class="row g-2 align-items-end">
            <!-- PIN + Lookup -->
            <div class="col-md-2">
              <label class="form-label form-label-sm mb-1">PIN</label>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control tract-pin" name="tracts[<?= $ti ?>][property_pin]"
                       value="<?= h($tract['property_pin'] ?? '') ?>" maxlength="15" placeholder="digits only">
                <button type="button" class="btn btn-outline-primary tract-lookup-btn"
                        onclick="lookupTractPin(this)" title="Lookup from Wake County IMAPS">Lookup</button>
              </div>
              <div class="tract-status form-text"></div>
            </div>
            <!-- Real Estate ID -->
            <div class="col-md-2">
              <label class="form-label form-label-sm mb-1">Real Estate ID</label>
              <input type="text" class="form-control form-control-sm tract-reid" name="tracts[<?= $ti ?>][property_realestateid]"
                     value="<?= h($tract['property_realestateid'] ?? '') ?>" maxlength="50">
            </div>
            <!-- Address -->
            <div class="col-md-3">
              <label class="form-label form-label-sm mb-1">Property Address</label>
              <input type="text" class="form-control form-control-sm tract-address" name="tracts[<?= $ti ?>][property_address]"
                     value="<?= h($tract['property_address'] ?? '') ?>">
            </div>
            <!-- Acreage -->
            <div class="col-md-1">
              <label class="form-label form-label-sm mb-1">Acres</label>
              <input type="number" step="0.0001" min="0" class="form-control form-control-sm tract-acreage"
                     name="tracts[<?= $ti ?>][property_acerage]"
                     value="<?= h($tract['property_acerage'] ?? '') ?>">
            </div>
            <!-- Property Owner -->
            <div class="col-md-3">
              <label class="form-label form-label-sm mb-1">Property Owner</label>
              <input type="text" class="form-control form-control-sm tract-owner"
                     name="tracts[<?= $ti ?>][owner_name]"
                     value="<?= h($tract['owner_name'] ?? '') ?>"
                     placeholder="Auto-filled from IMAPS">
            </div>
            <!-- Sort Order + Remove -->
            <div class="col-md-1">
              <label class="form-label form-label-sm mb-1">Order</label>
              <input type="number" class="form-control form-control-sm" name="tracts[<?= $ti ?>][sort_order]"
                     value="<?= (int)($tract['sort_order'] ?? 0) ?>" min="0" style="width:60px">
            </div>
            <div class="col-auto d-flex align-items-end pb-1">
              <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTract(this)" title="Remove this tract">✕</button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <button type="button" class="btn btn-outline-secondary btn-sm mb-4" onclick="addTract()">+ Add Another Tract</button>

    <!-- Zoning -->
    <h6 class="text-muted text-uppercase fw-semibold mb-3 border-bottom pb-1">Zoning</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label" for="current_zoning">Current Zoning</label>
        <input class="form-control" type="text" id="current_zoning" name="current_zoning"
               value="<?= $v('current_zoning') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="proposed_zoning">Proposed Zoning</label>
        <input class="form-control" type="text" id="proposed_zoning" name="proposed_zoning"
               value="<?= $v('proposed_zoning') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="comp_plan_designation">Comp Plan Designation</label>
        <input class="form-control" type="text" id="comp_plan_designation" name="comp_plan_designation"
               value="<?= $v('comp_plan_designation') ?>">
      </div>
    </div>

    <!-- Dates -->
    <h6 class="text-muted text-uppercase fw-semibold mb-3 border-bottom pb-1">Dates</h6>
    <div class="row g-3 mb-2">
      <div class="col-md-3">
        <label class="form-label" for="anticipated_start_date">Anticipated Start</label>
        <input class="form-control" type="date" id="anticipated_start_date" name="anticipated_start_date"
               value="<?= $v('anticipated_start_date') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="anticipated_end_date">Anticipated End</label>
        <input class="form-control" type="date" id="anticipated_end_date" name="anticipated_end_date"
               value="<?= $v('anticipated_end_date') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="agreement_termination_date">Termination Date</label>
        <input class="form-control" type="date" id="agreement_termination_date" name="agreement_termination_date"
               value="<?= $v('agreement_termination_date') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="planning_board_date">Planning Board Date</label>
        <input class="form-control" type="date" id="planning_board_date" name="planning_board_date"
               value="<?= $v('planning_board_date') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="town_council_hearing_date">Town Council Hearing Date</label>
        <input class="form-control" type="date" id="town_council_hearing_date" name="town_council_hearing_date"
               value="<?= $v('town_council_hearing_date') ?>">
      </div>
    </div>

  </div><!-- /card-body -->

  <!-- Utility / Land Use -->
  <div class="card-body border-top">
    <h6 class="text-muted text-uppercase fw-semibold mb-3 border-bottom pb-1">Utility &amp; Land Use</h6>
    <div class="row g-3">

      <div class="col-md-3">
        <label class="form-label" for="number_of_units">Number of Units (SF / ERU)</label>
        <input class="form-control" type="number" id="number_of_units" name="number_of_units"
               min="0" step="1"
               value="<?= h($agreement['number_of_units'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label" for="daily_flow_maximum">Daily Flow Maximum (GPD)</label>
        <div class="input-group">
          <input class="form-control" type="number" id="daily_flow_maximum" name="daily_flow_maximum"
                 min="0" step="1" placeholder="gallons/day"
                 value="<?= h($agreement['daily_flow_maximum'] ?? '') ?>">
          <span class="input-group-text">gpd</span>
        </div>
      </div>

      <div class="col-md-3">
        <label class="form-label" for="transportation_tier">Transportation Tier</label>
        <select class="form-select" id="transportation_tier" name="transportation_tier">
          <option value="">— Select —</option>
          <?php foreach (['Tier 1', 'Tier 2', 'Tier 3'] as $tier): ?>
            <option value="<?= h($tier) ?>" <?= ($agreement['transportation_tier'] ?? '') === $tier ? 'selected' : '' ?>>
              <?= h($tier) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3 d-flex align-items-end">
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="parkland_dedication"
                 name="parkland_dedication" value="1"
                 <?= !empty($agreement['parkland_dedication']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="parkland_dedication">
            Parkland Dedication Required
          </label>
        </div>
      </div>

      <div class="col-12">
        <label class="form-label" for="allocation_elements">Allocation Elements</label>
        <textarea class="form-control" id="allocation_elements" name="allocation_elements"
                  rows="5"
                  placeholder="List all allocation elements…"><?= h($agreement['allocation_elements'] ?? '') ?></textarea>
      </div>

    </div>
  </div>

  <div class="card-footer bg-white d-flex gap-2">
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Agreement' ?></button>
    <a href="/index.php?page=development_agreements" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<script>
// ── Tract row template (used by addTract()) ───────────────────────────────
function nextTractIndex() {
  return document.querySelectorAll('.tract-row').length;
}

function buildTractRowHtml(idx) {
  return `
    <div class="tract-row card mb-2 border" data-index="${idx}">
      <div class="card-body p-3">
        <input type="hidden" name="tracts[${idx}][tract_id]" value="">
        <div class="row g-2 align-items-end">
          <div class="col-md-2">
            <label class="form-label form-label-sm mb-1">PIN</label>
            <div class="input-group input-group-sm">
              <input type="text" class="form-control tract-pin" name="tracts[${idx}][property_pin]"
                     maxlength="15" placeholder="digits only">
              <button type="button" class="btn btn-outline-primary tract-lookup-btn"
                      onclick="lookupTractPin(this)" title="Lookup from Wake County IMAPS">Lookup</button>
            </div>
            <div class="tract-status form-text"></div>
          </div>
          <div class="col-md-2">
            <label class="form-label form-label-sm mb-1">Real Estate ID</label>
            <input type="text" class="form-control form-control-sm tract-reid" name="tracts[${idx}][property_realestateid]" maxlength="50">
          </div>
          <div class="col-md-3">
            <label class="form-label form-label-sm mb-1">Property Address</label>
            <input type="text" class="form-control form-control-sm tract-address" name="tracts[${idx}][property_address]">
          </div>
          <div class="col-md-1">
            <label class="form-label form-label-sm mb-1">Acres</label>
            <input type="number" step="0.0001" min="0" class="form-control form-control-sm tract-acreage" name="tracts[${idx}][property_acerage]">
          </div>
          <div class="col-md-3">
            <label class="form-label form-label-sm mb-1">Property Owner</label>
            <input type="text" class="form-control form-control-sm tract-owner"
                   name="tracts[${idx}][owner_name]"
                   placeholder="Auto-filled from IMAPS">
          </div>
          <div class="col-md-1">
            <label class="form-label form-label-sm mb-1">Order</label>
            <input type="number" class="form-control form-control-sm" name="tracts[${idx}][sort_order]" value="0" min="0" style="width:60px">
          </div>
          <div class="col-auto d-flex align-items-end pb-1">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTract(this)" title="Remove">✕</button>
          </div>
        </div>
      </div>
    </div>`;
}

function addTract() {
  const container = document.getElementById('tracts-container');
  const idx = nextTractIndex();
  container.insertAdjacentHTML('beforeend', buildTractRowHtml(idx));
}

function removeTract(btn) {
  const row = btn.closest('.tract-row');
  // Only remove if there's more than one row
  if (document.querySelectorAll('.tract-row').length <= 1) {
    // Just clear the fields instead of removing
    row.querySelectorAll('input[type=text], input[type=number]').forEach(i => i.value = '');
    return;
  }
  row.remove();
  reindexTracts();
}

function reindexTracts() {
  document.querySelectorAll('.tract-row').forEach((row, idx) => {
    row.dataset.index = idx;
    row.querySelectorAll('[name]').forEach(el => {
      el.name = el.name.replace(/tracts\[\d+\]/, `tracts[${idx}]`);
    });
  });
}

// ── IMAPS PIN Lookup ─────────────────────────────────────────────────────────
async function lookupTractPin(btn) {
  const row     = btn.closest('.tract-row');
  const pinEl   = row.querySelector('.tract-pin');
  const statusEl = row.querySelector('.tract-status');
  const pin     = pinEl.value.trim();

  if (!pin || !/^\d{1,15}$/.test(pin)) {
    statusEl.className = 'tract-status form-text text-danger';
    statusEl.textContent = 'Enter a numeric PIN first.';
    return;
  }

  btn.disabled    = true;
  btn.textContent = '…';
  statusEl.className   = 'tract-status form-text';
  statusEl.textContent = 'Looking up…';

  try {
    const res  = await fetch('/devagr_imaps_lookup.php?pin=' + encodeURIComponent(pin));
    const data = await res.json();

    if (data.error) {
      statusEl.className   = 'tract-status form-text text-danger';
      statusEl.textContent = data.error;
      return;
    }

    row.querySelector('.tract-reid').value    = data.property_realestateid ?? '';
    row.querySelector('.tract-address').value = data.property_address      ?? '';
    if (data.property_acerage != null) {
      row.querySelector('.tract-acreage').value = data.property_acerage;
    }
    if (data.owner_name) {
      row.querySelector('.tract-owner').value = data.owner_name;
    }

    statusEl.className   = 'tract-status form-text text-success';
    statusEl.textContent = '✓ Filled from Wake County GIS.';
  } catch {
    statusEl.className   = 'tract-status form-text text-danger';
    statusEl.textContent = 'Request failed — check internet connection.';
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Lookup';
  }
}
</script>
