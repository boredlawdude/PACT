<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$appRoot = dirname(__DIR__);
$envPaths = [];

if (is_file($appRoot . '/.env')) {
    $envPaths[] = $appRoot;
}

if (is_file(__DIR__ . '/.env')) {
    $envPaths[] = __DIR__;
}

if ($envPaths !== []) {
    $dotenv = Dotenv\Dotenv::createImmutable($envPaths);
    $dotenv->safeLoad();
}

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $appRoot);
}

if (!defined('APP_NAME')) {
    define('APP_NAME', $_ENV['APP_NAME'] ?? 'PACT');
}

