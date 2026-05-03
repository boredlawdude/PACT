<?php
declare(strict_types=1);

class DatabaseBackupController
{
    public function index(): void
    {
        require_system_admin();
        require APP_ROOT . '/app/views/admin/database_backup.php';
    }

    public function run(): void
    {
        require_system_admin();

        // CSRF check
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            http_response_code(403);
            echo 'Invalid CSRF token.';
            exit;
        }

        $savePath  = trim((string)($_POST['save_path'] ?? ''));
        $download  = ($savePath === '');

        // Build a filename: pact_backup_YYYY-MM-DD_HHMMSS.sql
        $filename  = 'pact_backup_' . date('Y-m-d_His') . '.sql';

        // Pull DB credentials — match the keys used in app/bootstrap.php
        $host   = $_ENV['DB_HOST']   ?? (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
        $port   = $_ENV['DB_PORT']   ?? (defined('DB_PORT') ? DB_PORT : '3306');
        $dbname = $_ENV['DB_NAME']   ?? (defined('DB_NAME') ? DB_NAME : 'contract_manager');
        $user   = $_ENV['DB_USER']   ?? (defined('DB_USER') ? DB_USER : '');
        $pass   = $_ENV['DB_PASS']   ?? (defined('DB_PASS') ? DB_PASS : '');

        // Set password via environment variable (avoids file permission issues)
        // MYSQL_PWD is read by mysqldump and never appears in process list args
        putenv('MYSQL_PWD=' . $pass);

        $mysqldump = $this->findMysqldump();

        $cmd = implode(' ', [
            escapeshellarg($mysqldump),
            '--host='   . escapeshellarg($host),
            '--port='   . escapeshellarg((string)$port),
            '--user='   . escapeshellarg($user),
            '--password=' . escapeshellarg($pass),
            '--single-transaction',
            '--routines',
            '--triggers',
            '--set-gtid-purged=OFF',
            escapeshellarg($dbname),
        ]);

        if ($download) {
            // Write to a temp file first so we can verify success before sending headers
            $tmpOut = tempnam(sys_get_temp_dir(), 'pact_bk_');
            $fullCmd = $cmd . ' > ' . escapeshellarg($tmpOut) . ' 2>&1';
            exec($fullCmd, $execOutput, $exitCode);

            if ($exitCode !== 0 || !file_exists($tmpOut) || filesize($tmpOut) === 0) {
                $errDetail = file_exists($tmpOut) ? trim(file_get_contents($tmpOut)) : 'No output produced.';
                @unlink($tmpOut);
                // Re-render the backup page with an error (layout already not loaded here)
                require_once APP_ROOT . '/includes/init.php';
                require_once APP_ROOT . '/app/views/layouts/header.php';
                $_SESSION['backup_error'] = 'mysqldump failed (exit ' . $exitCode . '): ' . htmlspecialchars($errDetail, ENT_QUOTES, 'UTF-8');
                header('Location: /index.php?page=db_backup');
                exit;
            }

            // Stream the temp file to the browser
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tmpOut));
            header('Cache-Control: no-cache');
            readfile($tmpOut);
            @unlink($tmpOut);
            exit;
        }

        // Save to a server path
        // Basic path safety: must be absolute, must not contain ..
        if (!str_starts_with($savePath, '/') || str_contains($savePath, '..')) {
            $_SESSION['backup_error'] = 'Save path must be an absolute path and must not contain "..".';
            header('Location: /index.php?page=db_backup');
            exit;
        }

        // Create directory if it doesn't exist
        if (!is_dir($savePath)) {
            if (!@mkdir($savePath, 0775, true) && !is_dir($savePath)) {
                $_SESSION['backup_error'] = 'Could not create directory: ' . htmlspecialchars($savePath, ENT_QUOTES, 'UTF-8');
                header('Location: /index.php?page=db_backup');
                exit;
            }
        }

        if (!is_writable($savePath)) {
            $_SESSION['backup_error'] = 'Directory is not writable: ' . htmlspecialchars($savePath, ENT_QUOTES, 'UTF-8');
            header('Location: /index.php?page=db_backup');
            exit;
        }

        $outFile = rtrim($savePath, '/') . '/' . $filename;
        $cmd .= ' > ' . escapeshellarg($outFile) . ' 2>&1';

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $_SESSION['backup_error'] = 'mysqldump failed (exit code ' . $exitCode . '). Check server logs.';
        } else {
            $size = file_exists($outFile) ? round(filesize($outFile) / 1024, 1) . ' KB' : '?';
            $_SESSION['backup_success'] = "Backup saved: {$outFile} ({$size})";
        }

        header('Location: /index.php?page=db_backup');
        exit;
    }

    private function findMysqldump(): string
    {
        // Common locations
        foreach (['/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/opt/homebrew/bin/mysqldump'] as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }
        // Fall back to PATH
        return 'mysqldump';
    }
}
