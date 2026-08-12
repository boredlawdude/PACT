<?php
// app/views/admin_settings/bidding_compliance_event_types.php
?>
<div class="container mt-4">
    <h2 class="h4 mb-3">Bidding Compliance Event Types</h2>
    <p class="text-muted">
        These are the options shown in the <strong>Event</strong> dropdown on a contract's Bidding Compliance Log.
        Uncheck <strong>Active</strong> to hide an option from the dropdown without deleting it (existing log
        entries using that label are unaffected either way).
    </p>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Event type saved successfully.</div>
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
                <th style="width:90px">Active</th>
                <th style="width:140px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventTypes as $et): ?>
                <tr>
                    <form method="post" action="/index.php?page=admin_bidding_compliance_event_types_update">
                        <input type="hidden" name="event_type_id" value="<?= (int)$et['event_type_id'] ?>">
                        <td><?= (int)$et['event_type_id'] ?></td>
                        <td><input type="text" name="label" value="<?= h($et['label']) ?>" class="form-control form-control-sm" required></td>
                        <td class="text-center">
                            <input type="checkbox" name="active" value="1" <?= !empty($et['active']) ? 'checked' : '' ?>>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                    <button type="button" class="btn btn-sm btn-danger ms-1"
                            onclick="if(confirm('Delete this event type?')){var f=document.createElement('form');f.method='post';f.action='/index.php?page=admin_bidding_compliance_event_types_delete';var i=document.createElement('input');i.type='hidden';i.name='event_type_id';i.value='<?= (int)$et['event_type_id'] ?>';f.appendChild(i);document.body.appendChild(f);f.submit();}">Delete</button>
                        </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <form method="post" action="/index.php?page=admin_bidding_compliance_event_types_create">
                    <td></td>
                    <td><input type="text" name="label" class="form-control form-control-sm" placeholder="New event label" required></td>
                    <td class="text-center"><input type="checkbox" name="active" value="1" checked></td>
                    <td><button type="submit" class="btn btn-sm btn-success">Add</button></td>
                </form>
            </tr>
        </tbody>
    </table>
    <a href="/index.php?page=admin_settings" class="btn btn-outline-secondary mt-3">Back to System Settings</a>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
