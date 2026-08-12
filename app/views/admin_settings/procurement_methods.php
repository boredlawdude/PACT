<?php
// app/views/admin_settings/procurement_methods.php
?>
<div class="container mt-4">
    <h2 class="h4 mb-3">Procurement Methods</h2>
    <p class="text-muted">
        Only the <strong>Short Desc</strong> is shown in the Procurement Method dropdown on contracts.
        Both Short Desc and Long Desc are available as merge fields (<code>${procurement_method}</code> /
        <code>${procurement_method_long_desc}</code>) when generating documents.
    </p>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Procurement method saved successfully.</div>
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
                <th>Short Desc</th>
                <th>Long Desc</th>
                <th style="width:120px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($methods as $m): ?>
                <tr>
                    <form method="post" action="/index.php?page=admin_procurement_methods_update">
                        <input type="hidden" name="procurement_method_id" value="<?= (int)$m['procurement_method_id'] ?>">
                        <td><?= (int)$m['procurement_method_id'] ?></td>
                        <td><input type="text" name="short_desc" value="<?= h($m['short_desc']) ?>" class="form-control form-control-sm" required></td>
                        <td><textarea name="long_desc" class="form-control form-control-sm" rows="2"><?= h($m['long_desc'] ?? '') ?></textarea></td>
                        <td>
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                    <button type="button" class="btn btn-sm btn-danger ms-1"
                            onclick="if(confirm('Delete this procurement method?')){var f=document.createElement('form');f.method='post';f.action='/index.php?page=admin_procurement_methods_delete';var i=document.createElement('input');i.type='hidden';i.name='procurement_method_id';i.value='<?= (int)$m['procurement_method_id'] ?>';f.appendChild(i);document.body.appendChild(f);f.submit();}">Delete</button>
                        </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <form method="post" action="/index.php?page=admin_procurement_methods_create">
                    <td></td>
                    <td><input type="text" name="short_desc" class="form-control form-control-sm" placeholder="New procurement method short desc" required></td>
                    <td><textarea name="long_desc" class="form-control form-control-sm" rows="2" placeholder="Long description (optional)"></textarea></td>
                    <td><button type="submit" class="btn btn-sm btn-success">Add</button></td>
                </form>
            </tr>
        </tbody>
    </table>
    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary mt-3">Back to System Settings</a>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
