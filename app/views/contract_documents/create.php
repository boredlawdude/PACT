<?php
declare(strict_types=1);
// contract_documents/create.php - Document upload form (MVC)
if (!function_exists('h')) {
    function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>
<div class="container mt-4">
    <h2 class="h5 mb-3">Upload Document for Contract #<?= (int)$contract_id ?></h2>
    <form method="post" enctype="multipart/form-data" action="/index.php?page=contract_documents_store&contract_id=<?= (int)$contract_id ?>">
        <div class="mb-3">
            <label for="file_name" class="form-label">Document Name</label>
            <input type="text" class="form-control" id="file_name" name="file_name" required>
        </div>
        <div class="mb-3">
            <label for="doc_type" class="form-label">Document Type</label>
            <input type="text" class="form-control" id="doc_type" name="doc_type" required>
        </div>
        <div class="mb-3">
            <label for="file_upload" class="form-label">Select File</label>
            <input type="file" class="form-control" id="file_upload" name="file_upload" required>
        </div>
        <input type="hidden" name="contract_id" value="<?= (int)$contract_id ?>">
        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="/index.php?page=contracts_show&contract_id=<?= (int)$contract_id ?>" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
