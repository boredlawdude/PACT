<?php
require_once __DIR__ . '/../includes/init.php';

// CHANGE THESE:
$email = 'john@schifano.com';
$pass  = 'Password1234!';  // pick a strong password
$name  = 'john';

$hash = password_hash($pass, PASSWORD_DEFAULT);

$pdo = db();
$pdo->prepare("INSERT INTO users (email, password_hash, full_name, role) VALUES (?, ?, ?, 'admin')")
    ->execute([$email, $hash, $name]);

echo "Admin user created: $email\n";

