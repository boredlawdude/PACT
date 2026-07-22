<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

$docId = (int)($_GET['doc_id'] ?? 0);
$sig = (string)($_GET['sig'] ?? '');

if ($docId <= 0 || $sig === '') {
    http_response_code(400);
    echo json_encode(['error' => 1, 'message' => 'Missing doc_id or sig']);
    exit;
}

if (!function_exists('oo_verify') || !oo_verify(['doc_id' => $docId], $sig)) {
    http_response_code(403);
    echo json_encode(['error' => 1, 'message' => 'Bad signature']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 1, 'message' => 'Invalid JSON']);
    exit;
}

$status = (int)($payload['status'] ?? 0);
// OnlyOffice status 2 or 6 includes a file URL to persist
if (!in_array($status, [2, 6], true)) {
    echo json_encode(['error' => 0]);
    exit;
}

$fileUrl = trim((string)($payload['url'] ?? ''));
if ($fileUrl === '') {
    http_response_code(400);
    echo json_encode(['error' => 1, 'message' => 'Missing file URL']);
    exit;
}

$stmt = db()->prepare(
    'SELECT contract_document_id, file_path
     FROM contract_documents
     WHERE contract_document_id = ?
     LIMIT 1'
);
$stmt->execute([$docId]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doc) {
    http_response_code(404);
    echo json_encode(['error' => 1, 'message' => 'Document not found']);
    exit;
}

$filePath = trim((string)($doc['file_path'] ?? ''));
if ($filePath === '') {
    http_response_code(500);
    echo json_encode(['error' => 1, 'message' => 'Empty file path']);
    exit;
}

$abs = APP_ROOT . '/' . ltrim($filePath, '/');
$newContent = @file_get_contents($fileUrl);
if ($newContent === false || $newContent === '') {
    http_response_code(502);
    echo json_encode(['error' => 1, 'message' => 'Failed to fetch edited file']);
    exit;
}

$dir = dirname($abs);
if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
    http_response_code(500);
    echo json_encode(['error' => 1, 'message' => 'Could not create directory']);
    exit;
}

$bytes = @file_put_contents($abs, $newContent, LOCK_EX);
if ($bytes === false) {
    http_response_code(500);
    echo json_encode(['error' => 1, 'message' => 'Failed to write file']);
    exit;
}

echo json_encode(['error' => 0]);
exit;
