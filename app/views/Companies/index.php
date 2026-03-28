<?php
declare(strict_types=1);

//require APP_ROOT . '/app/views/layouts/header.php';

if (!function_exists('h')) {
    function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="d-flex align-items-center mb-3">
    <h1 class="h4 me-auto">Companies</h1>
    <a class="btn btn-primary btn-sm" href="/index.php?page=companies_create">New Company</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Vendor ID</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Verified By</th>
                    <th>Active</th>
                    <th style="width: 120px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($companies)): ?>
                    <?php foreach ($companies as $r): ?>
                        <tr>
                            <td><?= (int)$r['company_id'] ?></td>
                            <td><?= h($r['name'] ?? '') ?></td>
                            <td><?= h($r['vendor_id'] ?? '') ?></td>
                            <td><?= h($r['contact_name'] ?? '') ?></td>
                            <td><?= h($r['email'] ?? '') ?></td>
                            <td><?= h($r['phone'] ?? '') ?></td>
                            <td><?= h($r['verified_by'] ?? '') ?></td>
                            <td><?= ((int)($r['is_active'] ?? 0) === 1) ? 'Yes' : 'No' ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="/index.php?page=companies_edit&company_id=<?= (int)$r['company_id'] ?>">
                                    Edit
                                </a>
                                <a class="btn btn-sm btn-outline-secondary ms-1"
                                   href="/index.php?page=contracts_search&company_id=<?= (int)$r['company_id'] ?>">
                                    See all Contracts
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-muted p-3">No companies yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>