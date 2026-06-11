<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

try {
    $_orgRow = db()->query("SELECT * FROM organization_settings ORDER BY id ASC LIMIT 1")->fetch() ?: [];
} catch (Throwable $e) {
    $_orgRow = [];
}
$orgName = $_orgRow['org_name'] ?? 'Your Organization';

$email  = trim(strtolower($_POST['email'] ?? ''));
$next   = (string)($_GET['next'] ?? $_POST['next'] ?? '/index.php?page=dashboard');
$errors = [];

function safe_next_local(string $next, string $fallback = '/index.php?page=dashboard'): string {
    $next = trim($next);
    if ($next === '') return $fallback;
    if (strpos($next, '/') === 0 && strpos($next, '//') !== 0) return $next;
    return $fallback;
}

if (current_person()) {
    header("Location: /index.php?page=dashboard");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = (string)($_POST['password'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    } elseif ($pw === '') {
        $errors[] = "Enter your password.";
    } else {
        if (login_person($email, $pw)) {
            session_write_close();
            header("Location: " . safe_next_local($next, '/index.php?page=dashboard'));
            exit;
        }
        $errors[] = "Invalid email or password.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PACT – <?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f4f8; }
    .login-header { background: linear-gradient(90deg, #1e3a5f, #2c5d8a); }
  </style>
</head>
<body>

  <!-- Top bar with app name, org name, and logo -->
  <div class="login-header d-flex align-items-center justify-content-between px-4 py-3 mb-5">
    <div>
      <div class="text-white fw-bold fs-5">PACT</div>
      <div class="text-white opacity-75 small">Procurement &amp; Contract Lifecycle Tracking for <?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php if (!empty($_orgRow['logo_path'])): ?>
      <img src="/<?= htmlspecialchars($_orgRow['logo_path'], ENT_QUOTES, 'UTF-8') ?>"
           alt="<?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8') ?> logo"
           style="max-height:56px; max-width:180px; object-fit:contain;">
    <?php endif; ?>
  </div>

  <div class="container">
    <div class="card shadow-sm mx-auto" style="max-width:480px;">
      <div class="card-body p-4">
        <h1 class="h4 fw-bold mb-1">Sign in</h1>
        <p class="text-muted small mb-4">PACT for <?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8') ?></p>

        <?php if ($errors): ?>
          <div class="alert alert-danger"><ul class="mb-0">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
          </ul></div>
        <?php endif; ?>

        <form method="post" action="/login.php" autocomplete="on">
          <input type="hidden" name="next" value="<?= htmlspecialchars($next, ENT_QUOTES, 'UTF-8') ?>">

          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" required
                 value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username">

          <label class="form-label mt-3">Password</label>
          <input class="form-control" type="password" name="password" required
                 autocomplete="current-password">

          <div class="d-flex align-items-center mt-3">
            <button class="btn btn-primary" type="submit">Sign in</button>
            <a class="ms-auto small" href="/password_reset_request.php">Forgot your password?</a>
          </div>
        </form>
      </div>
    </div>

    <p class="text-center text-muted mt-4" style="font-size:.75rem;">
      PACT is the <strong>P</strong>rocurement &amp; <strong>C</strong>ontract Lifecycle
      <strong>T</strong>racking system for <?= htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8') ?>
    </p>
  </div>

</body>
</html>
