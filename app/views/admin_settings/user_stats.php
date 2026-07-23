<?php
// app/views/admin_settings/user_stats.php
?>

<div class="container mt-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h1 class="h3 mb-0">User Activity Stats</h1>
        <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary">Back to System Settings</a>
    </div>

    <?php if (empty($hasLoginEvents)): ?>
        <div class="alert alert-warning">
            <strong>Login event tracking is not enabled yet.</strong><br>
            Run <code>user_login_events_migration.sql</code> to enable "Logins (Last 30 Days)" counts.
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Users</div>
                    <div class="fs-4 fw-semibold"><?= (int)$totals['users'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Can Login</div>
                    <div class="fs-4 fw-semibold"><?= (int)$totals['can_login'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Active</div>
                    <div class="fs-4 fw-semibold"><?= (int)$totals['active'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Logins (30d)</div>
                    <div class="fs-4 fw-semibold"><?= (int)$totals['logins_30d'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Records Created</div>
                    <div class="fs-4 fw-semibold"><?= (int)$totals['created'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Records Edited</div>
                    <div class="fs-4 fw-semibold"><?= (int)$totals['edited'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Per-User Detail</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Last Login</th>
                        <th class="text-end">Logins (30d)</th>
                        <th class="text-end">Records Created</th>
                        <th class="text-end">Records Edited</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($people)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($people as $person): ?>
                            <tr>
                                <td><?= h($person['name'] ?? '') ?></td>
                                <td><?= h($person['email'] ?? '') ?></td>
                                <td><?= h($person['role_names'] ?? '—') ?></td>
                                <td><?= !empty($person['last_login_at']) ? h($person['last_login_at']) : '—' ?></td>
                                <td class="text-end"><?= $person['logins_30d'] === null ? 'N/A' : (int)$person['logins_30d'] ?></td>
                                <td class="text-end"><?= (int)($person['records_created'] ?? 0) ?></td>
                                <td class="text-end"><?= (int)($person['records_edited'] ?? 0) ?></td>
                                <td>
                                    <?php if ((int)($person['is_active'] ?? 0) !== 1): ?>
                                        <span class="badge text-bg-danger">Inactive</span>
                                    <?php elseif ((int)($person['can_login'] ?? 0) !== 1): ?>
                                        <span class="badge text-bg-secondary">No Login</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted small mt-3 mb-0">
        "Records Created" and "Records Edited" are based on tracked activity tables (history, comments, documents, milestones, and related audit entries).
    </p>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
