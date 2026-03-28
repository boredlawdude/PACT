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
        $description = trim((string)($_POST['description'] ?? ''));
        $formalBiddingRequired = isset($_POST['formal_bidding_required']) ? 1 : 0;

        try {
            $stmt = $this->db->prepare("
                UPDATE contract_types 
                SET description = ?, formal_bidding_required = ?
                WHERE contract_type_id = ?
            ");
            $stmt->execute([$description, $formalBiddingRequired, $contractTypeId]);
            $messages[] = 'Contract type updated successfully';
        } catch (Throwable $e) {
            $errors[] = 'Failed to update contract type: ' . $e->getMessage();
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
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        ];

        if ($file['size'] > $maxSize) {
            return ['error' => 'File is too large. Maximum size is 10MB.'];
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowedMimes[$type] ?? [])) {
            return ['error' => "Invalid file type for {$type} template."];
        }

        $templateDir = APP_ROOT . '/storage/templates/' . $contractTypeId;
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Use original filename as uploaded
        $filename = basename($file['name']);
        $filepath = $templateDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['error' => 'Failed to save uploaded file.'];
        }

        // Return relative path for storage in database
        return 'storage/templates/' . $contractTypeId . '/' . $filename;
    }

    private function getContractType(int $contractTypeId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM contract_types WHERE contract_type_id = ? LIMIT 1");
        $stmt->execute([$contractTypeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
