<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionName = $_ENV['SESSION_NAME'] ?? 'contracts_app_sess';
    session_name($sessionName);

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $domain = $_SERVER['HTTP_HOST'] ?? '';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $domain,
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Lax',
    ]);

    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

require_once __DIR__ . '/auth.php';