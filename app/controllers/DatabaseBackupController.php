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
            '--column-statistics=0',
            '--no-tablespaces',
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

    /**
     * Restore the database from an uploaded .sql dump (as produced by run() above).
     * Executed via PDO rather than the mysql CLI so it works the same way
     * regardless of local CLI auth/host-grant quirks.
     */
    public function restore(): void
    {
        require_system_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        // CSRF check
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            http_response_code(403);
            echo 'Invalid CSRF token.';
            exit;
        }

        // Require the admin to type a confirmation phrase — this is a destructive,
        // irreversible operation that drops and recreates every table.
        $confirm = trim((string)($_POST['confirm_text'] ?? ''));
        if (strtoupper($confirm) !== 'RESTORE') {
            $_SESSION['backup_error'] = 'Restore not run: you must type RESTORE (in the confirmation box) to proceed.';
            header('Location: /index.php?page=db_backup');
            exit;
        }

        if (empty($_FILES['restore_file']) || $_FILES['restore_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['backup_error'] = 'Please choose a valid .sql backup file to upload.';
            header('Location: /index.php?page=db_backup');
            exit;
        }

        $file = $_FILES['restore_file'];
        $originalName = basename($file['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            $_SESSION['backup_error'] = 'Restore file must have a .sql extension.';
            header('Location: /index.php?page=db_backup');
            exit;
        }

        $sql = file_get_contents($file['tmp_name']);
        if ($sql === false || trim($sql) === '') {
            $_SESSION['backup_error'] = 'Could not read the uploaded file, or it was empty.';
            header('Location: /index.php?page=db_backup');
            exit;
        }

        // Strip stray mysqldump CLI warning/error lines that can end up mixed into
        // a backup file when stderr was redirected into stdout (older backups, or
        // ones captured with a different tool). Harmless to run on clean dumps too.
        $lines = explode("\n", $sql);
        $clean = array_filter($lines, function ($l) {
            return strpos(ltrim($l), 'mysqldump:') !== 0;
        });
        $sql = implode("\n", $clean);

        try {
            db()->exec($sql);
        } catch (Throwable $e) {
            $_SESSION['backup_error'] = 'Restore failed: ' . $e->getMessage();
            header('Location: /index.php?page=db_backup');
            exit;
        }

        $_SESSION['backup_success'] = 'Database restored successfully from ' . $originalName . '.';
        header('Location: /index.php?page=db_backup');
        exit;
    }
}
