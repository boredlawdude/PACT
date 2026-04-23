<?php
declare(strict_types=1);

$isEdit = ($mode ?? 'create') === 'edit';
$changeOrderId = $changeOrder['change_order_id'] ?? null;
$contractId    = $contract['contract_id'] ?? ($changeOrder['contract_id'] ?? null);

$action = $isEdit
    ? '/index.php?page=change_orders_update&change_order_id=' . urlencode((string)$changeOrderId)
    : '/index.php?page=change_orders_store';

$actionPrint = $isEdit
    ? '/index.php?page=change_orders_update_print&change_order_id=' . urlencode((string)$changeOrderId)
    : '/index.php?page=change_orders_store_print';

if (!function_exists('h')) {
    function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="d-flex align-items-center mb-3">
    <h1 class="h4 me-auto">
        <?= $isEdit ? 'Edit Change Order' : 'Add Change Order' ?>
        <span class="text-muted fs-6 fw-normal ms-2">
            &mdash; <?= h($contract['name'] ?? '') ?>
            <?php if (!empty($contract['contract_number'])): ?>
                (<?= h($contract['contract_number']) ?>)
            <?php endif; ?>
        </span>
    </h1>
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

<form method="post" action="<?= h($action) ?>" id="co-form" class="card shadow-sm">
    <input type="hidden" name="contract_id" value="<?= (int)$contractId ?>">

    <div class="card-header fw-semibold">
        <?= $isEdit ? 'Edit Change Order' : 'New Change Order' ?>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label" for="change_order_number">Change Order Number <span class="text-danger">*</span></label>
                <input class="form-control" type="text" id="change_order_number" name="change_order_number"
                       required maxlength="50"
                       value="<?= h($changeOrder['change_order_number'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="co_amount">Amount ($)</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input class="form-control" type="text" id="co_amount" name="co_amount"
                           placeholder="0.00"
                           value="<?= h($changeOrder['co_amount'] ?? '') ?>">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="approval_date">Approval Date</label>
                <input class="form-control" type="date" id="approval_date" name="approval_date"
                       value="<?= h($changeOrder['approval_date'] ?? '') ?>">
            </div>

            <div class="col-12">
                <label class="form-label" for="co_justification">Justification</label>
                <textarea class="form-control" id="co_justification" name="co_justification"
                          rows="4"
                          placeholder="Describe the reason for this change order…"><?= h($changeOrder['co_justification'] ?? '') ?></textarea>
            </div>

        </div>
    </div>

    <div class="card-footer d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Update Change Order' : 'Save Change Order' ?>
        </button>
        <button type="submit" class="btn btn-success"
                onclick="document.getElementById('co-form').action='<?= h($actionPrint) ?>'">
            &#128438; Save &amp; Print Generic CO Form
        </button>
        <a href="/index.php?page=contracts_show&contract_id=<?= (int)$contractId ?>#change-orders"
           class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
