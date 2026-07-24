<?php
declare(strict_types=1);

/**
 * onlyoffice_forcesave.php
 *
 * Called via fetch() from the inline editor right before the user navigates
 * away ("Back to Contract", tab close, etc). Tells the OnlyOffice Document
 * Server's Command Service to force-save the document's current state RIGHT
 * NOW, which makes it POST the latest content to onlyoffice_callback.php
 * synchronously instead of waiting for the editing session to time out.
 *
 * Docs: https://api.onlyoffice.com/docs/docs-api/additional-api/command-service/
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

$docId = (int)($_GET['doc_id'] ?? 0);
$sig = (string)($_GET['sig'] ?? '');
$key = trim((string)($_POST['key'] ?? ($_GET['key'] ?? '')));

if ($docId <= 0 || $sig === '' || $key === '') {
    http_response_code(400);
    echo json_encode(['error' => 1, 'message' => 'Missing doc_id, sig, or key']);
    exit;
}

if (!function_exists('oo_verify') || !oo_verify(['doc_id' => $docId, 'action' => 'forcesave'], $sig)) {
    http_response_code(403);
    echo json_encode(['error' => 1, 'message' => 'Bad signature']);
    exit;
}

// Look up the document's current file so we can detect (by mtime) once
// onlyoffice_callback.php has actually finished writing the new content —
// CommandService.ashx only acknowledges that the save was *requested*; the
// real write happens moments later via an async callback from the document
// server. Without waiting for that, "Back to Contract" can navigate before
// the file on disk has actually changed, so Download/Email still see the
// previous version until the next request.
$stmt = db()->prepare('SELECT file_path FROM contract_documents WHERE contract_document_id = ? LIMIT 1');
$stmt->execute([$docId]);
$filePath = (string)($stmt->fetchColumn() ?: '');
$abs = $filePath !== '' ? APP_ROOT . '/' . ltrim($filePath, '/') : '';
clearstatcache(true, $abs);
$beforeMtime = ($abs !== '' && is_file($abs)) ? filemtime($abs) : false;

$documentServerUrl = rtrim((string)($_ENV['ONLYOFFICE_DOCUMENT_SERVER_URL'] ?? ''), '/');
if ($documentServerUrl === '') {
    http_response_code(500);
    echo json_encode(['error' => 1, 'message' => 'Missing ONLYOFFICE_DOCUMENT_SERVER_URL']);
    exit;
}

$commandPayload = ['c' => 'forcesave', 'key' => $key];

$jwtSecret = trim((string)($_ENV['ONLYOFFICE_JWT_SECRET'] ?? ''));
$headers = ['Content-Type: application/json'];
if ($jwtSecret !== '' && function_exists('oo_jwt_sign')) {
    // Recent Document Server versions expect the JWT both as a top-level
    // "token" field in the body and as an Authorization: Bearer header.
    $token = oo_jwt_sign($commandPayload, $jwtSecret);
    $commandPayload['token'] = $token;
    $headers[] = 'Authorization: Bearer ' . $token;
}

$ch = curl_init($documentServerUrl . '/coauthoring/CommandService.ashx');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($commandPayload),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);
$raw = curl_exec($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($raw === false) {
    http_response_code(502);
    echo json_encode(['error' => 1, 'message' => 'Command Service request failed: ' . $curlErr]);
    exit;
}

$result = json_decode((string)$raw, true);
$commandError = is_array($result) ? (int)($result['error'] ?? 1) : 1;

// error 4 = "no changes were made" — nothing to wait for, the file on disk
// is already current.
if ($commandError !== 0 && $commandError !== 4) {
    http_response_code($status >= 400 ? $status : 502);
    echo json_encode(['error' => 1, 'message' => 'Force save failed', 'raw' => $result ?? $raw]);
    exit;
}

if ($commandError === 0 && $abs !== '') {
    // Poll for up to ~5 seconds for the callback to actually write the file.
    for ($i = 0; $i < 20; $i++) {
        usleep(250000);
        clearstatcache(true, $abs);
        $nowMtime = is_file($abs) ? filemtime($abs) : false;
        if ($nowMtime !== false && $nowMtime !== $beforeMtime) {
            break;
        }
    }
}

echo json_encode(['error' => 0]);
