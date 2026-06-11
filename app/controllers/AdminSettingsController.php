<?php
// app/controllers/AdminSettingsController.php
class AdminSettingsController {
    public function index() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $pdo = db();
        // Load settings
        $settings = [];
        $stmt = $pdo->query("SELECT setting_key, setting_value, description FROM system_settings WHERE setting_key IN ('storage_base_dir', 'docx_template_dir', 'html_template_dir', 'default_docx_template', 'default_html_template', 'default_email_message', 'compliance_info_link') ORDER BY setting_key");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row;
        }
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        $errors = $_SESSION['admin_settings_errors'] ?? [];
        $messages = $_SESSION['admin_settings_messages'] ?? [];
        $success = $_SESSION['admin_settings_success'] ?? false;
        unset($_SESSION['admin_settings_errors'], $_SESSION['admin_settings_messages'], $_SESSION['admin_settings_success']);
        require APP_ROOT . '/app/views/admin_settings/form.php';
    }

    // ── Organization profile ──────────────────────────────────────────────────

    public function organization(): void
    {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }

        $pdo = db();
        $org = $pdo->query("SELECT * FROM organization_settings ORDER BY id ASC LIMIT 1")->fetch() ?: [];

        require APP_ROOT . '/app/views/admin_settings/organization.php';
    }

    public function saveOrganization(): void
    {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Invalid CSRF token.');
        }
        unset($_SESSION['csrf_token']);

        $fields = [
            'org_name'                => trim($_POST['org_name']              ?? ''),
            'org_type'                => $_POST['org_type']                   ?? null,
            'website_url'             => trim($_POST['website_url']           ?? '') ?: null,
            'primary_contact_name'    => trim($_POST['primary_contact_name']  ?? '') ?: null,
            'primary_contact_email'   => trim($_POST['primary_contact_email'] ?? '') ?: null,
            'finance_director_name'   => trim($_POST['finance_director_name'] ?? '') ?: null,
            'mayor_or_exec_name'      => trim($_POST['mayor_or_exec_name']    ?? '') ?: null,
            'fiscal_year_start_month' => (int)($_POST['fiscal_year_start_month'] ?? 7),
        ];

        if ($fields['org_name'] === '') {
            $_SESSION['flash_error'] = 'Organization name is required.';
            header('Location: /index.php?page=admin_organization');
            exit;
        }

        // Handle logo upload
        $logoPath = trim($_POST['existing_logo_path'] ?? '') ?: null;
        if (!empty($_FILES['logo']['tmp_name'])) {
            $uploadErr = $_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($uploadErr !== UPLOAD_ERR_OK) {
                $_SESSION['flash_error'] = 'Logo upload error (code ' . $uploadErr . '). Max size is ' . ini_get('upload_max_filesize') . '.';
                header('Location: /index.php?page=admin_organization');
                exit;
            }
            $ext     = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];
            if (!in_array($ext, $allowed, true)) {
                $_SESSION['flash_error'] = 'Logo must be a PNG, JPG, GIF, SVG, or WebP file.';
                header('Location: /index.php?page=admin_organization');
                exit;
            }
            $uploadDir = APP_ROOT . '/public/uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0775, true)) {
                    $_SESSION['flash_error'] = 'Could not create uploads directory. Check server permissions on public/uploads/.';
                    header('Location: /index.php?page=admin_organization');
                    exit;
                }
            }
            if (!is_writable($uploadDir)) {
                $_SESSION['flash_error'] = 'Uploads directory is not writable. Run: chmod 775 public/uploads/ on the server.';
                header('Location: /index.php?page=admin_organization');
                exit;
            }
            $filename = 'org_logo.' . $ext;
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename)) {
                $_SESSION['flash_error'] = 'Failed to save logo file. Check permissions on public/uploads/.';
                header('Location: /index.php?page=admin_organization');
                exit;
            }
            $logoPath = 'uploads/' . $filename;
        }
        $fields['logo_path'] = $logoPath;

        $pdo      = db();
        $existing = $pdo->query("SELECT id FROM organization_settings LIMIT 1")->fetch();
        if ($existing) {
            $pdo->prepare("
                UPDATE organization_settings SET
                    org_name = ?, org_type = ?, website_url = ?, logo_path = ?,
                    primary_contact_name = ?, primary_contact_email = ?,
                    finance_director_name = ?, mayor_or_exec_name = ?,
                    fiscal_year_start_month = ?
                WHERE id = ?
            ")->execute([
                $fields['org_name'], $fields['org_type'], $fields['website_url'], $fields['logo_path'],
                $fields['primary_contact_name'], $fields['primary_contact_email'],
                $fields['finance_director_name'], $fields['mayor_or_exec_name'],
                $fields['fiscal_year_start_month'], $existing['id'],
            ]);
        } else {
            $pdo->prepare("
                INSERT INTO organization_settings
                    (org_name, org_type, website_url, logo_path,
                     primary_contact_name, primary_contact_email,
                     finance_director_name, mayor_or_exec_name, fiscal_year_start_month)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $fields['org_name'], $fields['org_type'], $fields['website_url'], $fields['logo_path'],
                $fields['primary_contact_name'], $fields['primary_contact_email'],
                $fields['finance_director_name'], $fields['mayor_or_exec_name'],
                $fields['fiscal_year_start_month'],
            ]);
        }

        header('Location: /index.php?page=admin_organization&saved=1');
        exit;
    }

    // ── Save system settings ──────────────────────────────────────────────────

    public function update() {
        require_login();
        if (!function_exists('is_system_admin') || !is_system_admin()) {
            http_response_code(403);
            exit('Access denied. System admin required.');
        }
        $pdo = db();
        $errors = [];
        $messages = [];
        $success = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                $errors[] = 'Invalid request. Please try again.';
            } else {
                unset($_SESSION['csrf_token']);
                $settingsToUpdate = [
                    'storage_base_dir',
                    'docx_template_dir',
                    'html_template_dir',
                    'default_docx_template',
                    'default_html_template',
                    'default_email_message',
                    'compliance_info_link',
                ];
                foreach ($settingsToUpdate as $key) {
                    $value = trim((string)($_POST[$key] ?? ''));
                    if ($value === '' && !in_array($key, ['default_email_message', 'compliance_info_link'])) {
                        $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' cannot be empty.';
                        continue;
                    }
                    if (in_array($key, ['storage_base_dir', 'docx_template_dir', 'html_template_dir'])) {
                        if (!str_starts_with($value, '/') || !is_dir($value)) {
                            $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' must be a valid absolute directory path.';
                            continue;
                        }
                    }
                    $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, updated_by = ?, updated_at = NOW() WHERE setting_key = ?");
                    $stmt->execute([$value, current_person_id() ?: null, $key]);
                }
                if (empty($errors)) {
                    $success = true;
                    $messages[] = 'Settings updated successfully.';
                }
            }
        }
        $_SESSION['admin_settings_errors'] = $errors;
        $_SESSION['admin_settings_messages'] = $messages;
        $_SESSION['admin_settings_success'] = $success;
        $this->index();
        return;
    }
}
