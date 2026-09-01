<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
$contractId = (int)($contractId ?? $_GET['contract_id'] ?? 0);
$changeOrderId = (int)($changeOrderId ?? $_GET['change_order_id'] ?? 0);
if (!isset($documentCategories)) {
    require_once APP_ROOT . '/app/models/DocumentCategory.php';
    $documentCategories = (new DocumentCategory(db()))->active();
}
?>
<div class="container mt-4" style="max-width: 600px;">
    <h2 class="h5 mb-3">Upload Document for Contract #<?= $contractId ?></h2>

    <form method="post" enctype="multipart/form-data" action="/index.php?page=contract_documents_store">
        <input type="hidden" name="contract_id" value="<?= $contractId ?>">
        <?php if ($changeOrderId > 0): ?>
            <input type="hidden" name="change_order_id" value="<?= $changeOrderId ?>">
        <?php endif; ?>

        <!-- Document Category -->
        <div class="mb-3">
            <label class="form-label">Document Category</label>
            <select id="doc_category" name="doc_category" class="form-select" required onchange="toggleExhibitFields()">
                <option value="">— Select —</option>
                <?php foreach ($documentCategories as $cat): ?>
                    <option value="<?= h($cat['category_key']) ?>"><?= h($cat['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Exhibit-specific fields (hidden by default) -->
        <div id="exhibit_fields" style="display:none;">
            <div class="mb-3">
                <label for="exhibit_letter" class="form-label">Exhibit Letter</label>
                <select id="exhibit_letter" name="exhibit_letter" class="form-select">
                    <option value="">— Select —</option>
                    <?php foreach (range('A', 'Z') as $letter): ?>
                        <option value="<?= $letter ?>"><?= $letter ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Exhibit Description</label>
                <input type="text" class="form-control" id="description" name="description" maxlength="255" placeholder="e.g. Scope of Work">
            </div>
        </div>

        <!-- PDF stamp label -->
        <div class="mb-3">
            <label for="exhibit_label" class="form-label">PDF Stamp Label <small class="text-muted">(leave blank for no stamp)</small></label>
            <input type="text" class="form-control" id="exhibit_label" name="exhibit_label" maxlength="50" placeholder="e.g. Exhibit A, Contract">
        </div>

        <!-- File upload -->
        <div class="mb-3">
            <label for="file_upload" class="form-label">Select File</label>
            <input type="file" class="form-control" id="file_upload" name="file_upload" required>
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="/index.php?page=contracts_show&contract_id=<?= $contractId ?>" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<script>
function toggleExhibitFields() {
    var cat = document.getElementById('doc_category').value;
    document.getElementById('exhibit_fields').style.display = (cat === 'exhibit') ? '' : 'none';
    document.getElementById('exhibit_letter').required = (cat === 'exhibit');
}
<?php if ($changeOrderId > 0): ?>
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('doc_category');
    sel.value = 'change_order';
    toggleExhibitFields();
});
<?php endif; ?>
</script>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
