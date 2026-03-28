<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
start_session();

$pdo = pdo();
function h($v): string { return htmlspecialchars((string)$v); }

$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$errors = [];
$ok = false;

if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
  http_response_code(400);
  exit("Invalid token.");
}

$token_hash = hash('sha256', $token);

$stmt = $pdo->prepare("
  SELECT password_reset_id, person_id, expires_at, used_at
  FROM password_resets
  WHERE token_hash = ?
  LIMIT 1
");
$stmt->execute([$token_hash]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  http_response_code(400);
  exit("Invalid or expired reset link.");
}

if ($row['used_at'] !== null) {
  http_response_code(400);
  exit("This reset link has already been used.");
}

if (new DateTime() > new DateTime($row['expires_at'])) {
  http_response_code(400);
  exit("This reset link has expired.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pw1 = (string)($_POST['password'] ?? '');
  $pw2 = (string)($_POST['password2'] ?? '');

  if (strlen($pw1) < 8) $errors[] = "Password must be at least 8 characters.";
  if ($pw1 !== $pw2) $errors[] = "Passwords do not match.";

  if (!$errors) {
    $hash = password_hash($pw1, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    $up = $pdo->prepare("UPDATE people SET password_hash = ? WHERE person_id = ?");
    $up->execute([$hash, (int)$row['person_id']]);

    $use = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE password_reset_id = ?");
    $use->execute([(int)$row['password_reset_id']]);

    $pdo->commit();

    $ok = true;
  }
}

// include __DIR__ . '/header.php';
?>

<h1 class="h4 mb-3">Set New Password</h1>

<?php if ($ok): ?>
  <div class="alert alert-success">
    Password updated. You can now <a href="/login.php">log in</a>.
  </div>
<?php else: ?>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0">
      <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="token" value="<?= h($token) ?>">

        <label class="form-label">New password</label>
        <input class="form-control" type="password" name="password" required>

        <label class="form-label mt-3">Confirm new password</label>
        <input class="form-control" type="password" name="password2" required>

        <button class="btn btn-primary mt-3">Update Password</button>
      </form>
    </div>
  </div>

<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
