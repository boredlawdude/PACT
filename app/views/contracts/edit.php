<?php
declare(strict_types=1);

//require APP_ROOT . '/app/views/layouts/header.php';

$isEdit = ($mode ?? 'create') === 'edit';
$contractId = $contract['contract_id'] ?? null;

$action = $isEdit
    ? '/index.php?page=contracts_update&contract_id=' . urlencode((string)$contractId)
    : '/index.php?page=contracts_store';

if (!function_exists('h')) {
    function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    .contract-editor-shell {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.03), rgba(15, 23, 42, 0));
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1.25rem;
    }

    .contract-editor-banner {
        background: linear-gradient(135deg, #14344e, #1d5b7d 55%, #3d7aa4);
        color: #fff;
        border-radius: 1.25rem 1.25rem 0 0;
    }

    .contract-editor-banner .text-muted {
        color: rgba(255, 255, 255, 0.72) !important;
    }

    .contract-editor-card {
        border: 0;
        border-radius: 0 0 1.25rem 1.25rem;
        overflow: hidden;
    }

    .contract-editor-card .card-body {
        background: #fff;
    }

    .contract-editor-card .form-label {
        font-weight: 600;
        color: #243447;
    }

    .contract-editor-card .form-control,
    .contract-editor-card .form-select {
        border-radius: 0.85rem;
        border-color: rgba(15, 23, 42, 0.14);
    }

    .contract-editor-card .form-control:focus,
    .contract-editor-card .form-select:focus {
        border-color: #3d7aa4;
        box-shadow: 0 0 0 0.2rem rgba(61, 122, 164, 0.16);
    }

    .contract-editor-card .border.rounded.p-3 {
        background: linear-gradient(180deg, #fafcff, #f7fafc);
        border-radius: 1rem !important;
        border-color: rgba(15, 23, 42, 0.1) !important;
    }

    .contract-actions {
        position: sticky;
        bottom: 0;
        z-index: 3;
        margin-top: 1rem;
        padding-top: 1rem;
        background: linear-gradient(180deg, rgba(255,255,255,0.75), #fff 40%);
        backdrop-filter: blur(8px);
    }

    .contract-actions .btn {
        border-radius: 0.85rem;
    }
</style>

<div class="contract-editor-shell shadow-sm mb-4">
    <div class="contract-editor-banner px-4 px-lg-5 py-4 py-lg-5">
        <div class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center">
            <div class="me-auto">
                <div class="small text-uppercase fw-semibold text-muted mb-1"><?= $isEdit ? 'Edit Contract' : 'New Contract' ?></div>
                <h1 class="display-6 fw-semibold mb-2"><?= $isEdit ? 'Edit Contract' : 'Create Contract' ?></h1>
                <div class="text-muted">A cleaner, better-aligned entry form for contract setup and updates.</div>
            </div>
            <?php if ($isEdit && $contractId): ?>
                <div class="badge text-bg-light text-dark px-3 py-2">Contract #<?= h($contract['contract_number'] ?? ('ID ' . $contractId)) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($flashErrors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($flashErrors as $err): ?>
        <li><?= h($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="<?= h($action) ?>" class="card shadow-lg contract-editor-card">

    <div class="card-body p-4 p-lg-5">
        <div class="row g-4">

            <div class="col-md-6">
                <label class="form-label" for="contract_name">Contract Name</label>
                <input class="form-control" type="text" id="contract_name" name="name" required
                       value="<?= h($contract['name'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="contract_number">Contract Number (Auto-Generated)</label>
                <input class="form-control bg-light text-muted" type="text" id="contract_number" name="contract_number"
                       value="<?= h($contract['contract_number'] ?? '') ?>">
            </div>


            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <?php
                  $descVal = $contract['description'] ?? '';
                  if ($descVal === '' && empty($isEdit)) {
                      $cName = trim((string)($contract['name'] ?? ''));
                      $descVal = ($cName !== '' ? $cName : '[Contract Name]') . ' as further described under the terms and conditions set forth in Exhibit A';
                  }
                ?>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="[Contract Name] as further described under the terms and conditions set forth in Exhibit A"><?= h($descVal) ?></textarea>
            </div>



            <div class="col-md-4">
                <label class="form-label">Contract Type</label>
                <select class="form-select" name="contract_type_id">
                    <option value="">(none)</option>
                    <?php foreach (($types ?? []) as $t): ?>
                        <option value="<?= (int)$t['contract_type_id'] ?>"
                            <?= ((string)($contract['contract_type_id'] ?? '') === (string)$t['contract_type_id']) ? 'selected' : '' ?>>
                            <?= h($t['contract_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="use_standard_contract"
                           id="use_standard_contract" value="1"
                           <?= !empty($contract['use_standard_contract']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="use_standard_contract">
                        Use Standard Contract
                        <span class="text-muted small d-block">Waives approvals flagged as standard-contract exempt</span>
                    </label>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="minimum_insurance_coi"
                           id="minimum_insurance_coi" value="1"
                           <?= !empty($contract['minimum_insurance_coi']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="minimum_insurance_coi">
                        Existing COI with $5 Million coverage?
                        <span class="text-muted small d-block">Waives Risk Manager review if flagged as insurance-exempt</span>
                    </label>
                </div>
                <?php if ($isEdit && $contractId): ?>
                <?php
                    $rmTo      = $riskManagerEmails ?? '';
                    $rmCnum    = $contract['contract_number'] ?? ('ID ' . $contractId);
                    $rmName    = $contract['name'] ?? '';
                    $rmPerson  = trim((current_person()['first_name'] ?? '') . ' ' . (current_person()['last_name'] ?? ''));
                    $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $rmUrl     = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/index.php?page=contracts_show&contract_id=' . (int)$contractId;
                    $rmSubject = rawurlencode('Request: Please Consider Reduced Insurance Requirements — Contract ' . $rmCnum);
                    $rmBody    = rawurlencode(
                        "Please consider reduced insurance requirements for the following contract:\r\n\r\n" .
                        $rmCnum . ($rmName ? ' — ' . $rmName : '') . "\r\n\r\n" .
                        "View the contract here:\r\n" . $rmUrl . "\r\n\r\n" .
                        "Contact " . ($rmPerson ?: 'the contract manager') . " for additional details."
                    );
                    $rmMailto  = 'mailto:' . $rmTo . '?subject=' . $rmSubject . '&body=' . $rmBody;
                ?>
                <div class="mt-2">
                    <a href="<?= h($rmMailto) ?>" class="btn btn-sm btn-outline-primary">
                        &#128231; Email Risk Manager for reduced insurance
                    </a>
                    <div class="form-text text-muted">Opens your email client &mdash; pre-populated and editable before sending.</div>
                </div>
                <?php endif; ?>
            </div>

                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 bg-light-subtle h-100 shadow-sm">
                                <div class="small text-uppercase fw-semibold text-muted mb-2">Payment / Contract Value</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Payment Type</label>
                                        <select class="form-select" name="payment_terms_id">
                                                <option value="">(none)</option>
                                                <?php foreach (($paymentTerms ?? []) as $pt): ?>
                                                        <option value="<?= (int)$pt['payment_terms_id'] ?>"
                                                                <?= ((string)($contract['payment_terms_id'] ?? '') === (string)$pt['payment_terms_id']) ? 'selected' : '' ?>>
                                                                <?= h($pt['name']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold" for="total_contract_value">Contract Value</label>
                                        <input class="form-control form-control-lg" type="text" id="total_contract_value" name="total_contract_value"
                                                     value="<?= h($contract['total_contract_value'] ?? '') ?>"
                                                     placeholder="Enter contract value">
                                    </div>
                                </div>
                            </div>
                        </div>

            <div class="col-md-4">
                <label class="form-label">Department</label>
                <select class="form-select" name="department_id">
                    <option value="">(none)</option>
                    <?php foreach (($departments ?? []) as $d): ?>
                        <option value="<?= (int)$d['department_id'] ?>"
                            <?= ((string)($contract['department_id'] ?? '') === (string)$d['department_id']) ? 'selected' : '' ?>>
                            <?= h($d['department_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-8">
                <div class="border rounded-4 p-3 bg-light-subtle h-100 shadow-sm">
                    <div class="small text-uppercase fw-semibold text-muted mb-2">Workflow Status</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <?php $currentStatusId = $contract['contract_status_id'] ?? ''; ?>
                            <select class="form-select" name="contract_status_id" required>
                                <option value="">Select…</option>
                                <?php foreach (($contractStatuses ?? []) as $status): ?>
                                    <option value="<?= (int)$status['contract_status_id'] ?>" <?= ((string)$currentStatusId === (string)$status['contract_status_id']) ? 'selected' : '' ?>>
                                        <?= h($status['contract_status_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Status Comment</label>
                            <input type="text" class="form-control" name="status_comment"
                                   maxlength="255"
                                   value="<?= h($contract['status_comment'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <input type="hidden" name="owner_company_id" value="<?= h($contract['owner_company_id'] ?? '') ?>">

                            <label class="form-label">Responsible Person</label>
                            <select class="form-select" name="owner_primary_contact_id">
                                <option value="">(none)</option>
                                <?php foreach (($ownerPeople ?? []) as $p): ?>
                                    <?php
                                    $nm = trim((string)($p['full_name'] ?? ''));
                                    if ($nm === '') {
                                        $nm = trim((string)($p['first_name'] ?? '') . ' ' . (string)($p['last_name'] ?? ''));
                                    }
                                    $label = $nm . (!empty($p['email']) ? ' — ' . $p['email'] : '');
                                    ?>
                                    <option value="<?= (int)$p['person_id'] ?>"
                                        <?= ((string)($contract['owner_primary_contact_id'] ?? '') === (string)$p['person_id']) ? 'selected' : '' ?>>
                                        <?= h($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

                        <div class="col-12">
                            <div class="border rounded-4 p-3 bg-light-subtle shadow-sm">
                                <div class="small text-uppercase fw-semibold text-muted mb-2">Vendor Information</div>
                                <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Vendor Company</label>
                <select class="form-select" id="counterparty_company_id" name="counterparty_company_id">
                    <option value="">Select…</option>
                    <?php foreach (($companies ?? []) as $co): ?>
                        <option value="<?= (int)$co['company_id'] ?>"
                            <?= ((string)($contract['counterparty_company_id'] ?? '') === (string)$co['company_id']) ? 'selected' : '' ?>>
                            <?= h($co['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Vendor Primary Contact</label>
                <select class="form-select" id="counterparty_contact_select">
                    <?php if (!empty($contract['counterparty_contact_name'])): ?>
                        <option value=""
                            data-name="<?= h($contract['counterparty_contact_name']) ?>"
                            data-email="<?= h($contract['counterparty_contact_email'] ?? '') ?>"
                            selected>
                            <?= h($contract['counterparty_contact_name']) ?>
                            <?= !empty($contract['counterparty_contact_email']) ? ' — ' . h($contract['counterparty_contact_email']) : '' ?>
                        </option>
                    <?php else: ?>
                        <option value="">— Select company first —</option>
                    <?php endif; ?>
                </select>
                <input type="hidden" name="counterparty_contact_name"  id="counterparty_contact_name"
                       value="<?= h($contract['counterparty_contact_name'] ?? '') ?>">
                <input type="hidden" name="counterparty_contact_email" id="counterparty_contact_email"
                       value="<?= h($contract['counterparty_contact_email'] ?? '') ?>">
            </div>

                </div>
              </div>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="start_date">Start Date</label>
                <input class="form-control" type="date" id="start_date" name="start_date"
                       value="<?= h($contract['start_date'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="end_date">End Date</label>
                <input class="form-control" type="date" id="end_date" name="end_date"
                       value="<?= h($contract['end_date'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="governing_law">Governing Law</label>
                <input class="form-control" type="text" id="governing_law" name="governing_law"
                       value="<?= h($contract['governing_law'] ?? 'North Carolina') ?>">
            </div>


            <input type="hidden" id="po_number" name="po_number" value="<?= h($contract['po_number'] ?? '') ?>">

            <div class="col-md-3">
                <label class="form-label" for="account_number">Account Number</label>
                <input class="form-control" type="text" id="account_number" name="account_number"
                       maxlength="20"
                       value="<?= h($contract['account_number'] ?? '') ?>">
            </div>

            <input type="hidden" id="po_amount" name="po_amount" value="<?= h($contract['po_amount'] ?? '') ?>">

            <div class="col-md-4">
                <label class="form-label" for="date_approved_by_procurement">Date Approved by Procurement</label>
                <input class="form-control" type="date" id="date_approved_by_procurement" name="date_approved_by_procurement"
                       value="<?= h($contract['date_approved_by_procurement'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="date_approved_by_manager">Date Approved by Manager</label>
                <input class="form-control" type="date" id="date_approved_by_manager" name="date_approved_by_manager"
                       value="<?= h($contract['date_approved_by_manager'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="date_approved_by_council">Date Approved by Council</label>
                <input class="form-control" type="date" id="date_approved_by_council" name="date_approved_by_council"
                       value="<?= h($contract['date_approved_by_council'] ?? '') ?>">
            </div>

            <div class="col-12">
                <label class="form-label" for="documents_path">Contract Documents Path</label>
                <input class="form-control" type="text" id="documents_path" name="documents_path" maxlength="255"
                       placeholder="e.g. \\server\contracts\2025"
                       value="<?= h($contract['documents_path'] ?? '') ?>">
            </div>

            <div class="col-12">
              <hr class="my-2">
              <h6 class="text-muted mb-3">
                <?php if (!empty($complianceInfoLink)): ?>
                  <a href="<?= h($complianceInfoLink) ?>" target="_blank" rel="noopener noreferrer">Procurement &amp; Public Bidding Compliance</a>
                <?php else: ?>
                  Procurement &amp; Public Bidding Compliance
                <?php endif; ?>
              </h6>
              <div class="row g-3">

                <div class="col-md-4">
                  <label class="form-label" for="procurement_method">Procurement Method</label>
                  <select class="form-select" id="procurement_method" name="procurement_method">
                    <option value="">— Select —</option>
                    <?php
                      $procMethods = [
                        'Competitive Bid (IFB)',
                        'Request for Proposals (RFP)',
                        'Sole Source / Single Source',
                        'Emergency Purchase',
                        'Cooperative / Piggyback Purchase',
                        'Small / Informal Purchase (below threshold)',
                        'Professional Services (QBS)',
                        'Service (non QBS)',
                        'Not Required',
                      ];
                      $currentMethod = $contract['procurement_method'] ?? '';
                      foreach ($procMethods as $m):
                    ?>
                      <option value="<?= h($m) ?>" <?= $currentMethod === $m ? 'selected' : '' ?>><?= h($m) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="bid_rfp_number">Bid / RFP Number</label>
                  <input class="form-control" type="text" id="bid_rfp_number" name="bid_rfp_number" maxlength="100"
                         placeholder="e.g. IFB-2025-012"
                         value="<?= h($contract['bid_rfp_number'] ?? '') ?>">
                </div>



                <div class="col-12">
                  <label class="form-label" for="procurement_notes">Explain Compliance with Public Bidding / Procurement Laws</label>
                  <textarea class="form-control" id="procurement_notes" name="procurement_notes" rows="5"
                            placeholder="Describe how this contract complies with public bidding and procurement requirements, any exemptions that apply, or why competitive bidding was not required."><?= h($contract['procurement_notes'] ?? '') ?></textarea>
                </div>

              </div>
            </div>

            <div class="col-12 contract-actions d-flex flex-wrap gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Update Contract' : 'Create Contract' ?>
                </button>

                <a href="/index.php?page=contracts" class="btn btn-outline-secondary">Cancel</a>

                <?php if ($isEdit && $contractId): ?>
                    <a href="/index.php?page=contracts_show&contract_id=<?= urlencode((string)$contractId) ?>"
                       class="btn btn-outline-secondary">
                        Back to Details
                    </a>
                    <a href="/index.php?page=contracts_generate_html&contract_id=<?= urlencode((string)$contractId) ?>"
                       target="_blank"
                       class="btn btn-outline-success">
                        Generate HTML
                    </a>
                    <a href="/index.php?page=contracts_generate_word&contract_id=<?= urlencode((string)$contractId) ?>"
                       class="btn btn-outline-info">
                        Generate Word Doc
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</form>

<script>
(function () {
    const companySel = document.getElementById('counterparty_company_id');
    const contactSel = document.getElementById('counterparty_contact_select');
    const nameInput  = document.getElementById('counterparty_contact_name');
    const emailInput = document.getElementById('counterparty_contact_email');

    function loadContacts(companyId, restoreName) {
        if (!companyId) {
            contactSel.innerHTML = '<option value="">— Select company first —</option>';
            return;
        }
        fetch('/index.php?page=api_company_contacts&company_id=' + encodeURIComponent(companyId))
            .then(r => r.json())
            .then(contacts => {
                contactSel.innerHTML = '<option value="" data-name="" data-email="">(none)</option>';
                contacts.forEach(c => {
                    const opt = document.createElement('option');
                    opt.dataset.name  = c.name;
                    opt.dataset.email = c.email || '';
                    opt.textContent   = c.name + (c.email ? ' — ' + c.email : '');
                    if (restoreName && c.name === restoreName) opt.selected = true;
                    contactSel.appendChild(opt);
                });
                // If no match found but we have a saved name, show it as a placeholder option
                if (restoreName && !contactSel.querySelector('[data-name="' + CSS.escape(restoreName) + '"]')) {
                    const opt = document.createElement('option');
                    opt.dataset.name  = restoreName;
                    opt.dataset.email = emailInput.value;
                    opt.textContent   = restoreName + (emailInput.value ? ' — ' + emailInput.value : '') + ' (saved)';
                    opt.selected = true;
                    contactSel.insertBefore(opt, contactSel.firstChild.nextSibling);
                }
                syncHidden();
            })
            .catch(() => {
                contactSel.innerHTML = '<option value="">Unable to load contacts</option>';
            });
    }

    function syncHidden() {
        const opt = contactSel.options[contactSel.selectedIndex];
        nameInput.value  = opt ? (opt.dataset.name  || '') : '';
        emailInput.value = opt ? (opt.dataset.email || '') : '';
    }

    companySel.addEventListener('change', function () {
        nameInput.value  = '';
        emailInput.value = '';
        loadContacts(this.value, null);
    });

    contactSel.addEventListener('change', syncHidden);

    // On page load: if a company is already selected, reload its contacts
    // and restore the saved contact name selection
    const savedName = nameInput.value;
    if (companySel.value) {
        loadContacts(companySel.value, savedName || null);
    }
})();
</script>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>