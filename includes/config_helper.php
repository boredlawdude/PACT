<?php
declare(strict_types=1);

/**
 * config_helper.php
 * 
 * Helper functions to read system settings from the database.
 * Provides caching per-request and safe fallbacks.
 * 
 * Assumes:
 * - pdo() function exists and returns a PDO connection
 * - system_settings table exists with columns: setting_key (VARCHAR PK), setting_value (TEXT)
 */

if (!function_exists('pdo')) {
    throw new RuntimeException('pdo() function is required but not found.');
}

/**
 * Fetch a single setting value from DB with per-request caching
 * 
 * @param string      $key      The setting_key from system_settings table
 * @param string|null $default  Fallback value if not found in DB
 * @return string|null          The value, or $default if missing
 */
function get_system_setting(string $key, ?string $default = null): ?string
{
    static $cache = [];

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $pdo = pdo();
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        $cache[$key] = ($value !== false) ? (string)$value : $default;
    } catch (Throwable $e) {
        error_log("Failed to read setting '$key': " . $e->getMessage());
        $cache[$key] = $default;
    }

    return $cache[$key];
}

/**
 * Convenience wrappers for common paths (used in generation/download scripts)
 */

/** Absolute path to storage root (parent of contracts/) */
function get_storage_base_dir(): string
{
    return rtrim(
        get_system_setting('storage_base_dir', dirname(__DIR__) . '/storage') ?? '',
        '/'
    );
}

/** Subfolder name under storage_base_dir where contracts are saved */
function get_contracts_subdir(): string
{
    return trim(
        get_system_setting('contracts_generated_subdir', 'contracts') ?? '',
        '/'
    );
}

/** Full absolute path to a contract's document folder */
function get_contract_storage_dir(int $contractId): string
{
    $base = get_storage_base_dir();
    $sub  = get_contracts_subdir();
    return $base . '/' . $sub . '/' . $contractId;
}

/** Absolute path to DOCX templates folder */
function get_docx_template_dir(): string
{
    return rtrim(
        get_system_setting('docx_template_dir', dirname(__DIR__) . '/templates/docx') ?? '',
        '/'
    );
}

/** Full path to default DOCX template file */
function get_default_docx_template_path(): string
{
    $dir  = get_docx_template_dir();
    $file = get_system_setting('default_docx_template', 'default_template.docx') ?? '';
    return $dir . '/' . ltrim($file, '/');
}

/** Absolute path to HTML templates folder */
function get_html_template_dir(): string
{
    return rtrim(
        get_system_setting('html_template_dir', dirname(__DIR__) . '/templates/html') ?? '',
        '/'
    );
}

/** Full path to default HTML template file */
function get_default_html_template_path(): string
{
    $dir  = get_html_template_dir();
    $file = get_system_setting('default_html_template', 'default_template.html') ?? '';
    return $dir . '/' . ltrim($file, '/');
}

/**
 * config_helper.php
 * Provides get_system_setting() and related path helpers
 */

if (!function_exists('pdo')) {
    throw new RuntimeException('pdo() function is required but not found.');
}

