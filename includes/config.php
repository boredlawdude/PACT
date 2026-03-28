<?php
declare(strict_types=1);

/**
 * Transitional compatibility config for contract_manager
 *
 * Goal:
 * - support older files that still require config.php
 * - load environment variables from .env
 * - provide backwards-compatible constants and helpers
 * - allow gradual migration to init.php + db()
 */

require_once __DIR__ . '/bootstrap.php';

/*
|--------------------------------------------------------------------------
| Backward-compatible constants
|--------------------------------------------------------------------------
|
| Old files may still reference these constants directly.
| Define them from .env so existing code continues to work.
|
*/

defined('DB_HOST')      || define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
defined('DB_PORT')      || define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
defined('DB_NAME')      || define('DB_NAME', $_ENV['DB_DATABASE'] ?? 'contract_manager');
defined('DB_USER')      || define('DB_USER', $_ENV['DB_USERNAME'] ?? '');
defined('DB_PASS')      || define('DB_PASS', $_ENV['DB_PASSWORD'] ?? '');

defined('APP_NAME')     || define('APP_NAME', $_ENV['APP_NAME'] ?? 'Contracts');
defined('SESSION_NAME') || define('SESSION_NAME', $_ENV['SESSION_NAME'] ?? 'contracts_app_sess');

defined('SMTP_HOST')        || define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? '');
defined('SMTP_PORT')        || define('SMTP_PORT', (int)($_ENV['SMTP_PORT'] ?? 587));
defined('SMTP_SECURE')      || define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? 'tls');
defined('SMTP_USERNAME')    || define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
defined('SMTP_PASSWORD')    || define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
defined('MAIL_FROM_EMAIL')  || define('MAIL_FROM_EMAIL', $_ENV['MAIL_FROM_EMAIL'] ?? '');
defined('MAIL_FROM_NAME')   || define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? APP_NAME);

defined('ONLYOFFICE_JWT_SECRET') || define('ONLYOFFICE_JWT_SECRET', $_ENV['ONLYOFFICE_JWT_SECRET'] ?? '');
defined('OO_SECRET')              || define('OO_SECRET', $_ENV['OO_SECRET'] ?? '');

/*
|--------------------------------------------------------------------------
| App paths
|--------------------------------------------------------------------------
|
| These replace older __DIR__-based assumptions with stable project-root paths.
|
*/

defined('DOCS_BASE_DIR') || define('DOCS_BASE_DIR', APP_ROOT . '/storage');
defined('DOCS_BASE_URL') || define('DOCS_BASE_URL', '/');

/*
|--------------------------------------------------------------------------
| Backward-compatible DB helper
|--------------------------------------------------------------------------
|
| Older files still call pdo(). Newer code should call db().
|
*/

if (!function_exists('db')) {
    function db(): PDO
    {
        static $pdo = null;

        if ($pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );

            $pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
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
| Backward-compatible session starter
|--------------------------------------------------------------------------
|
| Older files still call start_session(). Newer code should rely on init.php.
|
*/

if (!function_exists('start_session')) {
    function start_session(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);

            $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'secure' => $secure,
                'samesite' => 'Lax',
            ]);

            session_start();
        }
    }
}

/*
|--------------------------------------------------------------------------
| OnlyOffice helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('oo_sign')) {
    function oo_sign(array $params): string
    {
        ksort($params);
        $base = http_build_query($params);
        return hash_hmac('sha256', $base, OO_SECRET);
    }
}

if (!function_exists('oo_verify')) {
    function oo_verify(array $params, string $sig): bool
    {
        $expected = oo_sign($params);
        return hash_equals($expected, $sig);
    }
}

/*
|--------------------------------------------------------------------------
| HTML template merge helper
|--------------------------------------------------------------------------
*/

if (!function_exists('merge_html_template')) {
    function merge_html_template(string $html, array $data): string
    {
        $repl = [];

        foreach ($data as $k => $v) {
            $repl['{{' . $k . '}}'] = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        }

        return strtr($html, $repl);
    }
}

/*
|--------------------------------------------------------------------------
| System settings helpers
|--------------------------------------------------------------------------
|
| These are useful while you transition path/template settings into DB-driven config.
|
*/

if (!function_exists('get_system_setting')) {
    function get_system_setting(string $key, ?string $default = null): ?string
    {
        static $cache = [];

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $stmt = db()->prepare("
            SELECT setting_value
            FROM system_settings
            WHERE setting_key = ?
            LIMIT 1
        ");
        $stmt->execute([$key]);

        $value = $stmt->fetchColumn();
        $cache[$key] = ($value !== false) ? (string)$value : $default;

        return $cache[$key];
    }
}

if (!function_exists('get_storage_base_dir')) {
    function get_storage_base_dir(): string
    {
        return rtrim(
            get_system_setting('storage_base_dir', APP_ROOT . '/storage') ?? (APP_ROOT . '/storage'),
            '/'
        );
    }
}

if (!function_exists('get_generated_contracts_subdir')) {
    function get_generated_contracts_subdir(): string
    {
        return trim(
            get_system_setting('contracts_generated_subdir', 'generated_docs') ?? 'generated_docs',
            '/'
        );
    }
}

if (!function_exists('get_full_generated_contracts_dir')) {
    function get_full_generated_contracts_dir(int $contractId): string
    {
        $base = get_storage_base_dir();
        $sub  = get_generated_contracts_subdir();

        return $base . '/' . $sub . '/' . $contractId;
    }
}