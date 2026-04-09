<?php
// app/views/admin_settings/roles.php
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>
<div class="container mt-4">
    <h2 class="h4 mb-3">User Roles</h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Role saved successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
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

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:130px">Role Key</th>
                        <th style="width:180px">Display Name</th>
                        <th>Description</th>
                        <th style="width:80px" class="text-center">Active</th>
                        <th style="width:140px"></th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($roles as $role): ?>
                    <tr>
                        <form method="post" action="/index.php?page=admin_roles_update">
                            <input type="hidden" name="role_id" value="<?= (int)$role['role_id'] ?>">

                            <td>
                                <input type="text" name="role_key"
                                       class="form-control form-control-sm font-monospace text-uppercase"
                                       value="<?= h($role['role_key']) ?>"
                                       required pattern="[A-Z0-9_]+"
                                       title="Uppercase letters, numbers and underscores only">
                            </td>
                            <td>
                                <input type="text" name="role_name"
                                       class="form-control form-control-sm"
                                       value="<?= h($role['role_name']) ?>"
                                       required>
                            </td>
                            <td>
                                <input type="text" name="description"
                                       class="form-control form-control-sm"
                                       value="<?= h($role['description'] ?? '') ?>"
                                       placeholder="Optional description">
                            </td>
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           <?= $role['is_active'] ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td>
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                        </form>
                        <form method="post" action="/index.php?page=admin_roles_delete" class="d-inline">
                            <input type="hidden" name="role_id" value="<?= (int)$role['role_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger ms-1"
                                    onclick="return confirm('Delete role &quot;<?= h($role['role_name']) ?>&quot;? This cannot be undone.')">
                                Delete
                            </button>
                        </form>
                            </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Add new row -->
                <tr class="table-success">
                    <form method="post" action="/index.php?page=admin_roles_create">
                        <td>
                            <input type="text" name="role_key"
                                   class="form-control form-control-sm font-monospace text-uppercase"
                                   placeholder="NEW_ROLE"
                                   pattern="[A-Z0-9_]+"
                                   title="Uppercase letters, numbers and underscores only"
                                   required>
                        </td>
                        <td>
                            <input type="text" name="role_name"
                                   class="form-control form-control-sm"
                                   placeholder="Display Name"
                                   required>
                        </td>
                        <td>
                            <input type="text" name="description"
                                   class="form-control form-control-sm"
                                   placeholder="Optional description">
                        </td>
                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                            </div>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-sm btn-success">Add Role</button>
                        </td>
                    </form>
                </tr>

                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 text-muted small">
        <strong>Role Key</strong> is the internal identifier used in code (e.g. <code>ADMIN</code>, <code>VIEWER</code>). It must be unique and uppercase.<br>
        Roles currently assigned to users cannot be deleted.
    </div>

    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary mt-4">← Back to System Settings</a>
</div>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
