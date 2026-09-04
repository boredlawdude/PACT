<?php
declare(strict_types=1);

/**
 * Escape HTML output
 */
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Dump variable for debugging
 */
function dd($value): void
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
    exit;
}

/**
 * Registers a shutdown handler that renders a friendly HTML error page instead of
 * a blank 500 when the script dies from an uncatchable fatal error (e.g. memory
 * exhausted) that try/catch cannot intercept. Call near the top of scripts that
 * do heavy, failure-prone work (large PDF merges/conversions, etc.).
 */
function register_friendly_fatal_handler(string $backUrl, string $backLabel = 'Back'): void
{
    register_shutdown_function(function () use ($backUrl, $backLabel) {
        $error = error_get_last();
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
        if (!$error || !in_array($error['type'], $fatalTypes, true) || headers_sent()) {
            return;
        }

        $isMemory = stripos($error['message'], 'memory size') !== false;
        $title = $isMemory ? 'Out of Memory' : 'Unexpected Error';
        $hint = $isMemory
            ? 'This usually happens when a document contains very large embedded images (e.g. high-resolution scans). Try compressing the source file(s), or ask an administrator to raise the memory limit further.'
            : 'Please try again. If the problem persists, contact an administrator with the details below.';

        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . h($title) . '</title>';
        echo '<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css"></head><body class="p-4">';
        echo '<h4 class="text-danger">' . h($title) . '</h4>';
        echo '<p>' . h($hint) . '</p>';
        echo '<pre class="bg-light p-2 small text-muted" style="white-space:pre-wrap">'
           . h($error['message'] . ' in ' . $error['file'] . ':' . $error['line']) . '</pre>';
        echo '<a href="' . h($backUrl) . '" class="btn btn-secondary mt-2">' . h($backLabel) . '</a>';
        echo '</body></html>';
    });
}