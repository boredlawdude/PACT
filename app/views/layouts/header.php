<?php
declare(strict_types=1);
?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' — ' : '' ?>PACT</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
 <style>
    .app-navbar {
      background: linear-gradient(90deg, #1e3a5f, #2c5d8a);
    }

  .app-navbar .navbar-brand {
    color: #fff;
    font-weight: 600;
  }

  .app-navbar .nav-link {
    color: rgba(255,255,255,0.85);
  }

  .app-navbar .nav-link:hover {
    color: #fff;


</style>
 


</head>
<body class="bg-light"></body>
<body class="bg-light">

<?php
  try {
      $_orgRow = db()->query("SELECT org_name, logo_path FROM organization_settings ORDER BY id ASC LIMIT 1")->fetch() ?: [];
  } catch (Throwable $e) {
      $_orgRow = [];
  }
  $_orgName = $_orgRow['org_name'] ?? '';
?>
<nav class="navbar navbar-expand-lg app-navbar shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="/index.php?page=dashboard">
      <?php if (!empty($_orgRow['logo_path'])): ?>
        <img src="/<?= htmlspecialchars($_orgRow['logo_path'], ENT_QUOTES, 'UTF-8') ?>"
             alt="logo" style="max-height:32px; max-width:80px; object-fit:contain;">
      <?php endif; ?>
      PACT<?= $_orgName !== '' ? ' for ' . htmlspecialchars($_orgName, ENT_QUOTES, 'UTF-8') : '' ?>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <?php
        $isSuperOrAdmin = false;
        if (function_exists('current_person') && ($p = current_person())) {
          $roles = $p['roles'] ?? [];
          if (
            (is_array($roles) && (in_array('SUPERUSER', $roles, true) || in_array('ADMIN', $roles, true))) ||
            (isset($p['role']) && in_array(strtolower($p['role']), ['superuser', 'admin'], true))
          ) {
            $isSuperOrAdmin = true;
          }
        }
        // Pending badge counts
        $cIntakePending = 0;
        $devAgrPending  = 0;
        try {
            require_once APP_ROOT . '/app/models/ContractIntakeSubmission.php';
            $cIntakePending = (new ContractIntakeSubmission(db()))->countPending();
        } catch (Throwable $e) {}
        try {
            require_once APP_ROOT . '/app/models/DevelopmentAgreementSubmission.php';
            $devAgrPending = (new DevelopmentAgreementSubmission(db()))->countPending();
        } catch (Throwable $e) {}
      ?>
      <ul class="navbar-nav me-auto">

        <li class="nav-item">
          <a class="nav-link" href="/index.php?page=dashboard">Dashboard</a>
        </li>

        <!-- Contracts dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            Contracts
            <?php if ($cIntakePending > 0): ?>
              <span class="badge bg-warning text-dark ms-1"><?= $cIntakePending ?></span>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/index.php?page=contracts">All Contracts</a></li>
            <li><a class="dropdown-item" href="/index.php?page=contracts_create">+ New Contract</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item" href="/index.php?page=contract_intake_list">
                Contract Requests
                <?php if ($cIntakePending > 0): ?>
                  <span class="badge bg-warning text-dark ms-1"><?= $cIntakePending ?></span>
                <?php endif; ?>
              </a>
            </li>
          </ul>
        </li>

        <!-- Dev Agreements dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            Dev Agreements
            <?php if ($devAgrPending > 0): ?>
              <span class="badge bg-warning text-dark ms-1"><?= $devAgrPending ?></span>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/index.php?page=development_agreements">All Dev Agreements</a></li>
            <li>
              <a class="dropdown-item" href="/index.php?page=dev_agreement_submissions">
                Intake Submissions
                <?php if ($devAgrPending > 0): ?>
                  <span class="badge bg-warning text-dark ms-1"><?= $devAgrPending ?></span>
                <?php endif; ?>
              </a>
            </li>
          </ul>
        </li>

        <!-- Directory dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Directory</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/index.php?page=companies">Companies</a></li>
            <li><a class="dropdown-item" href="/index.php?page=companies_create">+ New Company</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/index.php?page=people">People</a></li>
            <li><a class="dropdown-item" href="/index.php?page=departments">Departments</a></li>
          </ul>
        </li>

        <?php if ($isSuperOrAdmin): ?>
        <!-- Admin dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Admin</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/index.php?page=people_create">+ New User</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/index.php?page=admin_settings">System Settings</a></li>
            <li><a class="dropdown-item" href="/index.php?page=admin_organization">Organization Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/admin_password_reset.php">Admin Password Reset</a></li>
          </ul>
        </li>
        <?php endif; ?>

        <li class="nav-item"><a class="nav-link" href="/index.php?page=user_manual">Help</a></li>

      </ul>

      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (function_exists('current_person') && ($p = current_person())): ?>
          <?php $displayName = $p['display_name'] ?? $p['name'] ?? $p['email'] ?? ''; ?>
          <?php if ($displayName): ?>
            <li class="nav-item">
              <span class="nav-link text-light opacity-75 small pe-1">
                <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
              </span>
            </li>
          <?php endif; ?>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link" href="/logout.php">Logout</a>
        </li>
      </ul>

    </div>
  </div>
</nav>

<div class="container">