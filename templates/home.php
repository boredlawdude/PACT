<?php
require APP_ROOT . '/includes/header.php';
?>

<div class="container mt-4">

<h1>Contracts Dashboard</h1>

<p>Welcome <?= h(current_person()['name'] ?? 'User') ?></p>

<ul>
    <li><a href="/contracts/list.php">View Contracts</a></li>
    <li><a href="/companies/list.php">Companies</a></li>
    <li><a href="/people/list.php">People</a></li>
    <li><a href="/admin_settings.php">System Settings</a></li>
</ul>

</div>

<?php
require APP_ROOT . '/includes/footer.php';
?>