<?php
declare(strict_types=1);

/**
 * Shared helper functions for the contract_manager app.
 *
 * Expected:
 * - APP_ROOT defined in bootstrap.php
 * - session_start() called in init.php
 * - db() available before functions that query system settings are used
 */

if (!function_exists('h')) {
    function h(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    function e(mixed $value): void
    {
        echo h($value);
    }
}

if (!function_exists('app_env')) {
    function app_env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        $base = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__);

        return $path !== ''
            ? $base . '/' . ltrim($path, '/')
            : $base;
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        $base = app_path('storage');

        return $path !== ''
            ? $base . '/' . ltrim($path, '/')
            : $base;
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        $base = app_path('public');

        return $path !== ''
            ? $base . '/' . ltrim($path, '/')
            : $base;
    }
}

if (!function_exists('template_path')) {
    function template_path(string $path = ''): string
    {
        $base = app_path('templates');

        return $path !== ''
            ? $base . '/' . ltrim($path, '/')
            : $base;
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

if (!function_exists('is_get')) {
    function is_get(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET';
    }
}

if (!function_exists('request_method')) {
    function request_method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }
}

if (!function_exists('input')) {
    function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}

if (!function_exists('post')) {
    function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }
}

if (!function_exists('get')) {
    function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('back')) {
    function back(string $fallback = '/'): never
    {
        $target = $_SERVER['HTTP_REFERER'] ?? $fallback;
        redirect($target);
    }
}

if (!function_exists('full_url')) {
    function full_url(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';

        return $scheme . '://' . $host . $uri;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token = null): bool
    {
        $submitted = $token ?? (string)($_POST['csrf_token'] ?? '');
        $session   = (string)($_SESSION['csrf_token'] ?? '');

        return $session !== '' && hash_equals($session, $submitted);
    }
}

if (!function_exists('require_csrf')) {
    function require_csrf(?string $token = null): void
    {
        if (!verify_csrf($token)) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }
}

if (!function_exists('flash_success')) {
    function flash_success(string $message): void
    {
        flash('success', $message);
    }
}

if (!function_exists('flash_error')) {
    function flash_error(string $message): void
    {
        flash('error', $message);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('with_old_input')) {
    function with_old_input(?array $input = null): void
    {
        $_SESSION['_old'] = $input ?? $_POST;
    }
}

if (!function_exists('clear_old_input')) {
    function clear_old_input(): void
    {
        unset($_SESSION['_old']);
    }
}

if (!function_exists('array_get')) {
    function array_get(array $array, string|int $key, mixed $default = null): mixed
    {
        return $array[$key] ?? $default;
    }
}

if (!function_exists('starts_with')) {
    function starts_with(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }
}

if (!function_exists('ends_with')) {
    function ends_with(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }
}

if (!function_exists('blank')) {
    function blank(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return $value === [];
        }

        return false;
    }
}

if (!function_exists('filled')) {
    function filled(mixed $value): bool
    {
        return !blank($value);
    }
}

if (!function_exists('require_int')) {
    function require_int(mixed $value, string $label = 'ID'): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);

        if ($int === false || $int === null) {
            http_response_code(400);
            exit($label . ' must be a valid integer.');
        }

        return (int)$int;
    }
}

if (!function_exists('to_bool')) {
    function to_bool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $result ?? false;
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'm/d/Y'): string
    {
        if (blank($date)) {
            return '';
        }

        try {
            return (new DateTime($date))->format($format);
        } catch (Throwable) {
            return '';
        }
    }
}

if (!function_exists('now')) {
    function now(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }
}

if (!function_exists('ensure_dir')) {
    function ensure_dir(string $path, int $permissions = 0775): void
    {
        if (!is_dir($path)) {
            mkdir($path, $permissions, true);
        }
    }
}

if (!function_exists('app_log')) {
    function app_log(string $message, array $context = []): void
    {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;

        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $line .= PHP_EOL;

        $logDir = storage_path('logs');
        ensure_dir($logDir);

        error_log($line, 3, $logDir . '/app.log');
    }
}

/*
|--------------------------------------------------------------------------
| System settings helpers
|--------------------------------------------------------------------------
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
        $default = storage_path();

        return rtrim(
            get_system_setting('storage_base_dir', $default) ?? $default,
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
        return get_storage_base_dir() . '/' . get_generated_contracts_subdir() . '/' . $contractId;
    }
}

/*
|--------------------------------------------------------------------------
| Template directory helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('get_docx_template_dir')) {
    function get_docx_template_dir(): string
    {
        $dir = get_system_setting('docx_template_dir', template_path('docx'));

        if (!$dir || !is_dir($dir)) {
            $dir = template_path('docx');
        }

        return rtrim($dir, '/');
    }
}

if (!function_exists('get_html_template_dir')) {
    function get_html_template_dir(): string
    {
        $dir = get_system_setting('html_template_dir', template_path('html'));

        if (!$dir || !is_dir($dir)) {
            $dir = template_path('html');
        }

        return rtrim($dir, '/');
    }
}

if (!function_exists('get_default_docx_template')) {
    function get_default_docx_template(): ?string
    {
        return get_system_setting('default_docx_template');
    }
}

if (!function_exists('get_default_html_template')) {
    function get_default_html_template(): ?string
    {
        return get_system_setting('default_html_template');
    }
}

if (!function_exists('merge_html_template')) {
    function merge_html_template(string $html, array $data): string
    {
        $replacements = [];

        foreach ($data as $key => $value) {
            $replacements['{{' . $key . '}}'] = h((string)$value);
        }

        return strtr($html, $replacements);
    }
}