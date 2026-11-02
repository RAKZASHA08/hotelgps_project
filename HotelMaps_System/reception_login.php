<?php
// StayFinder PH - Dedicated Hotel Reception & Retailer Portal Login
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}
require_once __DIR__ . '/db.php';

// If already logged in to a branch, redirect to reception
if (!empty($_SESSION['reception_logged_in']) && !empty($_SESSION['hotel_id'])) {
    header('Location: reception.php');
    exit;
}

$errorMsg = '';
$prefillBranch = $_GET['branch'] ?? '';

// Load all branches for quick picker
$accountsJsonFile = __DIR__ . '/data/reception_accounts.json';
$allBranches = [];
if (file_exists($accountsJsonFile)) {
    $allBranches = json_decode(file_get_contents($accountsJsonFile), true) ?: [];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $quickHotelId = trim($_POST['quick_hotel_id'] ?? '');

    if (!empty($quickHotelId) && $pdo) {
        // 1-Click Fast Demo Login for selected branch
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'receptionist' AND hotel_id = ? LIMIT 1");
        $stmt->execute([$quickHotelId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = 'receptionist';
            $_SESSION['hotel_id'] = $user['hotel_id'];
            $_SESSION['reception_logged_in'] = true;

            header('Location: reception.php?welcome=1');
            exit;
        } else {
            $errorMsg = "No front desk account found for the selected hotel branch.";
        }
    } elseif (empty($email) || empty($password)) {
        $errorMsg = "Please enter both front desk email and password.";
    } elseif ($pdo) {
        // Standard authentication
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && ($user['role'] === 'receptionist' || $user['role'] === 'admin')) {
            if (password_verify($password, $user['password_hash']) || $password === 'reception123') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['hotel_id'] = $user['hotel_id'] ?: ($prefillBranch ?: 'resort-shang-boracay');
                $_SESSION['reception_logged_in'] = true;

                header('Location: reception.php?welcome=1');
                exit;
            } else {
                $errorMsg = "Invalid front desk password. Please verify and try again.";
            }
        } else {
            $errorMsg = "Account not found or not registered as a hotel reception partner.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Reception &amp; Retailer Portal Login - StayFinder PH</title>

  <!-- Immediate Theme Resolver (Syncs with StayFinder System) -->
  <script>
    (function() {
      const savedTheme = localStorage.getItem('stayfinder_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>

  <!-- Bootstrap 5 & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    :root[data-theme="dark"] body.reception-login-body {
      background: #090D16;
      background-image: 
        radial-gradient(circle at 80% 10%, rgba(224, 86, 36, 0.16) 0%, transparent 45%),
        radial-gradient(circle at 10% 85%, rgba(56, 189, 248, 0.10) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(234, 179, 8, 0.05) 0%, transparent 60%);
      color: #F8FAFC;
    }
    :root[data-theme="light"] body.reception-login-body {
      background: #F4F6F9;
      background-image: 
        radial-gradient(circle at 85% 15%, rgba(224, 86, 36, 0.08) 0%, transparent 45%),
        radial-gradient(circle at 15% 85%, rgba(16, 185, 129, 0.06) 0%, transparent 45%);
      color: #1E293B;
    }

    body.reception-login-body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: background 0.3s ease, color 0.3s ease;
    }

    .login-glass-card {
      background: rgba(15, 23, 42, 0.85);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 24px;
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
    }
    :root[data-theme="light"] .login-glass-card {
      background: rgba(255, 255, 255, 0.95);
      border: 1px solid rgba(0, 0, 0, 0.08);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.10);
    }

    .brand-icon-box {
      width: 56px;
      height: 56px;
      border-radius: 16px;
      background: linear-gradient(135deg, #e05624, #c8714a);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-size: 1.8rem;
      box-shadow: 0 10px 24px rgba(224, 86, 36, 0.35);
    }

    .form-control-reception {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      color: #ffffff;
      padding: 0.75rem 1rem;
      font-size: 0.95rem;
    }
    :root[data-theme="light"] .form-control-reception {
      background: #ffffff;
      border: 1px solid #cbd5e1;
      color: #1e293b;
    }
    .form-control-reception:focus {
      background: rgba(255, 255, 255, 0.10);
      border-color: #e05624;
      box-shadow: 0 0 0 0.25rem rgba(224, 86, 36, 0.25);
      color: inherit;
    }

    .btn-reception-login {
      background: linear-gradient(135deg, #e05624, #b43e16);
      border: none;
      border-radius: 12px;
      color: #ffffff;
      font-weight: 700;
      padding: 0.85rem 1.5rem;
      box-shadow: 0 10px 24px rgba(224, 86, 36, 0.3);
      transition: all 0.2s ease;
    }
    .btn-reception-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 28px rgba(224, 86, 36, 0.45);
      color: #ffffff;
    }

    .quick-branch-pill {
      font-size: 0.75rem;
      border-radius: 20px;
      padding: 0.35rem 0.85rem;
      border: 1px solid rgba(255, 255, 255, 0.15);
      background: rgba(255, 255, 255, 0.04);
      color: #94a3b8;
      transition: all 0.2s ease;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }
    :root[data-theme="light"] .quick-branch-pill {
      border: 1px solid #e2e8f0;
      background: #ffffff;
      color: #475569;
    }
    .quick-branch-pill:hover {
      background: rgba(224, 86, 36, 0.15);
      border-color: #e05624;
      color: #e05624;
      transform: translateY(-1px);
    }

    .info-callout {
      border-left: 4px solid #e05624;
      background: rgba(224, 86, 36, 0.08);
      border-radius: 0 12px 12px 0;
      padding: 0.75rem 1rem;
    }

    /* Brand & Navigation Header Polish */
    .brand-title-text {
      color: #F8FAFC;
    }
    :root[data-theme="light"] .brand-title-text {
      color: #0F172A !important;
    }

    .header-nav-btn {
      color: #F8FAFC;
      border-color: rgba(255, 255, 255, 0.25);
    }
    :root[data-theme="light"] .header-nav-btn {
      color: #0F172A !important;
      border-color: #cbd5e1 !important;
    }
    :root[data-theme="light"] .header-nav-btn:hover {
      background: rgba(200, 113, 74, 0.1) !important;
      color: #c8714a !important;
      border-color: #c8714a !important;
    }

    /* Theme-Adaptive Directory Modal */
    .directory-modal-content {
      background: #0f172a;
      color: #f8fafc;
    }
    .directory-modal-content .modal-title {
      color: #ffffff;
    }
    .directory-modal-content .form-control {
      background: #1e293b;
      border-color: #334155;
      color: #f8fafc;
    }
    .directory-modal-content .input-group-text {
      background: #1e293b;
      border-color: #334155;
      color: #94a3b8;
    }
    .dir-branch-title {
      color: #ffffff;
    }

    :root[data-theme="light"] .directory-modal-content {
      background: #ffffff !important;
      color: #0f172a !important;
    }
    :root[data-theme="light"] .directory-modal-content .modal-title {
      color: #0f172a !important;
    }
    :root[data-theme="light"] .directory-modal-content .form-control {
      background: #ffffff !important;
      border-color: #cbd5e1 !important;
      color: #0f172a !important;
    }
    :root[data-theme="light"] .directory-modal-content .input-group-text {
      background: #f1f5f9 !important;
      border-color: #cbd5e1 !important;
      color: #64748b !important;
    }
    :root[data-theme="light"] .dir-branch-title {
      color: #0f172a !important;
    }
    :root[data-theme="light"] #directoryTable {
      --bs-table-bg: #ffffff;
      --bs-table-color: #1e293b;
      --bs-table-striped-bg: #f8fafc;
      --bs-table-hover-bg: #f1f5f9;
      --bs-table-border-color: #e2e8f0;
      color: #1e293b !important;
    }
    :root[data-theme="light"] .directory-modal-content .btn-close {
      filter: none !important;
      opacity: 0.65;
    }
  </style>
</head>
<body class="reception-login-body">

  <!-- Top Simple Header -->
  <nav class="navbar py-3">
    <div class="container-fluid px-3 px-md-5 d-flex justify-content-between align-items-center">
      <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
        <div class="brand-icon-box" style="width: 40px; height: 40px; font-size: 1.25rem;">
          <i class="bi bi-building-gear"></i>
        </div>
        <div>
          <span class="fw-bold fs-5 brand-title-text">StayFinder PH</span>
          <span class="badge bg-warning text-dark ms-2 fw-bold" style="font-size:0.65rem;">RECEPTION PORTAL</span>
        </div>
      </a>
      <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" id="themeToggleBtn" title="Toggle Theme">
          <i class="bi bi-sun-fill text-warning" id="themeIcon"></i>
        </button>
        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold header-nav-btn">
          <i class="bi bi-arrow-left me-1"></i> Traveler Portal
        </a>
      </div>
    </div>
  </nav>

  <!-- Login Content Box -->
  <main class="container my-auto py-4">
    <div class="row justify-content-center">
      <div class="col-12 col-md-9 col-lg-6 col-xl-5">

        <div class="login-glass-card p-4 p-md-5">

          <!-- Card Header -->
          <div class="text-center mb-4">
            <div class="brand-icon-box mx-auto mb-3">
              <i class="bi bi-key-fill"></i>
            </div>
            <h3 class="fw-extrabold mb-1">Hotel Reception Sign In</h3>
            <p class="text-secondary small mb-0">
              Access your hotel branch's dedicated front-desk reservation queue, room limits, and 360° panorama studio.
            </p>
          </div>

          <!-- Alert Messages -->
          <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3 shadow-sm">
              <i class="bi bi-exclamation-triangle-fill fs-5"></i>
              <div><?= htmlspecialchars($errorMsg) ?></div>
            </div>
          <?php elseif (isset($_GET['logged_out'])): ?>
            <div class="alert alert-success rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3 shadow-sm">
              <i class="bi bi-check-circle-fill fs-5"></i>
              <div>You have safely signed out of the front desk system.</div>
            </div>
          <?php endif; ?>

          <!-- Fast Branch Selector Pick -->
          <div class="mb-4">
            <label class="form-label small fw-bold text-uppercase text-secondary" style="font-size:0.7rem; letter-spacing:0.5px;">
              <i class="bi bi-building me-1 text-warning"></i>Choose Your Hotel Branch
            </label>
            <select class="form-select form-control-reception" id="branchPickerSelect" onchange="autoFillBranch(this.value)">
              <option value="">-- Select Your Hotel Branch (<?= count($allBranches) ?> Partners) --</option>
              <?php foreach ($allBranches as $b): ?>
                <option value="<?= htmlspecialchars($b['hotel_id']) ?>" 
                        data-email="<?= htmlspecialchars($b['email']) ?>"
                        <?= ($prefillBranch === $b['hotel_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($b['title']) ?> (<?= htmlspecialchars($b['city']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Login Form -->
          <form method="POST" action="reception_login.php" id="loginForm">
            <input type="hidden" name="quick_hotel_id" id="quickHotelIdInput" value="">

            <!-- Email Field -->
            <div class="mb-3">
              <label for="emailInput" class="form-label small fw-bold text-uppercase text-secondary" style="font-size:0.7rem; letter-spacing:0.5px;">
                Front Desk Account Email
              </label>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-secondary border-secondary border-opacity-25">
                  <i class="bi bi-envelope"></i>
                </span>
                <input type="email" name="email" id="emailInput" class="form-control form-control-reception border-start-0" 
                       placeholder="desk.okada@stayfinder.ph" required
                       value="<?= htmlspecialchars($_POST['email'] ?? ($prefillBranch ? 'desk.' . preg_replace('/[^a-z0-9]/', '', $prefillBranch) . '@stayfinder.ph' : 'desk.hotelokada@stayfinder.ph')) ?>">
              </div>
            </div>

            <!-- Password Field -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="passwordInput" class="form-label small fw-bold text-uppercase text-secondary mb-0" style="font-size:0.7rem; letter-spacing:0.5px;">
                  Front Desk Password
                </label>
                <span class="small text-secondary" style="font-size:0.75rem;">Default: <code class="text-warning">reception123</code></span>
              </div>
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-secondary border-secondary border-opacity-25">
                  <i class="bi bi-lock"></i>
                </span>
                <input type="password" name="password" id="passwordInput" class="form-control form-control-reception border-start-0 border-end-0" 
                       placeholder="••••••••••••" value="reception123" required>
                <button type="button" class="input-group-text bg-transparent border-start-0 text-secondary border-secondary border-opacity-25" onclick="togglePasswordVisibility()">
                  <i class="bi bi-eye" id="togglePassIcon"></i>
                </button>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-reception-login w-100 py-2 fs-6 mb-3">
              <i class="bi bi-door-open-fill me-2"></i>Sign In to Front Desk
            </button>
          </form>

          <!-- 1-Click Fast Demo Buttons -->
          <div class="pt-3 border-top border-secondary border-opacity-25">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="small text-secondary fw-bold" style="font-size:0.72rem; text-transform:uppercase;">1-Click Demo Accounts:</span>
              <button type="button" class="btn btn-link btn-xs text-info p-0 text-decoration-none small" data-bs-toggle="modal" data-bs-target="#directoryModal">
                View All 55 Accounts →
              </button>
            </div>
            <div class="d-flex flex-wrap gap-1">
              <button type="button" class="quick-branch-pill" onclick="oneClickLogin('hotel-okada')">
                🎰 Okada Manila
              </button>
              <button type="button" class="quick-branch-pill" onclick="oneClickLogin('resort-shang-boracay')">
                🏝️ Shangri-La Boracay
              </button>
              <button type="button" class="quick-branch-pill" onclick="oneClickLogin('sogo-alabang-south')">
                🏨 Hotel Sogo Alabang
              </button>
              <button type="button" class="quick-branch-pill" onclick="oneClickLogin('hotel-peninsula')">
                🏛️ Peninsula Makati
              </button>
              <button type="button" class="quick-branch-pill" onclick="oneClickLogin('hotel-solaire')">
                ✨ Solaire Resort
              </button>
              <button type="button" class="quick-branch-pill" onclick="oneClickLogin('hotel-clark-marriott')">
                🌟 Clark Marriott
              </button>
            </div>
          </div>

        </div>

      </div>
    </div>
  </main>

  <!-- Modal: All 55 Accounts Directory -->
  <div class="modal fade" id="directoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content directory-modal-content border border-secondary border-opacity-25 shadow-lg">
        <div class="modal-header border-secondary border-opacity-25">
          <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-journal-text text-warning"></i> Hotel Branch Front-Desk Directory (55 Partners)
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-3">
          <p class="small text-secondary mb-3">
            Every hotel branch has its own front desk credentials with password <strong class="text-warning">reception123</strong>. Click any branch to sign in instantly.
          </p>
          <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="directorySearchInput" class="form-control" placeholder="Search branch by name, city, or chain..." oninput="filterDirectory()">
          </div>
          <div class="table-responsive" style="max-height: 420px;">
            <table class="table table-dark table-hover table-sm align-middle" id="directoryTable">
              <thead>
                <tr class="text-secondary small">
                  <th>Hotel Branch</th>
                  <th>Location</th>
                  <th>Account Email</th>
                  <th>Password</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allBranches as $b): ?>
                  <tr class="dir-row" data-search="<?= strtolower(htmlspecialchars($b['title'] . ' ' . $b['city'] . ' ' . $b['category'])) ?>">
                    <td class="fw-bold dir-branch-title"><?= htmlspecialchars($b['title']) ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars($b['city']) ?></td>
                    <td><code class="text-info small"><?= htmlspecialchars($b['email']) ?></code></td>
                    <td><span class="badge bg-secondary font-monospace">reception123</span></td>
                    <td class="text-end">
                      <button type="button" class="btn btn-xs btn-primary rounded-pill px-3 py-1 small" onclick="oneClickLogin('<?= htmlspecialchars($b['hotel_id']) ?>')">
                        Sign In →
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Footer -->
  <footer class="py-3 text-center text-secondary small border-top border-secondary border-opacity-10">
    <div class="container">
      &copy; 2026 StayFinder PH &bull; Hotel Reception &amp; Retailer Side Management System &bull; All 55 Branches Isolated
    </div>
  </footer>

  <script>
    // Autofill email when branch select changes
    function autoFillBranch(hotelId) {
      if (!hotelId) return;
      const select = document.getElementById('branchPickerSelect');
      const selectedOption = select.options[select.selectedIndex];
      const email = selectedOption.getAttribute('data-email');
      if (email) {
        document.getElementById('emailInput').value = email;
        document.getElementById('passwordInput').value = 'reception123';
      }
    }

    // 1-Click login helper
    function oneClickLogin(hotelId) {
      document.getElementById('quickHotelIdInput').value = hotelId;
      document.getElementById('loginForm').submit();
    }

    // Password toggle
    function togglePasswordVisibility() {
      const input = document.getElementById('passwordInput');
      const icon = document.getElementById('togglePassIcon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
      } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
      }
    }

    // Theme toggle
    document.addEventListener('DOMContentLoaded', () => {
      const themeToggleBtn = document.getElementById('themeToggleBtn');
      const themeIcon = document.getElementById('themeIcon');

      function syncTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('stayfinder_theme', theme);
        if (themeIcon) {
          themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars text-primary';
        }
      }

      const activeTheme = localStorage.getItem('stayfinder_theme') || 'dark';
      syncTheme(activeTheme);

      if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
          const current = document.documentElement.getAttribute('data-theme') || 'dark';
          const next = current === 'dark' ? 'light' : 'dark';
          syncTheme(next);
        });
      }
    });

    // Directory live search filter
    function filterDirectory() {
      const query = document.getElementById('directorySearchInput').value.toLowerCase().trim();
      const rows = document.querySelectorAll('#directoryTable tbody tr.dir-row');
      rows.forEach(r => {
        const text = r.getAttribute('data-search') || '';
        r.style.display = text.includes(query) ? '' : 'none';
      });
    }
  </script>

</body>
</html>
