<?php
// app/views/admin_settings/document_categories.php
if (!function_exists('h')) {
    function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>
<div class="container mt-4">
    <h2 class="h4 mb-3">Document Categories</h2>
    <p class="text-muted">
        Controls the options shown in the <strong>Document Category</strong> dropdown when uploading a
        document to a contract. Built-in categories (marked <span class="badge text-bg-secondary">Built-in</span>)
        can be renamed or deactivated but not deleted, since some of them (Exhibit, Change Order) drive
        extra behavior elsewhere in the app.
    </p>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Document category saved successfully.</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= h($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <table class="table table-bordered align-middle bg-white">
        <thead>
            <tr>
                <th style="width:70px">ID</th>
                <th>Label</th>
                <th style="width:160px">Key</th>
                <th style="width:90px">Active</th>
                <th style="width:100px">Sort</th>
                <th style="width:150px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <form method="post" action="/index.php?page=admin_document_categories_update">
                        <input type="hidden" name="category_id" value="<?= (int)$c['category_id'] ?>">
                        <td><?= (int)$c['category_id'] ?></td>
                        <td><input type="text" name="label" value="<?= h($c['label']) ?>" class="form-control form-control-sm" required></td>
                        <td>
                            <code class="small text-muted"><?= h($c['category_key']) ?></code>
                            <?php if (!empty($c['is_system'])): ?>
                                <span class="badge text-bg-secondary ms-1">Built-in</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" <?= !empty($c['is_active']) ? 'checked' : '' ?>>
                        </td>
                        <td><input type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>" class="form-control form-control-sm"></td>
                        <td>
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                    <?php if (empty($c['is_system'])): ?>
                        <button type="button" class="btn btn-sm btn-danger ms-1"
                                onclick="if(confirm('Delete this document category?')){var f=document.createElement('form');f.method='post';f.action='/index.php?page=admin_document_categories_delete';var i=document.createElement('input');i.type='hidden';i.name='category_id';i.value='<?= (int)$c['category_id'] ?>';f.appendChild(i);document.body.appendChild(f);f.submit();}">Delete</button>
                    <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <form method="post" action="/index.php?page=admin_document_categories_create">
                    <td></td>
                    <td><input type="text" name="label" class="form-control form-control-sm" placeholder="New document category label" required></td>
                    <td class="text-muted small">(auto-generated)</td>
                    <td></td>
                    <td></td>
                    <td><button type="submit" class="btn btn-sm btn-success">Add</button></td>
                </form>
            </tr>
        </tbody>
    </table>
    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary mt-3">Back to System Settings</a>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
