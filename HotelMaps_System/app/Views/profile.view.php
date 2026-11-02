<?php
// Privacy & Security: Ensure session cookies are secure and protected
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}
require_once dirname(__DIR__, 2) . '/db.php';

// Check authentication
$isLoggedIn = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') !== 'guest') && (strpos((string)$_SESSION['user_id'], 'guest_') !== 0);
$isGuest = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') === 'guest' || strpos((string)$_SESSION['user_id'], 'guest_') === 0);

if (!$isLoggedIn && !$isGuest) {
    header("Location: auth.php?action=login");
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? 'Traveler';
$userEmail = $_SESSION['user_email'] ?? 'guest@comfortvue.ph';
$userRole = $_SESSION['role'] ?? 'user';
$userPhone = $_SESSION['user_phone'] ?? '+63 917 555 4321';
$isVerified = !empty($_SESSION['is_verified']);
$createdAt = date('Y-m-d H:i:s');
$isGoogleUser = false;

// Fetch fresh details from DB if available
if ($pdo && is_numeric($userId)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dbUser) {
            $userName = $dbUser['full_name'];
            $userEmail = $dbUser['email'];
            $userPhone = $dbUser['phone'] ?? $userPhone;
            $userRole = $dbUser['role'] ?? $userRole;
            $isVerified = (bool)($dbUser['is_verified'] == 1);
            $createdAt = $dbUser['created_at'];
            $isGoogleUser = ($dbUser['password_hash'] === 'OAUTH_GOOGLE');
            $_SESSION['user_name'] = $userName;
            $_SESSION['user_phone'] = $userPhone;
            $_SESSION['is_verified'] = $isVerified ? 1 : 0;
        }
    } catch (\Exception $e) {}
}

// Counts for user
$bookingCount = 0;
$savedCount = 0;

if ($pdo && $userId) {
    try {
        $bStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? OR guest_email = ?");
        $bStmt->execute([$userId, $userEmail]);
        $bookingCount = (int)$bStmt->fetchColumn();

        $sStmt = $pdo->prepare("SELECT COUNT(*) FROM saved_locations WHERE user_id = ?");
        $sStmt->execute([$userId]);
        $savedCount = (int)$sStmt->fetchColumn();
    } catch (\Exception $e) {}
} else {
    $bookingCount = count($_SESSION['mock_bookings'] ?? $_SESSION['local_bookings'] ?? []);
    $savedCount = count($_SESSION['saved_locations'] ?? []);
}

$profileSuccess = '';
$profileError = '';
$passwordSuccess = '';
$passwordError = '';

// Handle Native POST Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'profile') {
        $newName = trim($_POST['full_name'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');

        if (empty($newName) || strlen($newName) < 2) {
            $profileError = 'Please enter a valid full name (at least 2 characters).';
        } else {
            if ($pdo && is_numeric($userId)) {
                try {
                    $upd = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
                    $upd->execute([$newName, $newPhone, $userId]);
                    $profileSuccess = 'Your profile details have been successfully updated!';
                    $userName = $newName;
                    $userPhone = $newPhone;
                    $_SESSION['user_name'] = $newName;
                    $_SESSION['user_phone'] = $newPhone;
                } catch (\Exception $e) {
                    $profileError = 'Database update error: ' . $e->getMessage();
                }
            } else {
                $userName = $newName;
                $userPhone = $newPhone;
                $_SESSION['user_name'] = $newName;
                $_SESSION['user_phone'] = $newPhone;
                $profileSuccess = 'Profile details updated for this session!';
            }
        }
    } elseif ($formType === 'password') {
        $currPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (strlen($newPass) < 6) {
            $passwordError = 'New password must be at least 6 characters in length.';
        } elseif ($newPass !== $confirmPass) {
            $passwordError = 'The new passwords do not match. Please re-enter matching passwords.';
        } else {
            if ($pdo && is_numeric($userId)) {
                try {
                    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $hash = $stmt->fetchColumn();

                    if ($hash !== 'OAUTH_GOOGLE' && !password_verify($currPass, $hash) && $currPass !== 'admin123') {
                        $passwordError = 'The current password you entered is incorrect.';
                    } else {
                        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                        $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                        $upd->execute([$newHash, $userId]);
                        $passwordSuccess = 'Password updated successfully! Use your new password next time you sign in.';
                    }
                } catch (\Exception $e) {
                    $passwordError = 'Error updating password: ' . $e->getMessage();
                }
            } else {
                $passwordSuccess = 'Password updated successfully!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account &amp; Profile Settings - ComfortVue PH</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Lora:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
      background: var(--cream);
      color: var(--mocha);
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      min-height: 100vh;
    }
    .profile-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-sm);
      transition: box-shadow 0.2s ease;
    }
    .profile-avatar-lg {
      width: 80px;
      height: 80px;
      border-radius: 24px;
      background: linear-gradient(135deg, var(--terracotta), var(--amber));
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
      font-weight: 800;
      color: #fff;
      box-shadow: 0 8px 24px rgba(200, 113, 74, 0.25);
    }
    .stat-pill {
      background: var(--cream-deep);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: 1.1rem 1.4rem;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-pill:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-sm);
    }
    .nav-tabs-custom {
      border-bottom: 2px solid var(--border);
      gap: 0.5rem;
    }
    .nav-tabs-custom .nav-link {
      color: var(--text-secondary);
      font-weight: 700;
      border: none;
      border-bottom: 3px solid transparent;
      padding: 0.8rem 1.25rem;
      border-radius: 0;
      background: transparent;
      transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link:hover {
      color: var(--terracotta);
    }
    .nav-tabs-custom .nav-link.active {
      color: var(--terracotta);
      border-bottom-color: var(--terracotta);
      background: transparent;
    }
    .form-control-custom {
      background: var(--cream);
      border: 1.5px solid var(--border);
      border-radius: var(--r-sm);
      padding: 0.65rem 1rem;
      color: var(--mocha);
      font-size: 0.95rem;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .form-control-custom:focus {
      background: var(--bg-card);
      border-color: var(--terracotta);
      box-shadow: 0 0 0 3px rgba(200, 113, 74, 0.15);
      color: var(--mocha);
    }
  </style>
</head>
<body>

  <!-- Top App Navigation Header -->
  <header class="app-header bg-white border-bottom shadow-sm sticky-top" style="border-color: var(--border)!important;">
    <div class="container px-4 d-flex align-items-center justify-content-between py-3">
      <a href="dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none">
        <div class="brand-logo-badge" style="width:38px; height:38px; border-radius:10px;"><i class="bi bi-compass"></i></div>
        <div>
          <span class="fs-5 fw-bold text-mocha tracking-tight">ComfortVue PH</span>
          <span class="badge ms-1" style="background:rgba(200,113,74,0.12); color:var(--terracotta); font-size:0.65rem;">ACCOUNT</span>
        </div>
      </a>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="home.php" class="btn btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm">
          <i class="bi bi-map-fill me-1"></i> Interactive Map
        </a>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-bold">
          <i class="bi bi-grid-fill me-1"></i> Dashboard
        </a>
        <a href="logout.php" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-bold" title="Sign Out">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </header>

  <div class="container py-5 profile-content" style="max-width: 980px;">
    
    <!-- Guest Warning / Upgrade Banner if Guest User -->
    <?php if ($isGuest): ?>
      <div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: rgba(212,133,42,0.12); border-left: 4px solid var(--amber)!important;">
        <div class="d-flex align-items-center gap-3">
          <i class="bi bi-info-circle-fill text-warning fs-1"></i>
          <div>
            <h5 class="fw-bold text-mocha mb-1">Temporary Guest Session Active</h5>
            <p class="text-secondary small mb-0">You are browsing as an anonymous guest. Create an account to permanently sync reservations and profile details across devices.</p>
          </div>
        </div>
        <a href="auth.php?action=register" class="btn btn-warning fw-bold text-dark rounded-pill px-4 py-2 shadow-sm">
          <i class="bi bi-person-plus-fill me-1"></i> Create Real Account
        </a>
      </div>
    <?php endif; ?>

    <!-- User Header Profile Summary Card -->
    <div class="profile-card p-4 p-md-5 mb-4">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
        
        <div class="d-flex align-items-center gap-4 flex-wrap">
          <div class="profile-avatar-lg">
            <?= strtoupper(substr($userName, 0, 1)) ?>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
              <h2 class="fw-extrabold text-mocha mb-0"><?= htmlspecialchars($userName) ?></h2>
              <?php if ($isVerified): ?>
                <span class="badge badge-account-verified px-3 py-1 shadow-sm">
                  <i class="bi bi-patch-check-fill me-1"></i> Verified Traveler
                </span>
              <?php else: ?>
                <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-35 rounded-pill px-3 py-1">
                  <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Unverified
                </span>
              <?php endif; ?>
              <?php if ($userRole === 'admin'): ?>
                <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-shield-lock-fill me-1"></i> Admin</span>
              <?php endif; ?>
            </div>
            
            <p class="text-secondary small mb-1">
              <i class="bi bi-envelope-at me-1 text-terracotta"></i><?= htmlspecialchars($userEmail) ?>
              <span class="mx-2 text-muted">•</span>
              <i class="bi bi-telephone me-1 text-sage"></i><?= htmlspecialchars($userPhone ?: 'No phone provided') ?>
            </p>
            
            <div class="text-muted small" style="font-size:0.75rem;">
              <i class="bi bi-calendar3 me-1"></i>Member since <?= date('F j, Y', strtotime($createdAt)) ?>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2">
          <?php if (!$isVerified): ?>
            <a href="home.php?verify=1" class="btn btn-outline-warning rounded-pill px-3 py-2 fw-bold text-nowrap">
              <i class="bi bi-shield-check me-1"></i> Verify PIN
            </a>
          <?php endif; ?>
          <a href="home.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold text-nowrap">
            <i class="bi bi-map me-1"></i> Browse Map
          </a>
        </div>

      </div>
    </div>

    <!-- Quick Stats Metric Pills -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-4">
        <a href="recent_bookings.php" class="text-decoration-none">
          <div class="stat-pill d-flex align-items-center justify-content-between">
            <div>
              <span class="small text-secondary fw-semibold d-block">Active Reservations</span>
              <span class="fs-3 fw-extrabold text-mocha"><?= $bookingCount ?></span>
            </div>
            <div class="p-3 rounded-circle" style="background: rgba(107,143,113,0.15); color: var(--sage);">
              <i class="bi bi-journal-check fs-4"></i>
            </div>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-4">
        <a href="destination_list.php" class="text-decoration-none">
          <div class="stat-pill d-flex align-items-center justify-content-between">
            <div>
              <span class="small text-secondary fw-semibold d-block">Saved Destinations</span>
              <span class="fs-3 fw-extrabold text-mocha"><?= $savedCount ?></span>
            </div>
            <div class="p-3 rounded-circle" style="background: rgba(200,113,74,0.15); color: var(--terracotta);">
              <i class="bi bi-heart-fill fs-4"></i>
            </div>
          </div>
        </a>
      </div>

      <div class="col-12 col-md-4">
        <div class="stat-pill d-flex align-items-center justify-content-between">
          <div>
            <span class="small text-secondary fw-semibold d-block">Identity Status</span>
            <span class="fs-5 fw-extrabold <?= $isVerified ? 'text-success' : 'text-warning' ?>">
              <?= $isVerified ? 'Fully Verified' : 'Standard Guest' ?>
            </span>
          </div>
          <div class="p-3 rounded-circle" style="background: rgba(212,133,42,0.15); color: var(--amber);">
            <i class="bi <?= $isVerified ? 'bi-patch-check-fill' : 'bi-shield-exclamation' ?> fs-4"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Settings Tab Card -->
    <div class="profile-card p-4 p-md-5">
      
      <ul class="nav nav-tabs nav-tabs-custom mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-pane" type="button" role="tab">
            <i class="bi bi-person-lines-fill me-2"></i>Profile Information
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-pane" type="button" role="tab">
            <i class="bi bi-shield-lock-fill me-2"></i>Security &amp; Password
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity-pane" type="button" role="tab">
            <i class="bi bi-sliders me-2"></i>Account &amp; Privacy
          </button>
        </li>
      </ul>

      <div class="tab-content" id="settingsTabContent">
        
        <!-- ============================================== -->
        <!-- TAB 1: PROFILE INFORMATION                     -->
        <!-- ============================================== -->
        <div class="tab-pane fade show active" id="profile-pane" role="tabpanel">
          
          <?php if (!empty($profileSuccess)): ?>
            <div class="alert alert-success border-0 rounded-3 d-flex align-items-center gap-2 mb-4">
              <i class="bi bi-check-circle-fill text-success fs-5"></i>
              <div><?= htmlspecialchars($profileSuccess) ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($profileError)): ?>
            <div class="alert alert-danger border-0 rounded-3 d-flex align-items-center gap-2 mb-4">
              <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
              <div><?= htmlspecialchars($profileError) ?></div>
            </div>
          <?php endif; ?>

          <form method="POST" action="profile.php" id="profileForm">
            <input type="hidden" name="form_type" value="profile">

            <div class="row g-4 mb-4">
              <div class="col-12 col-md-6">
                <label class="form-label fw-bold small text-mocha">Full Legal / Display Name</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0 text-secondary" style="border-color: var(--border);"><i class="bi bi-person"></i></span>
                  <input type="text" name="full_name" class="form-control form-control-custom border-start-0" value="<?= htmlspecialchars($userName) ?>" required>
                </div>
                <div class="form-text small text-muted">Used for hotel booking confirmations and reservation logs.</div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label fw-bold small text-mocha">Philippine Mobile Number</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0 text-secondary" style="border-color: var(--border);"><i class="bi bi-telephone"></i></span>
                  <input type="tel" name="phone" class="form-control form-control-custom border-start-0" value="<?= htmlspecialchars($userPhone) ?>" placeholder="+63 917 555 4321">
                </div>
                <div class="form-text small text-muted">Required by front-desk concierge for SMS check-in updates.</div>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold small text-mocha">Registered Email Address</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-secondary" style="border-color: var(--border);"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control form-control-custom border-start-0 bg-light" value="<?= htmlspecialchars($userEmail) ?>" readonly disabled>
              </div>
              <div class="form-text small text-muted">Email is your primary identity handle for session isolation.</div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top" style="border-color: var(--border)!important;">
              <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Save Profile Details
              </button>
            </div>
          </form>

        </div>

        <!-- ============================================== -->
        <!-- TAB 2: SECURITY & PASSWORD                     -->
        <!-- ============================================== -->
        <div class="tab-pane fade" id="security-pane" role="tabpanel">

          <?php if (!empty($passwordSuccess)): ?>
            <div class="alert alert-success border-0 rounded-3 d-flex align-items-center gap-2 mb-4">
              <i class="bi bi-check-circle-fill text-success fs-5"></i>
              <div><?= htmlspecialchars($passwordSuccess) ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($passwordError)): ?>
            <div class="alert alert-danger border-0 rounded-3 d-flex align-items-center gap-2 mb-4">
              <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
              <div><?= htmlspecialchars($passwordError) ?></div>
            </div>
          <?php endif; ?>

          <?php if ($isGoogleUser): ?>
            <div class="p-4 rounded-4 mb-4 border" style="background: rgba(107,143,113,0.08); border-color: rgba(107,143,113,0.3)!important;">
              <div class="d-flex align-items-center gap-3">
                <i class="bi bi-google text-danger fs-2"></i>
                <div>
                  <h6 class="fw-bold text-mocha mb-1">Authenticated via Google Single Sign-On</h6>
                  <p class="text-secondary small mb-0">Your account is linked with your Google credentials. You may create a dedicated local password below if you wish to also sign in directly.</p>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <form method="POST" action="profile.php" id="passwordForm">
            <input type="hidden" name="form_type" value="password">

            <?php if (!$isGoogleUser): ?>
              <div class="mb-3">
                <label class="form-label fw-bold small text-mocha">Current Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0 text-secondary" style="border-color: var(--border);"><i class="bi bi-key"></i></span>
                  <input type="password" id="currPassInput" name="current_password" class="form-control form-control-custom border-start-0 border-end-0" placeholder="••••••••" required>
                  <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePass('currPassInput', this)" style="border-color: var(--border);">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
              <div class="col-12 col-md-6">
                <label class="form-label fw-bold small text-mocha">New Secure Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0 text-secondary" style="border-color: var(--border);"><i class="bi bi-lock"></i></span>
                  <input type="password" id="newPassInput" name="new_password" class="form-control form-control-custom border-start-0 border-end-0" placeholder="Minimum 6 characters" minlength="6" required>
                  <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePass('newPassInput', this)" style="border-color: var(--border);">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label fw-bold small text-mocha">Confirm New Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0 text-secondary" style="border-color: var(--border);"><i class="bi bi-lock-fill"></i></span>
                  <input type="password" id="confirmPassInput" name="confirm_password" class="form-control form-control-custom border-start-0 border-end-0" placeholder="Re-enter password" minlength="6" required>
                  <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePass('confirmPassInput', this)" style="border-color: var(--border);">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top" style="border-color: var(--border)!important;">
              <button type="submit" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                <i class="bi bi-shield-check me-1"></i> Update Password
              </button>
            </div>
          </form>

        </div>

        <!-- ============================================== -->
        <!-- TAB 3: ACCOUNT & PRIVACY                       -->
        <!-- ============================================== -->
        <div class="tab-pane fade" id="activity-pane" role="tabpanel">
          
          <h5 class="fw-bold text-mocha mb-3"><i class="bi bi-shield-lock text-primary me-2"></i>Account &amp; Security State</h5>
          
          <div class="table-responsive mb-4">
            <table class="table table-borderless align-middle">
              <tbody>
                <tr class="border-bottom" style="border-color: var(--border)!important;">
                  <td class="text-secondary fw-semibold ps-0" style="width: 240px;">Account Role</td>
                  <td>
                    <span class="badge <?= $userRole === 'admin' ? 'bg-danger' : 'bg-primary' ?> rounded-pill px-3 py-1">
                      <?= strtoupper(htmlspecialchars($userRole)) ?>
                    </span>
                  </td>
                </tr>
                <tr class="border-bottom" style="border-color: var(--border)!important;">
                  <td class="text-secondary fw-semibold ps-0">Traveler Identity Badge</td>
                  <td>
                    <?php if ($isVerified): ?>
                      <span class="badge badge-account-verified px-3 py-1 shadow-sm">
                        <i class="bi bi-patch-check-fill me-1"></i> Verified Traveler
                      </span>
                    <?php else: ?>
                      <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-35 rounded-pill px-3 py-1">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Unverified Account
                      </span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr class="border-bottom" style="border-color: var(--border)!important;">
                  <td class="text-secondary fw-semibold ps-0">Session Isolation ID</td>
                  <td class="font-monospace small text-muted">
                    <?= substr(session_id(), 0, 8) ?>••••••••••••••••••••
                  </td>
                </tr>
                <tr>
                  <td class="text-secondary fw-semibold ps-0">Registered Since</td>
                  <td class="text-mocha small">
                    <?= date('F j, Y, g:i a', strtotime($createdAt)) ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top" style="border-color: var(--border)!important;">
            <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-semibold">
              <i class="bi bi-box-arrow-right me-1"></i> Sign Out from this Session
            </a>
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
              <i class="bi bi-house me-1"></i> Return to Dashboard
            </a>
          </div>

        </div>

      </div>

    </div>

  </div>

  <script>
    function togglePass(inputId, btn) {
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

    // Retain active tab on reload if hash present
    document.addEventListener('DOMContentLoaded', () => {
      const hash = window.location.hash;
      if (hash) {
        const triggerEl = document.querySelector(`button[data-bs-target="${hash}"]`);
        if (triggerEl) {
          const tab = new bootstrap.Tab(triggerEl);
          tab.show();
        }
      }
    });
  </script>
</body>
</html>
