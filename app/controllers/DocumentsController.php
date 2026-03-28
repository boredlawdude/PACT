<?php
declare(strict_types=1);

class DocumentsController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function uploadExhibit(): void
    {
        $contractId = (int)($_POST['contract_id'] ?? 0);
        $exhibitLabel = trim($_POST['exhibit_label'] ?? '');
        $contractName = trim($_POST['contract_name'] ?? '');
        if ($contractId <= 0 || empty($_FILES['exhibit_file']['tmp_name'])) {
            http_response_code(400);
            echo 'Missing contract or file.';
            return;
        }
        $ext = pathinfo($_FILES['exhibit_file']['name'], PATHINFO_EXTENSION);
        $safeLabel = preg_replace('/[^a-zA-Z0-9._-]/', '_', $exhibitLabel ?: 'Exhibit');
        $safeContract = preg_replace('/[^a-zA-Z0-9._-]/', '_', $contractName ?: 'Contract');
        $finalName = $safeLabel . '_to_' . $safeContract . ($ext ? ('.' . $ext) : '');
        $storageDir = APP_ROOT . "/storage/generated_docs/{$contractId}";
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0777, true);
        }
        $targetPath = $storageDir . "/" . $finalName;
        if (!move_uploaded_file($_FILES['exhibit_file']['tmp_name'], $targetPath)) {
            http_response_code(500);
            echo 'Failed to move uploaded file.';
            return;
        }
        $webPath = "/storage/generated_docs/{$contractId}/{$finalName}";
        $stmt = $this->db->prepare("INSERT INTO contract_documents (contract_id, file_path, doc_type, file_name, created_by_person_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $contractId,
            $webPath,
            'Exhibit',
            $finalName,
            $_SESSION['person_id'] ?? null
        ]);
        header('Location: /index.php?page=contracts_show&contract_id=' . $contractId);
        exit;
    }
}
