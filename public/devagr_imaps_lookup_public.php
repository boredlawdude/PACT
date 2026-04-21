sc`<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

// Public endpoint — no login required (Wake County parcel data is publicly available)
header('Content-Type: application/json; charset=utf-8');

// Simple per-session rate limit: max 30 lookups per session to prevent abuse
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('intake_sess');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

$_SESSION['imaps_count'] = ($_SESSION['imaps_count'] ?? 0) + 1;
if ($_SESSION['imaps_count'] > 30) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many lookups in this session. Please refresh the page or continue filling in the fields manually.']);
    exit;
}

$pin = trim((string)($_GET['pin'] ?? ''));

if (!preg_match('/^\d{1,15}$/', $pin)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid PIN. Expected up to 15 digits (e.g. 0123456789).']);
    exit;
}

$apiUrl = 'https://maps.wakegov.com/arcgis/rest/services/Property/Parcels/MapServer/0/query?' .
    http_build_query([
        'where'          => "PIN_NUM='" . $pin . "'",
        'outFields'      => 'PIN_NUM,REID,OWNER,ADDR1,ADDR2,SITE_ADDRESS,DEED_ACRES',
        'returnGeometry' => 'false',
        'f'              => 'json',
    ]);

$ctx = stream_context_create(['http' => ['timeout' => 10, 'method' => 'GET']]);
$raw = @file_get_contents($apiUrl, false, $ctx);

if ($raw === false) {
    echo json_encode(['error' => 'Could not reach Wake County GIS. Check your internet connection.']);
    exit;
}

$data = json_decode($raw, true);

if (empty($data['features'])) {
    echo json_encode(['error' => 'No parcel found for PIN ' . htmlspecialchars($pin, ENT_QUOTES, 'UTF-8') . '. Verify the PIN.']);
    exit;
}

$attr     = $data['features'][0]['attributes'];
$ownerRaw = trim((string)($attr['OWNER'] ?? ''));

if (str_contains($ownerRaw, ',')) {
    [$rawLast, $rawFirst] = explode(',', $ownerRaw, 2);
    $ownerName = ucwords(strtolower(trim($rawFirst))) . ' ' . ucwords(strtolower(trim($rawLast)));
} else {
    $ownerName = ucwords(strtolower($ownerRaw));
}

echo json_encode([
    'property_address'      => ucwords(strtolower(trim((string)($attr['SITE_ADDRESS'] ?? '')))),
    'property_pin'          => (string)($attr['PIN_NUM'] ?? ''),
    'property_realestateid' => (string)($attr['REID']    ?? ''),
    'property_acerage'      => $attr['DEED_ACRES'] !== null ? (float)$attr['DEED_ACRES'] : null,
    'owner_name'            => $ownerName,
]);
