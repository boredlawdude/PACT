<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Application Bootstrap
|--------------------------------------------------------------------------
| Loads environment variables, database connection, and helpers
*/

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Set application timezone
date_default_timezone_set('America/New_York');

/*
|--------------------------------------------------------------------------
| Load .env
|--------------------------------------------------------------------------
*/
 
$envFile = APP_ROOT . '/.env';

if (file_exists($envFile)) {
    $env = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if (is_array($env)) {
        foreach ($env as $key => $value) {
            $_ENV[$key] = $value;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

if (!function_exists('db')) {
    function db(): PDO
    {
        static $pdo = null;

        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
                // Sync MySQL session timezone with PHP
                try {
                    $pdo->exec("SET time_zone = 'America/New_York'");
                } catch (PDOException $tzEx) {
                    // Named timezone tables not loaded; leave MySQL on its system timezone
                    // PHP is already set to America/New_York which should match
                }
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return $pdo;
    }
}

if (!function_exists('pdo')) {
    function pdo(): PDO
    {
        return db();
    }
}



/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

require_once APP_ROOT . '/includes/helpers.php';

