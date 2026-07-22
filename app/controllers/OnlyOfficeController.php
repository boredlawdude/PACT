<?php
declare(strict_types=1);

final class OnlyOfficeController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = db();
    }

    public function editor(): void
    {
        $docId = (int)($_GET['document_id'] ?? 0);
        if ($docId <= 0) {
            http_response_code(400);
            echo 'Invalid document id.';
            return;
        }

        $doc = $this->findDocument($docId);
        if (!$doc) {
            http_response_code(404);
            echo 'Document not found.';
            return;
        }

        $contractId = (int)($doc['contract_id'] ?? 0);
        if ($contractId <= 0 || !can_manage_contract($contractId)) {
            http_response_code(403);
            echo 'Forbidden.';
            return;
        }

        $fileName = (string)($doc['file_name'] ?? '');
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($ext === '') {
            $ext = 'docx';
        }

        if (!in_array($ext, ['docx', 'doc', 'odt', 'rtf', 'txt'], true)) {
            http_response_code(400);
            echo 'Inline editor currently supports text documents only.';
            return;
        }

        $appBaseUrl = rtrim($this->appBaseUrl(), '/');
        $docSig = oo_sign(['id' => $docId]);
        $cbSig = oo_sign(['doc_id' => $docId]);

        $docUrl = $appBaseUrl . '/onlyoffice_download.php?id=' . $docId . '&sig=' . urlencode($docSig);
        $callbackUrl = $appBaseUrl . '/onlyoffice_callback.php?doc_id=' . $docId . '&sig=' . urlencode($cbSig);

        $mtime = $this->documentMtime($doc);
        $keyMaterial = $docId . '|' . ((string)$doc['file_path']) . '|' . (string)$mtime;
        $docKey = substr(hash('sha256', $keyMaterial), 0, 64);

        $person = current_person();
        $userName = trim((string)($person['name'] ?? $person['email'] ?? 'User'));

        $editorConfig = [
            'document' => [
                'fileType' => $ext,
                'key' => $docKey,
                'title' => $fileName !== '' ? $fileName : ('Document_' . $docId . '.' . $ext),
                'url' => $docUrl,
            ],
            'documentType' => 'word',
            'editorConfig' => [
                'mode' => 'edit',
                'callbackUrl' => $callbackUrl,
                'user' => [
                    'id' => (string)current_person_id(),
                    'name' => $userName,
                ],
                'customization' => [
                    'forcesave' => true,
                ],
            ],
        ];

        $jwtSecret = trim((string)($_ENV['ONLYOFFICE_JWT_SECRET'] ?? ''));
        $editorToken = null;
        if ($jwtSecret !== '' && function_exists('oo_jwt_sign')) {
            $editorToken = oo_jwt_sign($editorConfig, $jwtSecret);
        }

        $documentServerUrl = rtrim((string)($_ENV['ONLYOFFICE_DOCUMENT_SERVER_URL'] ?? ''), '/');
        if ($documentServerUrl === '') {
            http_response_code(500);
            echo 'Missing ONLYOFFICE_DOCUMENT_SERVER_URL in environment.';
            return;
        }

        require APP_ROOT . '/app/views/contracts/onlyoffice_editor.php';
    }

    private function findDocument(int $docId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT contract_document_id, contract_id, file_name, file_path
             FROM contract_documents
             WHERE contract_document_id = ?
             LIMIT 1'
        );
        $stmt->execute([$docId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function documentMtime(array $doc): int
    {
        $path = (string)($doc['file_path'] ?? '');
        if ($path === '') {
            return time();
        }

        $abs = APP_ROOT . '/' . ltrim($path, '/');
        $mtime = @filemtime($abs);
        return $mtime !== false ? (int)$mtime : time();
    }

    private function appBaseUrl(): string
    {
        $explicit = trim((string)($_ENV['ONLYOFFICE_APP_BASE_URL'] ?? ''));
        if ($explicit !== '') {
            return $explicit;
        }

        $appUrl = trim((string)($_ENV['APP_URL'] ?? ''));
        if ($appUrl !== '') {
            return $appUrl;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
        return $scheme . '://' . $host;
    }
}
