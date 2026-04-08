<?php
declare(strict_types=1);

/**
 * DocuSign Connect webhook receiver.
 *
 * DocuSign sends a POST here whenever an envelope status changes.
 * Configure this URL in DocuSign Connect settings with JSON payload format.
 *
 * Required .env key (optional but recommended):
 *   DOCUSIGN_WEBHOOK_HMAC_KEY – HMAC secret set in DocuSign Connect to verify requests.
 *
 * This endpoint intentionally has no session/login requirement since DocuSign
 * calls it server-to-server.
 */

// Bootstrap the app without initiating a user session
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../app/bootstrap.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$rawBody = (string)file_get_contents('php://input');

// ----- HMAC verification (if key is configured) -----
$hmacKey = (string)(getenv('DOCUSIGN_WEBHOOK_HMAC_KEY') ?: '');
if ($hmacKey !== '') {
    $sigHeader = (string)($_SERVER['HTTP_X_DOCUSIGN_SIGNATURE_1'] ?? '');
    if ($sigHeader === '') {
        http_response_code(401);
        exit;
    }
    $expected = base64_encode(hash_hmac('sha256', $rawBody, $hmacKey, true));
    if (!hash_equals($expected, $sigHeader)) {
        http_response_code(401);
        exit;
    }
}

$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    http_response_code(400);
    exit;
}

// DocuSign Connect JSON envelope data structure
$envelopeId     = (string)($payload['envelopeId']     ?? ($payload['data']['envelopeId'] ?? ''));
$envelopeStatus = (string)($payload['status']         ?? ($payload['data']['envelopeSummary']['status'] ?? ''));

if ($envelopeId === '' || $envelopeStatus === '') {
    // Not an envelope status event we can act on; respond 200 to stop retries
    http_response_code(200);
    exit;
}

// Normalise status to lowercase
$envelopeStatus = strtolower($envelopeStatus);

$db = pdo();

$upd = $db->prepare("
    UPDATE contract_documents
    SET    docusign_status       = :status,
           docusign_completed_at = CASE WHEN :status2 = 'completed' THEN NOW() ELSE docusign_completed_at END
    WHERE  docusign_envelope_id  = :env_id
");
$upd->execute([
    ':status'  => $envelopeStatus,
    ':status2' => $envelopeStatus,
    ':env_id'  => $envelopeId,
]);

http_response_code(200);
exit;
