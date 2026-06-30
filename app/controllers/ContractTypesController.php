<?php
declare(strict_types=1);

class ContractTypesController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function index(): void
    {
        $stmt = $this->db->query("
            SELECT * FROM contract_types 
            WHERE is_active = 1 
            ORDER BY contract_type
        ");
        $contractTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require APP_ROOT . '/app/views/contract_types/index.php';
    }

    public function edit(int $contractTypeId): void
    {
        $stmt = $this->db->prepare("SELECT * FROM contract_types WHERE contract_type_id = ? LIMIT 1");
        $stmt->execute([$contractTypeId]);
        $contractType = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$contractType) {
            http_response_code(404);
            echo 'Contract type not found.';
            return;
        }

        $flashMessages = $_SESSION['flash_messages'] ?? [];
        $flashErrors = $_SESSION['flash_errors'] ?? [];
        unset($_SESSION['flash_messages']);
        unset($_SESSION['flash_errors']);

        require APP_ROOT . '/app/views/contract_types/edit.php';
    }

    public function create(): void
    {
        $flashErrors = $_SESSION['flash_errors'] ?? [];
        $flashOld    = $_SESSION['flash_old'] ?? [];
        unset($_SESSION['flash_errors'], $_SESSION['flash_old']);

        require APP_ROOT . '/app/views/contract_types/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        $contractTypeName     = trim((string)($_POST['contract_type'] ?? ''));
        $description          = trim((string)($_POST['description'] ?? ''));
        $formalBiddingRequired = isset($_POST['formal_bidding_required']) ? 1 : 0;

        $errors = [];

        if ($contractTypeName === '') {
            $errors[] = 'Contract type name is required.';
        } elseif (strlen($contractTypeName) > 100) {
            $errors[] = 'Contract type name must be 100 characters or fewer.';
        }

        if (!empty($errors)) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old']    = $_POST;
            header('Location: /index.php?page=contract_types_create');
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO contract_types (contract_type, description, formal_bidding_required)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$contractTypeName, $description, $formalBiddingRequired]);
            $newId = (int)$this->db->lastInsertId();
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $_SESSION['flash_errors'] = ["A contract type named \"$contractTypeName\" already exists."];
            } else {
                $_SESSION['flash_errors'] = ['Failed to create contract type: ' . $e->getMessage()];
            }
            $_SESSION['flash_old'] = $_POST;
            header('Location: /index.php?page=contract_types_create');
            exit;
        }

        $_SESSION['flash_messages'] = ["Contract type \"$contractTypeName\" created successfully."];
        header("Location: /index.php?page=contract_types_edit&contract_type_id={$newId}");
        exit;
    }

    public function delete(int $contractTypeId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        $contractType = $this->getContractType($contractTypeId);
        if (!$contractType) {
            $_SESSION['flash_errors'] = ['Contract type not found.'];
            header('Location: /index.php?page=contract_types');
            exit;
        }

        try {
            $stmt = $this->db->prepare("UPDATE contract_types SET is_active = 0 WHERE contract_type_id = ?");
            $stmt->execute([$contractTypeId]);
        } catch (Throwable $e) {
            $_SESSION['flash_errors'] = ['Failed to delete contract type: ' . $e->getMessage()];
            header('Location: /index.php?page=contract_types');
            exit;
        }

        $_SESSION['flash_messages'] = ["Contract type \"" . $contractType['contract_type'] . "\" deleted."];
        header('Location: /index.php?page=contract_types');
        exit;
    }

    public function update(int $contractTypeId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        $contractType = $this->getContractType($contractTypeId);
        if (!$contractType) {
            http_response_code(404);
            $_SESSION['flash_errors'] = ['Contract type not found'];
            header("Location: /index.php?page=contract_types");
            exit;
        }

        $errors = [];
        $messages = [];

        // Update basic fields
        $contractTypeName     = trim((string)($_POST['contract_type'] ?? ''));
        $description          = trim((string)($_POST['description'] ?? ''));
        $formalBiddingRequired = isset($_POST['formal_bidding_required']) ? 1 : 0;

        if ($contractTypeName === '') {
            $errors[] = 'Contract type name is required.';
        } elseif (strlen($contractTypeName) > 100) {
            $errors[] = 'Contract type name must be 100 characters or fewer.';
        }

        if (!empty($errors)) {
            $_SESSION['flash_errors'] = $errors;
            header("Location: /index.php?page=contract_types_edit&contract_type_id={$contractTypeId}");
            exit;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE contract_types 
                SET contract_type = ?, description = ?, formal_bidding_required = ?
                WHERE contract_type_id = ?
            ");
            $stmt->execute([$contractTypeName, $description, $formalBiddingRequired, $contractTypeId]);
            $messages[] = 'Contract type updated successfully';
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $errors[] = "A contract type named \"$contractTypeName\" already exists.";
            } else {
                $errors[] = 'Failed to update contract type: ' . $e->getMessage();
            }
        }

        // Handle HTML template upload
        if (!empty($_FILES['template_html']['name'])) {
            $result = $this->handleTemplateUpload($_FILES['template_html'], 'html', $contractTypeId);
            if (is_array($result) && isset($result['error'])) {
                $errors[] = $result['error'];
            } else {
                try {
                    $stmt = $this->db->prepare("UPDATE contract_types SET template_file_html = ? WHERE contract_type_id = ?");
                    $stmt->execute([$result, $contractTypeId]);
                    $messages[] = 'HTML template uploaded successfully';
                } catch (Throwable $e) {
                    $errors[] = 'Failed to save HTML template: ' . $e->getMessage();
                }
            }
        }

        // Handle DOCX template upload
        if (!empty($_FILES['template_docx']['name'])) {
            $result = $this->handleTemplateUpload($_FILES['template_docx'], 'docx', $contractTypeId);
            if (is_array($result) && isset($result['error'])) {
                $errors[] = $result['error'];
            } else {
                try {
                    $stmt = $this->db->prepare("UPDATE contract_types SET template_file_docx = ? WHERE contract_type_id = ?");
                    $stmt->execute([$result, $contractTypeId]);
                    $messages[] = 'DOCX template uploaded successfully';
                } catch (Throwable $e) {
                    $errors[] = 'Failed to save DOCX template: ' . $e->getMessage();
                }
            }
        }

        $_SESSION['flash_messages'] = $messages;
        if (!empty($errors)) {
            $_SESSION['flash_errors'] = $errors;
        }

        header("Location: /index.php?page=contract_types_edit&contract_type_id={$contractTypeId}");
        exit;
    }

    private function handleTemplateUpload(array $file, string $type, int $contractTypeId): string|array
    {
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedMimes = [
            'html' => ['text/html', 'text/plain'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip']
        ];

        if ($file['size'] > $maxSize) {
            return ['error' => 'File is too large. Maximum size is 10MB.'];
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowedMimes[$type] ?? [])) {
            return ['error' => "Invalid file type for {$type} template."];
        }

        if ($type === 'docx') {
            $ext = strtolower((string)pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
            if ($ext !== 'docx') {
                return ['error' => 'DOCX template must use a .docx file extension.'];
            }
            if (!$this->isValidDocxTemplate((string)$file['tmp_name'])) {
                return ['error' => 'Invalid DOCX template. Please upload a valid Word .docx file.'];
            }
        }

        $baseDir = $this->getTemplateBaseDir($type);
        if ($baseDir === '' || !str_starts_with($baseDir, '/')) {
            return ['error' => 'Template directory setting is invalid. Please check Admin Settings.'];
        }

        $templateDir = rtrim($baseDir, '/') . '/' . $contractTypeId;
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        $filename = $this->sanitizeUploadFilename((string)($file['name'] ?? ''), $type);
        if ($filename === '') {
            return ['error' => 'Invalid filename for uploaded template.'];
        }
        $filepath = $templateDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['error' => 'Failed to save uploaded file.'];
        }

        return $this->toStoredTemplatePath($filepath);
    }

    private function getTemplateBaseDir(string $type): string
    {
        if ($type === 'docx') {
            return $this->getSystemSetting('docx_template_dir', APP_ROOT . '/storage/templates');
        }

        if ($type === 'html') {
            return $this->getSystemSetting('html_template_dir', APP_ROOT . '/storage/templates');
        }

        return APP_ROOT . '/storage/templates';
    }

    private function getSystemSetting(string $key, string $default): string
    {
        $stmt = $this->db->prepare('SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = trim((string)($stmt->fetchColumn() ?: ''));
        return $value !== '' ? $value : $default;
    }

    private function toStoredTemplatePath(string $absolutePath): string
    {
        $abs = str_replace('\\', '/', $absolutePath);
        $root = rtrim(str_replace('\\', '/', APP_ROOT), '/');

        if (str_starts_with($abs, $root . '/')) {
            return ltrim(substr($abs, strlen($root)), '/');
        }

        // Fallback for paths outside APP_ROOT.
        return $abs;
    }

    private function isValidDocxTemplate(string $path): bool
    {
        if (!is_file($path) || !is_readable($path)) {
            return false;
        }

        if (!class_exists('ZipArchive')) {
            // If ZipArchive is unavailable, fall back to current checks.
            return true;
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return false;
        }

        $hasContentTypes = $zip->locateName('[Content_Types].xml') !== false;
        $hasMainDocument = $zip->locateName('word/document.xml') !== false;
        if (!$hasContentTypes || !$hasMainDocument) {
            $zip->close();
            return false;
        }

        $contentTypesXml = $zip->getFromName('[Content_Types].xml');
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!is_string($contentTypesXml) || !is_string($documentXml)) {
            return false;
        }

        return $this->isWellFormedXml($contentTypesXml) && $this->isWellFormedXml($documentXml);
    }

    private function isWellFormedXml(string $xml): bool
    {
        $prev = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $ok = $dom->loadXML($xml, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $ok;
    }

    private function sanitizeUploadFilename(string $originalName, string $type): string
    {
        $filename = basename(trim($originalName));
        $filename = trim($filename, " \t\n\r\0\x0B\"'");

        $ext = strtolower((string)pathinfo($filename, PATHINFO_EXTENSION));
        $base = (string)pathinfo($filename, PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9._ -]/', '_', $base) ?? '';
        $base = preg_replace('/\s+/', ' ', $base) ?? '';
        $base = trim($base, " ._-");

        if ($base === '') {
            $base = $type . '_template_' . time();
        }

        if ($ext === '') {
            $ext = $type === 'docx' ? 'docx' : 'html';
        }

        return $base . '.' . $ext;
    }

    private function getContractType(int $contractTypeId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM contract_types WHERE contract_type_id = ? LIMIT 1");
        $stmt->execute([$contractTypeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
