<?php
declare(strict_types=1);

ini_set('display_errors','1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/init.php';

start_session();

header('Content-Type: text/html; charset=utf-8');

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function request_base_url(): string {
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
  $scheme = $https ? 'https' : 'http';
  $host = trim($_SERVER['HTTP_HOST'] ?? 'localhost');
  return $scheme . '://' . $host;
}

$pdo = pdo();
$email = trim(strtolower($_POST['email'] ?? ''));
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $msg = "If the email exists and has login access, a reset link has been sent.";

  $stmt = $pdo->prepare("SELECT person_id, is_active, can_login FROM people WHERE email = ? LIMIT 1");
  $stmt->execute([$email]);
  $p = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($p && (int)$p['is_active'] === 1 && (int)$p['can_login'] === 1) {
    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);
    $expires_at = (new DateTime('+60 minutes'))->format('Y-m-d H:i:s');

    // mark prior tokens used
    $pdo->prepare("
      UPDATE password_resets
      SET used_at = NOW()
      WHERE person_id = ?
        AND used_at IS NULL
        AND expires_at > NOW()
    ")->execute([(int)$p['person_id']]);

    $pdo->prepare("
      INSERT INTO password_resets (person_id, token_hash, expires_at, requested_ip, user_agent)
      VALUES (?, ?, ?, ?, ?)
    ")->execute([
      (int)$p['person_id'],
      $token_hash,
      $expires_at,
      $_SERVER['REMOTE_ADDR'] ?? null,
      substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
    ]);

    $link = request_base_url() . "/password_reset.php?token=" . urlencode($token);

    error_log("PASSWORD RESET LINK for {$email}: {$link}");

    // send email
    send_reset_email($email, $link);
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Password Reset</title>
</head>
<body>
  <h2>Password Reset Request</h2>

  <?php if ($msg): ?>
    <p style="color:green;"><?= h($msg) ?></p>
  <?php endif; ?>

  <form method="post">
    <label>Email</label><br>
    <input name="email" type="email" value="<?= h($email) ?>" required><br><br>
    <button type="submit">Send reset link</button>
  </form>
</body>
</html>
