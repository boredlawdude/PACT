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
// Command Service returns {"error":0} on success (0 = ok, other codes = failure).
if (!is_array($result) || (int)($result['error'] ?? 1) !== 0) {
    http_response_code($status >= 400 ? $status : 502);
    echo json_encode(['error' => 1, 'message' => 'Force save failed', 'raw' => $result ?? $raw]);
    exit;
}

echo json_encode(['error' => 0]);
