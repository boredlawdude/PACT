<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$docId = (int)($_GET['id'] ?? 0);
$sig = (string)($_GET['sig'] ?? '');
$type = (string)($_GET['type'] ?? 'contract_document');

if ($docId <= 0 || $sig === '') {
    http_response_code(400);
    exit('Bad request.');
}

$sigParams = ['id' => $docId];
if ($type === 'contract_type_template') {
    $sigParams['type'] = $type;
}

if (!function_exists('oo_verify') || !oo_verify($sigParams, $sig)) {
    http_response_code(403);
    exit('Forbidden.');
}

if ($type === 'contract_type_template') {
    $stmt = db()->prepare(
        'SELECT contract_type_id, template_file_docx
         FROM contract_types
         WHERE contract_type_id = ?
         LIMIT 1'
    );
    $stmt->execute([$docId]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    $filePath = $doc ? trim((string)($doc['template_file_docx'] ?? '')) : '';
} else {
    $stmt = db()->prepare(
        'SELECT contract_document_id, file_name, file_path
         FROM contract_documents
         WHERE contract_document_id = ?
         LIMIT 1'
    );
    $stmt->execute([$docId]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    $filePath = $doc ? trim((string)($doc['file_path'] ?? '')) : '';
}

if (!$doc) {
    http_response_code(404);
    exit('Not found.');
}

if ($filePath === '') {
    http_response_code(404);
    exit('Missing file path.');
}

$abs = APP_ROOT . '/' . ltrim($filePath, '/');
if (!is_file($abs)) {
    http_response_code(404);
    exit('File not found.');
}

$fileName = (string)($doc['file_name'] ?? basename($abs));
$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$mime = 'application/octet-stream';
if ($ext === 'docx') {
    $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
} elseif ($ext === 'doc') {
    $mime = 'application/msword';
} elseif ($ext === 'odt') {
    $mime = 'application/vnd.oasis.opendocument.text';
} elseif ($ext === 'rtf') {
    $mime = 'application/rtf';
} elseif ($ext === 'txt') {
    $mime = 'text/plain; charset=utf-8';
} elseif ($ext === 'pdf') {
    $mime = 'application/pdf';
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($abs));
header('Content-Disposition: inline; filename="' . str_replace(['"', "\r", "\n"], '_', $fileName) . '"');
header('X-Content-Type-Options: nosniff');
readfile($abs);
exit;
