<?php
// app/views/admin_settings/town_locations.php
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Town Locations</h2>
        <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary btn-sm">&larr; Back to Settings</a>
    </div>
    <p class="text-muted">
        Town-owned addresses (Town Hall, Public Works, Water Plant, Parks &amp; Rec, etc.) that a contract's
        deliverables or on-site work can be assigned to. Selectable on the Contract Edit form as
        &ldquo;Town Location for Shipping or Work&rdquo;.
    </p>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Saved successfully.</div>
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
                <th>ID</th>
                <th>Name</th>
                <th>Address Line 1</th>
                <th>Address Line 2</th>
                <th>City</th>
                <th>State</th>
                <th>Zip</th>
                <th>Active</th>
                <th style="width:120px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($locations as $loc): ?>
                <tr>
                    <form method="post" action="/index.php?page=admin_town_locations_update">
                        <input type="hidden" name="location_id" value="<?= (int)$loc['location_id'] ?>">
                        <td><?= (int)$loc['location_id'] ?></td>
                        <td><input type="text" name="location_name" value="<?= h($loc['location_name']) ?>" class="form-control form-control-sm" required></td>
                        <td><input type="text" name="address_line1" value="<?= h($loc['address_line1'] ?? '') ?>" class="form-control form-control-sm"></td>
                        <td><input type="text" name="address_line2" value="<?= h($loc['address_line2'] ?? '') ?>" class="form-control form-control-sm"></td>
                        <td><input type="text" name="city" value="<?= h($loc['city'] ?? '') ?>" class="form-control form-control-sm"></td>
                        <td><input type="text" name="state_region" value="<?= h($loc['state_region'] ?? '') ?>" class="form-control form-control-sm" style="width:70px"></td>
                        <td><input type="text" name="postal_code" value="<?= h($loc['postal_code'] ?? '') ?>" class="form-control form-control-sm" style="width:90px"></td>
                        <td class="text-center">
                            <input type="checkbox" name="is_active" value="1" <?= !empty($loc['is_active']) ? 'checked' : '' ?>>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                    <button type="button" class="btn btn-sm btn-danger ms-1"
                            onclick="if(confirm('Delete this location?')){var f=document.createElement('form');f.method='post';f.action='/index.php?page=admin_town_locations_delete';var i=document.createElement('input');i.type='hidden';i.name='location_id';i.value='<?= (int)$loc['location_id'] ?>';f.appendChild(i);document.body.appendChild(f);f.submit();}">Delete</button>
                        </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <form method="post" action="/index.php?page=admin_town_locations_create">
                    <td></td>
                    <td><input type="text" name="location_name" class="form-control form-control-sm" placeholder="New location name" required></td>
                    <td><input type="text" name="address_line1" class="form-control form-control-sm" placeholder="Street address"></td>
                    <td><input type="text" name="address_line2" class="form-control form-control-sm" placeholder="Suite / unit"></td>
                    <td><input type="text" name="city" class="form-control form-control-sm" placeholder="City"></td>
                    <td><input type="text" name="state_region" class="form-control form-control-sm" placeholder="ST" style="width:70px"></td>
                    <td><input type="text" name="postal_code" class="form-control form-control-sm" placeholder="Zip" style="width:90px"></td>
                    <td class="text-center"><input type="checkbox" name="is_active" value="1" checked></td>
                    <td><button type="submit" class="btn btn-sm btn-success">Add</button></td>
                </form>
            </tr>
        </tbody>
    </table>
    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary mt-3">Back to System Settings</a>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
