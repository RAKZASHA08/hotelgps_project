<?php
// Privacy & Security: Ensure session cookies are secure and protected
$isLoggedIn = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') !== 'guest') && (strpos((string)$_SESSION['user_id'], 'guest_') !== 0);
$userName = $_SESSION['user_name'] ?? 'Traveler';
$userEmail = $_SESSION['user_email'] ?? 'guest@stayfinder.ph';
$userRole = $_SESSION['role'] ?? 'guest';
$isAdmin = ($userRole === 'admin') || ($userEmail === 'admin@stayfinder.ph') || ($userEmail === 'admin@comfortvue.ph');
$isVerified = false;
$userBookingsCount = 0;

if ($isLoggedIn && $pdo && !empty($userEmail)) {
    try {
        $stmt = $pdo->prepare("SELECT is_verified, role FROM users WHERE email = ?");
        $stmt->execute([$userEmail]);
        $uRow = $stmt->fetch();
        if ($uRow) {
            $isVerified = (bool)($uRow['is_verified'] == 1);
            if ($uRow['role'] === 'admin') $isAdmin = true;
        }
    } catch (\Exception $e) {}
} else {
    $isVerified = !empty($_SESSION['is_verified']);
}

// Access Control: Reception Desk & 360 Studio portal are strictly restricted to Hotel Staff & Retailers
$isStaff = !empty($_SESSION['reception_logged_in']) 
    || in_array(strtolower($userRole), ['receptionist', 'retailer', 'hotel_admin', 'admin']) 
    || $isAdmin;

// Fetch user recent bookings
$userRecentBookings = [];
if ($isLoggedIn && $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE guest_email = ? OR user_id = ? ORDER BY created_at DESC LIMIT 4");
        $stmt->execute([$userEmail, (string)($_SESSION['user_id'] ?? '')]);
        $userRecentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $userBookingsCount = count($userRecentBookings);
        
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE (guest_email = ? OR user_id = ?) AND status = 'CONFIRMED'");
        $countStmt->execute([$userEmail, (string)($_SESSION['user_id'] ?? '')]);
        $userBookingsCount = (int)$countStmt->fetchColumn();
    } catch (\Exception $e) {}
}

if (empty($userRecentBookings) && !empty($_SESSION['mock_bookings'])) {
    $userRecentBookings = array_slice($_SESSION['mock_bookings'], 0, 4);
    $userBookingsCount = count($_SESSION['mock_bookings']);
}

// Fetch top trending hotels from db or fallback inventory
$trendingHotels = [];
$totalHotelsCount = 55;
if ($pdo) {
    try {
        $totalHotelsCount = (int)$pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
        $stmt = $pdo->query("SELECT id, title, branch_name, city, price_from, image, category_display, rating, views_count FROM hotels ORDER BY rating DESC, views_count DESC LIMIT 6");
        $trendingHotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {}
}

if (empty($trendingHotels)) {
    $inventoryFile = dirname(__DIR__, 2) . '/data/stays_inventory.json';
    if (file_exists($inventoryFile)) {
        $raw = json_decode(file_get_contents($inventoryFile), true) ?: [];
        $totalHotelsCount = count($raw);
        $trendingHotels = array_slice($raw, 0, 6);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - StayFinder PH</title>
  
  <!-- Immediate Dark Mode Preference Resolver -->
  <script>
    (function() {
      const savedTheme = localStorage.getItem('stayfinder_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css?v=2.2">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    :root[data-theme="dark"] body.dashboard-body {
      background: #090D16;
      background-image: 
        radial-gradient(circle at 85% 12%, rgba(224, 86, 36, 0.12) 0%, transparent 45%),
        radial-gradient(circle at 15% 75%, rgba(56, 189, 248, 0.08) 0%, transparent 45%),
        radial-gradient(circle at 50% 50%, rgba(200, 113, 74, 0.04) 0%, transparent 60%);
      background-attachment: fixed;
      color: #F8FAFC;
    }

    :root[data-theme="light"] body.dashboard-body {
      background: #F8F6F0;
      background-image: 
        radial-gradient(circle at 85% 10%, rgba(224, 86, 36, 0.08) 0%, transparent 45%),
        radial-gradient(circle at 10% 80%, rgba(107, 143, 113, 0.08) 0%, transparent 45%);
      background-attachment: fixed;
      color: #1E293B;
    }

    body.dashboard-body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      min-height: 100vh;
      overflow-x: hidden;
      transition: background 0.3s ease, color 0.3s ease;
    }

    /* Hero Banner */
    .dashboard-hero-banner {
      position: relative;
      border-radius: 20px;
      padding: 3rem 2.5rem;
      margin-bottom: 2rem;
      overflow: hidden;
      background: linear-gradient(135deg, rgba(224, 86, 36, 0.92), rgba(186, 68, 28, 0.95)), 
                  url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1920&q=80') center/cover;
      box-shadow: 0 16px 36px rgba(224, 86, 36, 0.25);
      border: 1px solid rgba(255, 255, 255, 0.18);
    }
    .dashboard-hero-banner::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent 70%);
      pointer-events: none;
    }

    /* Glassmorphic Stat Cards */
    .dashboard-stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1.3rem 1.4rem;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .dashboard-stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 14px 32px rgba(0, 0, 0, 0.16);
      border-color: rgba(224, 86, 36, 0.4);
    }
    .stat-icon-wrapper {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.35rem;
    }

    /* Quick Filter Jump Bar */
    .dashboard-search-container {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1rem 1.25rem;
      margin-bottom: 2rem;
      box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    }

    /* Modernized Trend Hotel Cards */
    .dashboard-hotel-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 6px 20px rgba(0,0,0,0.06);
      transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .dashboard-hotel-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 36px rgba(0, 0, 0, 0.18);
      border-color: var(--terracotta);
    }
    .dashboard-hotel-img-box {
      position: relative;
      width: 100%;
      height: 200px;
      overflow: hidden;
    }
    .dashboard-hotel-img-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.45s ease;
    }
    .dashboard-hotel-card:hover .dashboard-hotel-img-box img {
      transform: scale(1.05);
    }

    /* Recent Booking Mini Table */
    .dashboard-booking-row {
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 0.9rem 1.1rem;
      margin-bottom: 0.75rem;
      transition: all 0.2s ease;
    }
    .dashboard-booking-row:hover {
      background: rgba(224, 86, 36, 0.05);
      border-color: rgba(224, 86, 36, 0.3);
    }
  </style>
</head>
<body class="dashboard-body">

  <!-- Top Application Header (Synchronized with Map Page) -->
  <header class="app-header sticky-top">
    <div class="container-fluid px-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
      <!-- Brand Logo -->
      <a href="index.php" class="d-flex align-items-center gap-3 text-decoration-none">
        <div class="brand-logo-badge"><i class="bi bi-compass"></i></div>
        <div>
          <div class="d-flex align-items-center gap-2">
            <span class="fs-5 fw-bold tracking-tight brand-title-text" style="color: var(--mocha);">StayFinder PH</span>
            <span class="badge" style="background: rgba(200,113,74,0.18); color: var(--terracotta); border: 1px solid rgba(200,113,74,0.35); font-size: 0.68rem;">
              <i class="bi bi-patch-check-fill me-1"></i>Metro Manila
            </span>
          </div>
          <div class="small" style="font-size: 0.72rem; color: var(--text-muted);">Hotels • Condo Staycations • Short-Term Rentals</div>
        </div>
      </a>

      <!-- Navigation & Action Controls -->
      <div class="d-flex align-items-center flex-wrap gap-2">
        <a href="dashboard.php" class="btn-nav-pill active text-decoration-none">
          <i class="bi bi-grid-fill"></i> Dashboard
        </a>
        <a href="home.php" class="btn-nav-pill text-decoration-none">
          <i class="bi bi-map-fill text-warning"></i> Map &amp; Hotels
        </a>
        <a href="home.php?view=bookings" class="btn-nav-pill text-decoration-none">
          <i class="bi bi-journal-check text-success"></i> My Bookings
          <span class="badge rounded-pill ms-1" style="background:var(--sage); font-size: 0.65rem;"><?= $userBookingsCount ?> Active</span>
        </a>
        <?php if ($isStaff): ?>
        <a href="reception.php" class="btn-nav-pill text-decoration-none" title="Hotel Front Desk & Retailer Portal">
          <i class="bi bi-building-gear text-info"></i> Reception Desk
        </a>
        <?php endif; ?>

        <!-- Account Verification Badge -->
        <?php if ($isVerified): ?>
          <span class="badge badge-account-verified px-3 py-2 shadow-sm">
            <i class="bi bi-patch-check-fill"></i> Account Verified
          </span>
        <?php else: ?>
          <a href="home.php?action=verify" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" title="Verify your account with 6-digit PIN">
            <i class="bi bi-shield-exclamation text-warning"></i> Verify Account
          </a>
        <?php endif; ?>

        <!-- Theme Toggle Button (Synchronized with Hotel Map Page) -->
        <button class="btn-nav-pill px-3" id="themeToggleBtn" type="button" title="Toggle Dark/Light Mode">
          <i class="bi bi-sun-fill text-warning"></i>
          <span class="d-none d-sm-inline fw-semibold ms-1" id="themeToggleLabel" style="font-size:0.78rem;">Dark</span>
        </button>

        <!-- User Dropdown Menu -->
        <div class="dropdown">
          <button class="btn-nav-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle text-info"></i> <?= htmlspecialchars($userName) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border border-secondary border-opacity-50">
            <li>
              <div class="px-3 py-2">
                <span class="fw-bold text-white d-block"><?= htmlspecialchars($userName) ?></span>
                <span class="small text-secondary d-block"><?= htmlspecialchars($userEmail) ?></span>
                <div class="mt-2">
                  <?php if ($isVerified): ?>
                    <span class="badge badge-account-verified px-2 py-1" style="font-size:0.7rem;">
                      <i class="bi bi-patch-check-fill me-1"></i>Verified Traveler
                    </span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark" style="font-size:0.65rem;">
                      <i class="bi bi-exclamation-triangle me-1"></i>Unverified Account
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </li>
            <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
            <li>
              <a class="dropdown-item text-light" href="home.php">
                <i class="bi bi-map me-2 text-warning"></i>Open Hotel &amp; Route Map
              </a>
            </li>
            <li>
              <a class="dropdown-item text-light" href="home.php?view=bookings">
                <i class="bi bi-journal-text me-2 text-success"></i>My Reservations (<?= $userBookingsCount ?>)
              </a>
            </li>
            <li>
              <a class="dropdown-item text-light" href="profile.php">
                <i class="bi bi-person-gear me-2 text-info"></i>Account &amp; Profile Settings
              </a>
            </li>
            <?php if ($isStaff): ?>
            <li>
              <a class="dropdown-item text-warning" href="reception.php">
                <i class="bi bi-building-gear me-2 text-warning"></i>Hotel Reception / Retailer Desk
              </a>
            </li>
            <?php endif; ?>
            <?php if ($isAdmin): ?>
              <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
              <li><a class="dropdown-item text-warning" href="admin.php"><i class="bi bi-shield-lock me-2"></i>Admin Console</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
          </ul>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Container -->
  <main class="container-fluid px-3 px-md-5 py-4">

    <?php if (isset($_GET['error']) && $_GET['error'] === 'reception_restricted'): ?>
      <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center justify-content-between" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35) !important;">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-shield-lock-fill text-warning fs-5"></i>
          <div>
            <strong class="text-white">Access Restricted to Receptionists &amp; Retailers</strong>
            <div class="small text-secondary">The Hotel Reception Desk &amp; 360° Studio Portal is exclusively for authorized hotel branch staff. As a valued client traveler, you can explore hotels and manage your reservations below.</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Hero Greeting Banner -->
    <div class="dashboard-hero-banner text-white">
      <div class="row align-items-center g-4">
        <div class="col-12 col-lg-8">
          <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 fw-semibold" style="font-size:0.75rem; letter-spacing:0.5px;">
            <i class="bi bi-sparkles me-1"></i>STAYFINDER PH EXECUTIVE PORTAL
          </span>
          <h1 class="fw-extrabold display-5 mb-2 lh-sm">
            Welcome back, <?= htmlspecialchars($userName) ?>! 👋
          </h1>
          <p class="fs-6 opacity-90 mb-4" style="max-width: 680px; font-weight: 400;">
            Discover verified hotels, commuter hourly suites, and live multi-modal travel routes with turn-by-turn navigation across Metro Manila.
          </p>
          <div class="d-flex flex-wrap gap-2">
            <a href="home.php" class="btn btn-light btn-lg rounded-pill fw-bold text-terracotta shadow px-4 py-2 fs-6">
              <i class="bi bi-map-fill me-2"></i>Explore Hotel Map &amp; Stays
            </a>
            <?php if ($isStaff): ?>
            <a href="reception.php" class="btn btn-warning btn-lg rounded-pill fw-bold text-dark shadow px-3 py-2 fs-6">
              <i class="bi bi-building-gear me-1"></i>Hotel Reception Desk
            </a>
            <?php endif; ?>
            <a href="home.php?category=all&locate=1" class="btn btn-outline-light btn-lg rounded-pill fw-bold px-3 py-2 fs-6">
              <i class="bi bi-geo-alt-fill me-1"></i>Manila Bay Stays
            </a>
            <a href="home.php?view=bookings" class="btn btn-outline-light btn-lg rounded-pill fw-bold px-3 py-2 fs-6">
              <i class="bi bi-journal-check me-1"></i>My Reservations
            </a>
          </div>
        </div>
        <div class="col-12 col-lg-4 text-lg-end d-none d-lg-block">
          <div class="p-3 rounded-4 d-inline-block text-start border border-white border-opacity-25 shadow-sm" style="background: rgba(0,0,0,0.22); backdrop-filter: blur(12px); min-width: 250px;">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-shield-check text-warning fs-4"></i>
              <div>
                <div class="fw-bold small text-white">Anti-Spam Shield</div>
                <div class="small opacity-75" style="font-size:0.72rem;">20% Downpayment Gateway</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2 pt-2 border-top border-white border-opacity-20">
              <span class="badge bg-success bg-opacity-75">GCash</span>
              <span class="badge bg-success bg-opacity-75">Maya</span>
              <span class="badge bg-info bg-opacity-75">GrabPay</span>
              <span class="badge bg-primary bg-opacity-75">Cards</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 4 Executive Stat Cards -->
    <div class="row g-3 mb-4">
      <!-- Stat 1: Active Bookings -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="dashboard-stat-card">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <span class="text-secondary small fw-semibold d-block text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">My Reservations</span>
              <h2 class="fw-extrabold stat-value mb-0 mt-1"><?= $userBookingsCount ?></h2>
            </div>
            <div class="stat-icon-wrapper" style="background: rgba(107, 143, 113, 0.15); color: var(--sage);">
              <i class="bi bi-journal-check"></i>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-20">
            <span class="small text-secondary" style="font-size:0.75rem;">Guaranteed bookings</span>
            <a href="home.php?view=bookings" class="small text-info text-decoration-none fw-semibold">View All →</a>
          </div>
        </div>
      </div>

      <!-- Stat 2: Verification Status -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="dashboard-stat-card">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <span class="text-secondary small fw-semibold d-block text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Account Tier</span>
              <h4 class="fw-bold mb-0 mt-2 stat-value">
                <?= $isVerified ? 'Verified Guest' : 'Standard Guest' ?>
              </h4>
            </div>
            <div class="stat-icon-wrapper" style="background: <?= $isVerified ? 'rgba(56, 189, 248, 0.15)' : 'rgba(234, 179, 8, 0.15)' ?>; color: <?= $isVerified ? '#38bdf8' : '#eab308' ?>;">
              <i class="bi <?= $isVerified ? 'bi-patch-check-fill' : 'bi-shield-exclamation' ?>"></i>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-20">
            <span class="small text-secondary" style="font-size:0.75rem;">
              <?= $isVerified ? 'Full booking privileges' : 'PIN validation pending' ?>
            </span>
            <?php if (!$isVerified): ?>
              <a href="home.php?action=verify" class="small text-warning text-decoration-none fw-bold">Verify PIN →</a>
            <?php else: ?>
              <span class="badge bg-success bg-opacity-25 text-success small py-1">Active Pass</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Stat 3: Total Accommodations -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="dashboard-stat-card">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <span class="text-secondary small fw-semibold d-block text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Metro Manila Stays</span>
              <h2 class="fw-extrabold stat-value mb-0 mt-1"><?= $totalHotelsCount ?>+</h2>
            </div>
            <div class="stat-icon-wrapper" style="background: rgba(224, 86, 36, 0.15); color: var(--terracotta);">
              <i class="bi bi-buildings"></i>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-20">
            <span class="small text-secondary" style="font-size:0.75rem;">Hotels, Motels &amp; Condos</span>
            <a href="home.php" class="small text-terracotta text-decoration-none fw-semibold">View Pins →</a>
          </div>
        </div>
      </div>

      <!-- Stat 4: Multi-Modal Live Routes -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="dashboard-stat-card">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <span class="text-secondary small fw-semibold d-block text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Navigation Engine</span>
              <h4 class="fw-bold mb-0 mt-2 stat-value">5 Travel Modes</h4>
            </div>
            <div class="stat-icon-wrapper" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">
              <i class="bi bi-sign-turn-right-fill"></i>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-20">
            <span class="small text-secondary" style="font-size:0.75rem;">Car, Moto, Bike, Train, Flight</span>
            <a href="home.php" class="small text-info text-decoration-none fw-semibold">Live GPS →</a>
          </div>
        </div>
      </div>
    </div>

    <?php if ($isStaff): ?>
    <!-- Hotel Retailer & Reception Desk Banner -->
    <div class="card retailer-promo-dark border-0 rounded-4 p-3 p-md-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, rgba(224, 86, 36, 0.15), rgba(15, 23, 42, 0.95)); border: 1px solid rgba(224, 86, 36, 0.35) !important;">
      <div class="row align-items-center g-3">
        <div class="col-12 col-lg-8">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size:0.7rem;">
              <i class="bi bi-building-gear me-1"></i>RETAILER &amp; BRANCH SYSTEM
            </span>
            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-30" style="font-size:0.7rem;">
              <i class="bi bi-broadcast me-1"></i>Real-Time Desk Chime Alert
            </span>
          </div>
          <h4 class="fw-bold text-white-pure mb-1">Hotel Reception Desk &amp; 360° Studio Portal</h4>
          <p class="small mb-0" style="color: #CBD5E1 !important; max-width: 720px;">
            Front desk portal for each hotel branch: receive instant live booking requests with audio brass bell chimes, set room inventory limits to prevent overbooking, and upload customized 360° panoramic virtual tours directly into guest view.
          </p>
        </div>
        <div class="col-12 col-lg-4 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end">
          <a href="reception.php" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
            <i class="bi bi-door-open-fill me-1"></i>Enter Reception Portal
          </a>
          <a href="reception.php" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold small text-white">
            <i class="bi bi-question-circle me-1"></i>Setup Guide
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Quick Search & Category Jump Bar -->
    <div class="dashboard-search-container mb-4">
      <div class="row align-items-center g-3">
        <div class="col-12 col-lg-5">
          <form action="home.php" method="GET" class="d-flex gap-2">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" name="search" class="form-control" placeholder="Search Makati, Solaire, BGC, Sogo...">
              <button class="btn btn-primary fw-semibold px-3" type="submit">Search Stays</button>
            </div>
          </form>
        </div>
        <div class="col-12 col-lg-7">
          <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
            <span class="text-secondary small me-1 d-none d-md-inline">Quick Categories:</span>
            <a href="home.php?category=hotel" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
              <i class="bi bi-building text-warning me-1"></i>Hotels
            </a>
            <a href="home.php?category=motel" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
              <i class="bi bi-car-front-fill text-primary me-1"></i>Motels &amp; Hourly
            </a>
            <a href="home.php?category=airbnb" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
              <i class="bi bi-key-fill text-info me-1"></i>AirBnB &amp; Condos
            </a>
            <a href="home.php?commuter=1" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 text-warning">
              <i class="bi bi-train-front-fill me-1"></i>Walk-in Commuter
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Grid: Recent Bookings & Trending Hotels -->
    <div class="row g-4 mb-5">
      <!-- Left Column: Recent Bookings Widget -->
      <div class="col-12 col-xl-4">
        <div class="dashboard-stat-card h-100">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold section-title mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-journal-text text-terracotta"></i> Recent Bookings
            </h5>
            <a href="home.php?view=bookings" class="small text-info text-decoration-none">Manage All →</a>
          </div>

          <?php if (!empty($userRecentBookings)): ?>
            <div class="d-flex flex-column gap-2 mb-3">
              <?php foreach ($userRecentBookings as $bk): ?>
                <div class="dashboard-booking-row">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="fw-bold booking-title small"><?= htmlspecialchars($bk['stayTitle'] ?? $bk['hotel_title'] ?? 'Hotel Suite') ?></span>
                    <span class="badge bg-success bg-opacity-25 text-success font-monospace" style="font-size:0.65rem;">
                      <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($bk['status'] ?? 'CONFIRMED') ?>
                    </span>
                  </div>
                  <div class="text-secondary small mb-1" style="font-size:0.72rem;">
                    <?= htmlspecialchars($bk['roomName'] ?? $bk['room_name'] ?? 'Deluxe Suite') ?> 
                    • <?= htmlspecialchars($bk['stayType'] ?? $bk['stay_type'] ?? '1 Night') ?>
                  </div>
                  <div class="d-flex justify-content-between align-items-center pt-1 border-top border-secondary border-opacity-20" style="font-size:0.72rem;">
                    <span class="text-secondary">
                      Paid Deposit: <strong class="text-success">₱<?= number_format((float)($bk['downpayment_amount'] ?? ((float)$bk['amount'] * 0.2))) ?></strong>
                    </span>
                    <span class="text-secondary">
                      Bal: <strong class="text-warning">₱<?= number_format((float)($bk['balance_due'] ?? ((float)$bk['amount'] * 0.8))) ?></strong>
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-5 my-auto text-secondary">
              <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
              <div class="fw-semibold section-title mb-1">No Reservations Yet</div>
              <p class="small text-secondary mb-3">Book your first stay with instant online down payment confirmation.</p>
              <a href="home.php" class="btn btn-sm btn-primary rounded-pill px-3">Browse Hotels Now</a>
            </div>
          <?php endif; ?>

          <div class="p-3 rounded-3 mt-auto" style="background: rgba(224, 86, 36, 0.08); border: 1px dashed rgba(224, 86, 36, 0.25);">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-shield-lock-fill text-warning fs-5"></i>
              <div class="small text-secondary" style="font-size:0.72rem;">
                Reservations are locked with a 20% down payment deposit to eliminate fake spam bookings. Remaining balance settled upon hotel arrival.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Trending Accommodations Grid -->
      <div class="col-12 col-xl-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h4 class="fw-bold section-title mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-fire text-danger"></i> Trending Accommodations in Metro Manila
          </h4>
          <a href="home.php" class="small text-info text-decoration-none fw-semibold">View All 55+ on Map →</a>
        </div>

        <div class="row g-3">
          <?php foreach ($trendingHotels as $h): ?>
            <div class="col-12 col-md-6 col-lg-4">
              <div class="dashboard-hotel-card">
                <div class="dashboard-hotel-img-box">
                  <img src="<?= htmlspecialchars($h['image']) ?>" alt="<?= htmlspecialchars($h['title']) ?>">
                  <span class="badge position-absolute top-0 start-0 m-2 bg-dark bg-opacity-75 text-white-pure" style="font-size:0.65rem;">
                    <?= htmlspecialchars($h['category_display'] ?? 'Hotel') ?>
                  </span>
                  <span class="badge position-absolute top-0 end-0 m-2 bg-dark bg-opacity-75 text-warning" style="font-size:0.68rem;">
                    ★ <?= number_format((float)($h['rating'] ?? 4.9), 2) ?>
                  </span>
                </div>
                <div class="p-3 d-flex flex-column flex-grow-1 justify-content-between">
                  <div>
                    <h6 class="fw-bold hotel-card-title mb-1 lh-sm text-truncate" title="<?= htmlspecialchars($h['title']) ?>">
                      <?= htmlspecialchars($h['title']) ?>
                    </h6>
                    <div class="text-secondary small mb-2" style="font-size:0.72rem;">
                      <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($h['city'] ?? 'Metro Manila') ?>
                    </div>
                  </div>
                  <div class="pt-2 border-top border-secondary border-opacity-20 d-flex align-items-center justify-content-between gap-1">
                    <div>
                      <span class="text-secondary d-block" style="font-size:0.65rem;">FROM</span>
                      <span class="text-success fw-bold fs-6">₱<?= number_format((float)$h['price_from']) ?></span>
                    </div>
                    <div class="d-flex gap-1">
                      <a href="home.php?locate=<?= urlencode($h['id']) ?>" class="btn btn-xs btn-outline-info py-1 px-2" style="font-size:0.68rem;" title="View on Map">
                        <i class="bi bi-pin-map-fill me-1"></i>Map
                      </a>
                      <a href="home.php?tour=<?= urlencode($h['id']) ?>" class="btn btn-xs btn-primary py-1 px-2" style="font-size:0.68rem;" title="Virtual 360 View">
                        <i class="bi bi-vr me-1"></i>360°
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Seasonal Escapes & Travel Recommendations -->
    <div class="mb-5">
      <h4 class="fw-bold section-title mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-compass-fill text-info"></i> Curated Getaway Destinations (2026)
      </h4>
      <div class="row g-3">
        <!-- Card 1: Beach Escapes -->
        <div class="col-12 col-md-4">
          <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100" style="background: var(--bg-card); border: 1px solid var(--border)!important;">
            <div style="height: 160px; overflow: hidden; position: relative;">
              <img src="https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&q=80" class="w-100 h-100 object-fit-cover" alt="Beach Resort">
              <span class="badge position-absolute top-0 start-0 m-2 bg-primary">Beach Resorts</span>
            </div>
            <div class="card-body">
              <h5 class="fw-bold getaway-card-title mb-1">Boracay &amp; Mactan Escapes</h5>
              <p class="small text-secondary mb-3">Ultra-luxury beachfront suites with private infinity pools and white sand access.</p>
              <a href="home.php?category=all&search=Shangri-La" class="btn btn-sm btn-outline-primary rounded-pill px-3">Explore Beach Stays</a>
            </div>
          </div>
        </div>

        <!-- Card 2: City Luxury -->
        <div class="col-12 col-md-4">
          <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100" style="background: var(--bg-card); border: 1px solid var(--border)!important;">
            <div style="height: 160px; overflow: hidden; position: relative;">
              <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80" class="w-100 h-100 object-fit-cover" alt="City Casino">
              <span class="badge position-absolute top-0 start-0 m-2 bg-warning text-dark">Entertainment City</span>
            </div>
            <div class="card-body">
              <h5 class="fw-bold getaway-card-title mb-1">Entertainment City Grand Resorts</h5>
              <p class="small text-secondary mb-3">Okada Manila, Solaire, and City of Dreams casino suites with integrated dining.</p>
              <a href="home.php?category=all&search=Solaire" class="btn btn-sm btn-outline-warning rounded-pill px-3 text-warning">Explore Casinos</a>
            </div>
          </div>
        </div>

        <!-- Card 3: Transit Express -->
        <div class="col-12 col-md-4">
          <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100" style="background: var(--bg-card); border: 1px solid var(--border)!important;">
            <div style="height: 160px; overflow: hidden; position: relative;">
              <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80" class="w-100 h-100 object-fit-cover" alt="Express Motel">
              <span class="badge position-absolute top-0 start-0 m-2 bg-info">Transit &amp; Express</span>
            </div>
            <div class="card-body">
              <h5 class="fw-bold getaway-card-title mb-1">Walk-in Express &amp; Commuter Hubs</h5>
              <p class="small text-secondary mb-3">Hourly wash &amp; layover rates near MRT/LRT stations, EDSA, and airport terminals.</p>
              <a href="home.php?category=motel" class="btn btn-sm btn-outline-info rounded-pill px-3">Explore Express</a>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>

  <!-- Theme Synchronization Script (Syncs 100% with home.php and other pages) -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const themeToggleBtn = document.getElementById('themeToggleBtn');
      const icon = themeToggleBtn ? themeToggleBtn.querySelector('i') : null;
      const label = document.getElementById('themeToggleLabel');

      function updateThemeUI(theme) {
        if (theme === 'dark') {
          if (icon) icon.className = 'bi bi-sun-fill text-warning';
          if (label) label.textContent = 'Dark';
        } else {
          if (icon) icon.className = 'bi bi-moon-stars text-primary';
          if (label) label.textContent = 'Light';
        }
      }

      const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';
      updateThemeUI(activeTheme);

      if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
          let current = document.documentElement.getAttribute('data-theme') || 'dark';
          let nextTheme = current === 'dark' ? 'light' : 'dark';
          document.documentElement.setAttribute('data-theme', nextTheme);
          localStorage.setItem('stayfinder_theme', nextTheme);
          updateThemeUI(nextTheme);
        });
      }

      // Cross-tab / cross-window storage event listener to stay in real-time sync
      window.addEventListener('storage', (e) => {
        if (e.key === 'stayfinder_theme' && e.newValue) {
          document.documentElement.setAttribute('data-theme', e.newValue);
          updateThemeUI(e.newValue);
        }
      });
    });
  </script>

</body>
</html>
