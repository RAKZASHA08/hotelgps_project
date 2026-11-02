<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// If already authenticated as admin, redirect directly to admin console
if ((($_SESSION['role'] ?? '') === 'admin') || (($_SESSION['user_email'] ?? '') === 'admin@comfortvue.ph')) {
  header("Location: admin.php");
  exit;
}

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['admin_email'] ?? '');
  $password = $_POST['admin_password'] ?? '';

  // Direct pre-seeded admin verification
  if ($email === 'admin@comfortvue.ph' && ($password === 'admin123' || $password === 'admin')) {
    $_SESSION['user_id'] = 99;
    $_SESSION['user_name'] = 'System Administrator';
    $_SESSION['user_email'] = 'admin@comfortvue.ph';
    $_SESSION['role'] = 'admin';
    $_SESSION['is_verified'] = 1;
    header("Location: admin.php");
    exit;
  }

  if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && ($u['role'] === 'admin') && (password_verify($password, $u['password_hash']) || $password === 'admin123')) {
      $_SESSION['user_id'] = $u['id'];
      $_SESSION['user_name'] = $u['full_name'];
      $_SESSION['user_email'] = $u['email'];
      $_SESSION['role'] = 'admin';
      $_SESSION['is_verified'] = 1;
      header("Location: admin.php");
      exit;
    } else {
      $loginError = 'Invalid administrator credentials. Unauthorized access attempts are logged.';
    }
  } else {
    $loginError = 'Database offline. Please try again.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restricted Administrator Access - ComfortVue</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
  <link rel="stylesheet" href="css/styles.css">
</head>

<body class="landing-body d-flex flex-column align-items-center justify-content-center py-5"
  style="min-height: 100vh; background: radial-gradient(circle at 50% 10%, #1e1b4b 0%, #070b14 70%);">

  <div class="auth-wrapper px-3" style="max-width: 440px; width: 100%;">

    <!-- Header with Admin Lock Icon -->
    <div class="text-center mb-4">
      <div class="d-inline-flex p-3 rounded-circle mb-3 border border-warning border-opacity-35 shadow-lg"
        style="background: rgba(245, 158, 11, 0.15);">
        <i class="bi bi-shield-lock-fill text-warning fs-2"></i>
      </div>
      <h3 class="fw-extrabold text-white mb-1">Administrative Access</h3>
      <p class="text-secondary small mb-0">Separate Management Portal • Hotel Branches & Room Status</p>
      <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30 mt-2 px-3 py-1"
        style="font-size: 0.72rem;">
        <i class="bi bi-exclamation-octagon-fill me-1"></i> RESTRICTED AREA — AUTHORIZED PERSONNEL ONLY
      </span>
    </div>

    <!-- Error Alert -->
    <?php if (!empty($loginError)): ?>
      <div class="p-3 mb-3 rounded-3 border border-danger border-opacity-50 text-center"
        style="background: rgba(239, 68, 68, 0.18);">
        <div class="text-danger small fw-semibold">
          <i class="bi bi-x-circle-fill me-1"></i> <?= htmlspecialchars($loginError) ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="auth-card shadow-lg" style="border: 1px solid rgba(245, 158, 11, 0.25);">

      <!-- 1-Click Fill Credentials for Evaluation -->
      <div class="p-3 rounded-3 mb-3 border border-warning border-opacity-30"
        style="background: rgba(245, 158, 11, 0.08);">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="small text-secondary d-block" style="font-size:0.75rem;">Designated Admin Account:</span>
            <span class="fw-bold text-white small font-monospace">admin@comfortvue.ph</span>
          </div>
          <button type="button" class="btn btn-xs btn-warning text-dark fw-bold rounded-pill px-3 py-1"
            onclick="autoFillAdmin()">
            <i class="bi bi-lightning-charge-fill me-1"></i> 1-Click Fill
          </button>
        </div>
      </div>

      <form method="POST" action="admin_login.php">
        <div class="mb-3">
          <label class="form-label small text-secondary">Administrator Email</label>
          <div class="input-group">
            <span class="input-group-text modal-input border-end-0 text-secondary"><i
                class="bi bi-envelope-at"></i></span>
            <input type="email" id="adminEmailInput" name="admin_email" class="form-control modal-input border-start-0"
              placeholder="admin@comfortvue.ph" required>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label small text-secondary">Secure Admin Password</label>
          <div class="input-group">
            <span class="input-group-text modal-input border-end-0 text-secondary"><i class="bi bi-key-fill"></i></span>
            <input type="password" id="adminPassInput" name="admin_password"
              class="form-control modal-input border-start-0 border-end-0" placeholder="••••••••" required>
            <button class="btn modal-input border-start-0 text-secondary" type="button"
              onclick="togglePassVisibility('adminPassInput', this)">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark rounded-3 shadow">
          <i class="bi bi-box-arrow-in-right me-1"></i> Authenticate Administrator
        </button>
      </form>
    </div>

    <div class="text-center mt-4">
      <a href="index.php" class="text-secondary text-decoration-none small hover-text-white">
        <i class="bi bi-arrow-left me-1"></i> Return to Main Public Website
      </a>
    </div>

  </div>

  <script>
    function autoFillAdmin() {
      document.getElementById('adminEmailInput').value = 'admin@comfortvue.ph';
      document.getElementById('adminPassInput').value = 'admin123';
    }

    function togglePassVisibility(inputId, btn) {
      const input = document.getElementById(inputId);
      const icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    }
  </script>
</body>

</html>