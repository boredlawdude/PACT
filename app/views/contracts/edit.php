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

<div class="d-flex align-items-center mb-3">
    <h1 class="h4 me-auto"><?= $isEdit ? 'Edit Contract' : 'Create Contract' ?></h1>
</div>

<form method="post" action="<?= h($action) ?>" class="card shadow-sm">
    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label" for="contract_number">Contract Number (Auto-Generated)</label>
                <input class="form-control bg-light text-muted" type="text" id="contract_number" name="contract_number"
                       value="<?= h($contract['contract_number'] ?? '') ?>">
            </div>

            <div class="col-md-8">
                <label class="form-label" for="name">Name</label>
                <input class="form-control" type="text" id="name" name="name"
                       value="<?= h($contract['name'] ?? '') ?>" required>
            </div>

            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= h($contract['description'] ?? '') ?></textarea>
            </div>

            <div class="col-12">
                <label class="form-label" for="contract_body_html">Contract Body HTML</label>
                <textarea class="form-control" id="contract_body_html" name="contract_body_html" rows="6"><?= h($contract['contract_body_html'] ?? '') ?></textarea>
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

            <div class="col-md-4">
                <label class="form-label">Status</label>
                <?php $currentStatus = $contract['status'] ?? 'draft'; ?>
                <select class="form-select" name="status">
                    <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="in_review" <?= $currentStatus === 'in_review' ? 'selected' : '' ?>>In Review</option>
                    <option value="signed" <?= $currentStatus === 'signed' ? 'selected' : '' ?>>Signed</option>
                    <option value="expired" <?= $currentStatus === 'expired' ? 'selected' : '' ?>>Expired</option>
                    <option value="terminated" <?= $currentStatus === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Owner Company</label>
                <select class="form-select" name="owner_company_id" required>
                    <option value="">Select…</option>
                    <?php foreach (($companies ?? []) as $co): ?>
                        <option value="<?= (int)$co['company_id'] ?>"
                            <?= ((string)($contract['owner_company_id'] ?? '') === (string)$co['company_id']) ? 'selected' : '' ?>>
                            <?= h($co['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Counterparty Company</label>
                <select class="form-select" name="counterparty_company_id" required>
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

            <div class="col-md-6">
                <label class="form-label">Counterparty Primary Contact</label>
                <select class="form-select" name="counterparty_primary_contact_id">
                    <option value="">(none)</option>
                    <?php foreach (($counterpartyPeople ?? []) as $p): ?>
                        <?php
                        $nm = trim((string)($p['full_name'] ?? ''));
                        if ($nm === '') {
                            $nm = trim((string)($p['first_name'] ?? '') . ' ' . (string)($p['last_name'] ?? ''));
                        }
                        $label = $nm . (!empty($p['email']) ? ' — ' . $p['email'] : '');
                        ?>
                        <option value="<?= (int)$p['person_id'] ?>"
                            <?= ((string)($contract['counterparty_primary_contact_id'] ?? '') === (string)$p['person_id']) ? 'selected' : '' ?>>
                            <?= h($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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

            <div class="col-md-3">
                <label class="form-label" for="currency">Currency</label>
                <input class="form-control" type="text" id="currency" name="currency" maxlength="3"
                       value="<?= h($contract['currency'] ?? 'USD') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label" for="total_contract_value">Total Contract Value</label>
                <input class="form-control" type="text" id="total_contract_value" name="total_contract_value"
                       value="<?= h($contract['total_contract_value'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label" for="renewal_term_months">Renewal Term (Months)</label>
                <input class="form-control" type="number" id="renewal_term_months" name="renewal_term_months"
                       value="<?= h($contract['renewal_term_months'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label d-block">Auto Renew</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="auto_renew" name="auto_renew" value="1"
                        <?= !empty($contract['auto_renew']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="auto_renew">Yes</label>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label" for="documents_path">Documents Path</label>
                <input class="form-control" type="text" id="documents_path" name="documents_path"
                       value="<?= h($contract['documents_path'] ?? '') ?>">
            </div>

            <div class="col-12 d-flex gap-2 mt-3">
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

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>