<?php
// Hotel Reception & Retailer Side Management System
$userRole = $_SESSION['role'] ?? 'guest';
$isAdmin = ($userRole === 'admin') || (($_SESSION['user_email'] ?? '') === 'admin@comfortvue.ph');
$isReceptionist = ($userRole === 'receptionist') || $isAdmin || (!empty($_SESSION['hotel_id']));

if ($isAdmin && !empty($_GET['branch'])) {
    $assignedHotelId = trim($_GET['branch']);
    $_SESSION['hotel_id'] = $assignedHotelId;
} else {
    $assignedHotelId = $_SESSION['hotel_id'] ?? 'resort-shang-boracay';
}

$userName = $_SESSION['user_name'] ?? 'Front Desk Manager';
$userEmail = $_SESSION['user_email'] ?? 'reception@stayfinder.ph';
$autoWelcome = !empty($_GET['welcome']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Reception &amp; Retailer Portal - StayFinder PH</title>

  <!-- Immediate Theme Resolver (Syncs with guest portal & dashboard) -->
  <script>
    (function() {
      const savedTheme = localStorage.getItem('stayfinder_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>

  <!-- Google Fonts & Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Pannellum 360° Panorama Viewer -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
  <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>

  <!-- SweetAlert2 & Axios & Vue 3 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- System Stylesheet -->
  <link rel="stylesheet" href="css/styles.css?v=2.5">

  <style>
    /* Executive Reception Theme & Ambient Lighting */
    :root[data-theme="dark"] body.reception-body {
      background: #090D16;
      background-image: 
        radial-gradient(circle at 90% 10%, rgba(200, 113, 74, 0.12) 0%, transparent 45%),
        radial-gradient(circle at 10% 70%, rgba(56, 189, 248, 0.08) 0%, transparent 45%),
        radial-gradient(circle at 50% 50%, rgba(107, 143, 113, 0.04) 0%, transparent 60%);
      background-attachment: fixed;
      color: #F8FAFC;
    }

    :root[data-theme="light"] body.reception-body {
      background: #F8F6F0;
      background-image: 
        radial-gradient(circle at 90% 10%, rgba(200, 113, 74, 0.08) 0%, transparent 45%),
        radial-gradient(circle at 10% 80%, rgba(107, 143, 113, 0.08) 0%, transparent 45%);
      background-attachment: fixed;
      color: #1E293B;
    }

    body.reception-body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      min-height: 100vh;
      overflow-x: hidden;
      transition: background 0.3s ease, color 0.3s ease;
    }

    /* Top Hotel Identity Banner */
    .branch-identity-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      padding: 1.25rem 1.5rem;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
      transition: all 0.25s ease;
    }

    /* Live Notification Banner */
    .notification-alert-bar {
      background: linear-gradient(135deg, rgba(224, 86, 36, 0.95), rgba(186, 68, 28, 0.95));
      color: #fff;
      border-radius: 14px;
      padding: 0.9rem 1.25rem;
      box-shadow: 0 10px 28px rgba(224, 86, 36, 0.35);
      animation: alertPulse 2s infinite ease-in-out;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    @keyframes alertPulse {
      0%, 100% { transform: translateY(0); box-shadow: 0 10px 28px rgba(224, 86, 36, 0.35); }
      50% { transform: translateY(-2px); box-shadow: 0 14px 34px rgba(224, 86, 36, 0.5); }
    }

    /* Stat Cards */
    .stat-counter-box {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: 1.1rem 1.25rem;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .stat-counter-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      border-color: rgba(200, 113, 74, 0.4);
    }

    /* Tab Navigation Pills */
    .nav-reception-tab {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      border-radius: 50px;
      font-size: 0.88rem;
      font-weight: 700;
      color: var(--text-secondary);
      background: var(--bg-card);
      border: 1.5px solid var(--border);
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
      white-space: nowrap;
    }
    .nav-reception-tab:hover {
      color: var(--terracotta);
      border-color: var(--terracotta);
      background: rgba(200, 113, 74, 0.06);
    }
    .nav-reception-tab.active {
      color: #fff !important;
      background: var(--terracotta) !important;
      border-color: var(--terracotta) !important;
      box-shadow: 0 4px 14px rgba(200, 113, 74, 0.35);
    }

    /* Room Card in Reception */
    .reception-room-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      overflow: hidden;
      transition: all 0.25s ease;
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    .reception-room-card:hover {
      border-color: var(--terracotta);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    .reception-room-thumb {
      height: 165px;
      width: 100%;
      object-fit: cover;
      background: var(--cream-deep);
    }

    /* 360 Upload Dropzone */
    .upload-dropzone {
      border: 2px dashed var(--terracotta);
      background: rgba(200, 113, 74, 0.04);
      border-radius: 16px;
      padding: 2.5rem 1.5rem;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .upload-dropzone:hover, .upload-dropzone.dragover {
      background: rgba(200, 113, 74, 0.1);
      border-color: #f2a882;
      transform: scale(1.01);
    }

    /* Booking Request Row */
    .booking-row-item {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: 1.1rem 1.25rem;
      margin-bottom: 0.85rem;
      transition: all 0.2s ease;
    }
    .booking-row-item.unread-new {
      border-left: 5px solid var(--terracotta) !important;
      background: rgba(200, 113, 74, 0.04);
    }
    .booking-row-item:hover {
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      border-color: rgba(200, 113, 74, 0.35);
    }

    /* Tutorial Step Card */
    .tutorial-step-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      padding: 1.75rem;
      margin-bottom: 1.5rem;
      position: relative;
      transition: all 0.25s ease;
    }
    .tutorial-step-card:hover {
      border-color: var(--terracotta);
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.1);
    }
    .step-number-badge {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--terracotta), var(--amber));
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.25rem;
      box-shadow: 0 4px 12px rgba(200, 113, 74, 0.35);
      flex-shrink: 0;
    }

    /* Capacity Progress Bar */
    .capacity-progress {
      height: 8px;
      border-radius: 10px;
      background: rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }
    :root[data-theme="dark"] .capacity-progress {
      background: rgba(255, 255, 255, 0.1);
    }

    /* Reception High Contrast Light & Dark Mode Typographic Polish */
    :root[data-theme="light"] .branch-identity-card h4,
    :root[data-theme="light"] .stat-counter-box .display-6,
    :root[data-theme="light"] .reception-room-card h6,
    :root[data-theme="light"] .booking-row-item h6,
    :root[data-theme="light"] .booking-row-item .text-white,
    :root[data-theme="light"] .tutorial-step-card h5,
    :root[data-theme="light"] .tutorial-step-card strong.text-white,
    :root[data-theme="light"] .tutorial-step-card .fw-bold.text-white,
    :root[data-theme="light"] .modal-title,
    :root[data-theme="light"] .modal-body .fw-bold.text-white,
    :root[data-theme="light"] .modal-body h5.fw-bold.text-white,
    :root[data-theme="light"] .modal-body h6.fw-bold.text-white {
      color: #0F172A !important;
    }

    :root[data-theme="dark"] .branch-identity-card h4,
    :root[data-theme="dark"] .stat-counter-box .display-6,
    :root[data-theme="dark"] .reception-room-card h6,
    :root[data-theme="dark"] .booking-row-item h6,
    :root[data-theme="dark"] .booking-row-item .text-white,
    :root[data-theme="dark"] .tutorial-step-card h5,
    :root[data-theme="dark"] .tutorial-step-card strong.text-white,
    :root[data-theme="dark"] .tutorial-step-card .fw-bold.text-white,
    :root[data-theme="dark"] .modal-title,
    :root[data-theme="dark"] .modal-body .fw-bold.text-white,
    :root[data-theme="dark"] .modal-body h5.fw-bold.text-white,
    :root[data-theme="dark"] .modal-body h6.fw-bold.text-white {
      color: #F8FAFC !important;
    }

    /* Notification Alert Bar text must always be crisp pure white */
    .notification-alert-bar,
    .notification-alert-bar *,
    .notification-alert-bar strong,
    .step-number-badge,
    .step-number-badge * {
      color: #FFFFFF !important;
    }
    .notification-alert-bar .btn-light {
      background: #FFFFFF !important;
      color: #D95F5F !important;
    }

    /* Reception Onboarding Banner */
    .reception-onboarding-banner {
      background: linear-gradient(135deg, rgba(224, 86, 36, 0.20), rgba(15, 23, 42, 0.95)) !important;
      border: 1px solid rgba(224, 86, 36, 0.45) !important;
      color: #F8FAFC !important;
    }
    .reception-onboarding-banner .banner-title {
      color: #FFFFFF !important;
    }
    .reception-onboarding-banner .branch-name-badge {
      background: rgba(255, 255, 255, 0.15) !important;
      color: #FFFFFF !important;
    }
    .reception-onboarding-banner .onboarding-step-box {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .reception-onboarding-banner .onboarding-step-box:hover {
      background: rgba(255, 255, 255, 0.12);
      transform: translateY(-2px);
    }
    .reception-onboarding-banner .step-title {
      color: #FFFFFF !important;
    }
    .reception-onboarding-banner .step-desc {
      color: #CBD5E1 !important;
    }
    .reception-onboarding-banner .banner-dismiss-btn {
      color: #E2E8F0 !important;
      border-color: rgba(255, 255, 255, 0.35) !important;
    }
    .reception-onboarding-banner .banner-dismiss-btn:hover {
      background: rgba(255, 255, 255, 0.15) !important;
      color: #FFFFFF !important;
    }

    :root[data-theme="light"] .reception-onboarding-banner {
      background: linear-gradient(135deg, rgba(224, 86, 36, 0.10), rgba(244, 239, 228, 0.98)) !important;
      border: 1px solid rgba(224, 86, 36, 0.35) !important;
      color: #1E293B !important;
    }
    :root[data-theme="light"] .reception-onboarding-banner .banner-title {
      color: #0F172A !important;
    }
    :root[data-theme="light"] .reception-onboarding-banner .branch-name-badge {
      background: rgba(200, 113, 74, 0.15) !important;
      color: var(--terracotta) !important;
      border: 1px solid rgba(200, 113, 74, 0.3) !important;
    }
    :root[data-theme="light"] .reception-onboarding-banner .onboarding-step-box {
      background: #FFFFFF !important;
      border: 1px solid var(--border) !important;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    :root[data-theme="light"] .reception-onboarding-banner .onboarding-step-box:hover {
      background: #FFFFFF !important;
      border-color: var(--terracotta) !important;
      box-shadow: 0 4px 14px rgba(200, 113, 74, 0.15);
    }
    :root[data-theme="light"] .reception-onboarding-banner .step-title {
      color: #0F172A !important;
    }
    :root[data-theme="light"] .reception-onboarding-banner .step-desc {
      color: #64748B !important;
    }
    :root[data-theme="light"] .reception-onboarding-banner .banner-dismiss-btn {
      color: var(--text-secondary) !important;
      border-color: var(--border) !important;
    }
    :root[data-theme="light"] .reception-onboarding-banner .banner-dismiss-btn:hover {
      background: rgba(200, 113, 74, 0.1) !important;
      color: var(--terracotta) !important;
    }

    /* Wizard Info Cards */
    .wizard-info-card {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 12px;
      padding: 1rem;
    }
    :root[data-theme="light"] .wizard-info-card {
      background: var(--cream-deep) !important;
      border: 1px solid var(--border) !important;
    }

    /* Image overlay badges must always have white text */
    .reception-room-card .badge.bg-dark,
    .reception-room-card .badge.bg-dark * {
      color: #FFFFFF !important;
    }
  </style>
</head>
<body class="reception-body">

  <div id="receptionApp" v-cloak>

    <!-- Top Reception Navbar -->
    <header class="app-header">
      <div class="container-fluid px-3 px-md-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          
          <!-- Brand Logo & Front Desk Title -->
          <div class="d-flex align-items-center gap-2">
            <a href="dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none">
              <div class="brand-logo-badge">
                <i class="bi bi-building-gear"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2">
                  <span class="fs-5 fw-bold tracking-tight text-white">StayFinder Reception</span>
                  <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.68rem;">
                    <i class="bi bi-shield-lock-fill me-1"></i>Retailer Portal
                  </span>
                </div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">Branch Front Desk &bull; 360&deg; Panorama Studio &bull; Bookings Dispatch</div>
              </div>
            </a>
          </div>

          <!-- Quick Branch Switcher Dropdown & 1-Click Demo Pills -->
          <div class="d-flex align-items-center gap-2 flex-wrap">
            
            <!-- If Admin: Show Branch Switcher -->
            <template v-if="isAdmin">
              <!-- Quick Demo Branch Buttons -->
              <div class="d-none d-xl-flex align-items-center gap-1">
                <span class="small text-secondary fw-semibold me-1" style="font-size: 0.75rem;">Quick Demo:</span>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" :class="{ 'btn-primary text-white': activeHotelId === 'resort-shang-boracay' }" @click="switchBranch('resort-shang-boracay')">
                  🏝️ Shangri-La Boracay
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" :class="{ 'btn-primary text-white': activeHotelId === 'sogo-alabang-south' }" @click="switchBranch('sogo-alabang-south')">
                  🏨 Sogo Alabang
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" :class="{ 'btn-primary text-white': activeHotelId === 'hotel-okada' }" @click="switchBranch('hotel-okada')">
                  🎰 Okada Manila
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" :class="{ 'btn-primary text-white': activeHotelId === 'hotel-peninsula' }" @click="switchBranch('hotel-peninsula')">
                  🏛️ Peninsula Makati
                </button>
              </div>

              <!-- All 55+ Branches Dropdown -->
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm" type="button" data-bs-toggle="dropdown">
                  <i class="bi bi-arrow-left-right text-info"></i>
                  <span class="text-truncate" style="max-width: 170px;">{{ currentHotel?.title || 'Switch Branch' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border border-secondary border-opacity-50" style="max-height: 380px; overflow-y: auto; min-width: 280px;">
                  <li><h6 class="dropdown-header text-uppercase text-secondary" style="font-size: 0.68rem;">Select Hotel Branch / Retailer ({{ allBranches.length }})</h6></li>
                  <li v-for="b in allBranches" :key="b.id">
                    <button type="button" class="dropdown-item d-flex align-items-center justify-content-between py-2" :class="{ active: b.id === activeHotelId }" @click="switchBranch(b.id)">
                      <div class="text-truncate me-2">
                        <div class="fw-bold small">{{ b.title }}</div>
                        <div class="text-secondary small" style="font-size: 0.7rem;"><i class="bi bi-geo-alt me-1"></i>{{ b.city }} ({{ b.branch_name }})</div>
                      </div>
                      <span v-if="b.id === activeHotelId" class="badge bg-success rounded-pill">Active</span>
                    </button>
                  </li>
                </ul>
              </div>
            </template>

            <!-- If Dedicated Branch Staff: Show Locked Active Branch Badge -->
            <template v-else>
              <div class="d-flex align-items-center gap-1">
                <span class="badge bg-secondary bg-opacity-25 text-white border border-secondary border-opacity-50 px-3 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                  <i class="bi bi-shield-lock-fill text-warning"></i>
                  <span class="fw-bold text-truncate" style="max-width: 220px;">{{ currentHotel?.title || 'Branch Front Desk' }}</span>
                </span>
              </div>
            </template>

            <!-- "Get Started" Wizard Button -->
            <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 py-1 fw-bold text-dark shadow-sm d-inline-flex align-items-center gap-1" @click="openGetStartedModal" title="Open 3-step setup tutorial">
              <i class="bi bi-stars"></i> Get Started
            </button>

            <!-- Bell Sound Notification Toggle -->
            <button type="button" class="btn-nav-pill px-3" :class="{ 'border-warning text-warning': soundEnabled }" @click="toggleSound" :title="soundEnabled ? 'Desk bell notification sound enabled' : 'Click to enable desk bell audio'">
              <i class="bi" :class="soundEnabled ? 'bi-bell-fill text-warning' : 'bi-bell-slash text-secondary'"></i>
              <span class="d-none d-md-inline ms-1 small">{{ soundEnabled ? 'Bell ON' : 'Bell OFF' }}</span>
            </button>

            <!-- Test Bell Sound Button -->
            <button type="button" class="btn btn-xs btn-outline-warning rounded-circle p-1" @click="playDeskBell" title="Play test hotel desk bell chime">
              <i class="bi bi-volume-up-fill"></i>
            </button>

            <!-- Dark / Light Theme Toggle -->
            <button type="button" class="btn-nav-pill px-3" id="themeToggleBtn" @click="toggleTheme" title="Toggle Dark/Light Mode">
              <i class="bi" :class="currentTheme === 'dark' ? 'bi-sun-fill text-warning' : 'bi-moon-stars text-primary'"></i>
            </button>

            <!-- Jump to Guest Portal -->
            <a :href="'home.php?locate=' + encodeURIComponent(activeHotelId)" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-semibold d-none d-sm-inline-flex align-items-center gap-1" title="View live room cards & 360 viewer as travelers see them">
              <i class="bi bi-box-arrow-up-right"></i> Guest View
            </a>

            <!-- User Menu -->
            <div class="dropdown">
              <button class="btn-nav-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-badge text-info"></i> {{ currentUserName }}
              </button>
              <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border border-secondary border-opacity-50">
                <li class="px-3 py-2 border-bottom border-secondary border-opacity-25">
                  <div class="fw-bold text-white small">{{ currentUserName }}</div>
                  <div class="text-secondary small">{{ currentUserEmail }}</div>
                  <span class="badge bg-primary bg-opacity-25 text-terracotta border border-primary border-opacity-30 mt-1" style="font-size:0.65rem;">
                    Branch Front Desk Staff
                  </span>
                </li>
                <li><a class="dropdown-item text-warning fw-semibold" href="javascript:void(0)" @click="openGetStartedModal"><i class="bi bi-stars me-2 text-warning"></i>Get Started Tutorial</a></li>
                <li><a class="dropdown-item text-light" href="javascript:void(0)" @click="selectedTab = 'tutorial'"><i class="bi bi-journal-bookmark me-2"></i>Full Setup Guide</a></li>
                <li><a class="dropdown-item text-light" href="dashboard.php"><i class="bi bi-grid-fill me-2"></i>Traveler Dashboard</a></li>
                <li><a class="dropdown-item text-light" href="home.php"><i class="bi bi-map me-2"></i>Interactive Map</a></li>
                <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
                <li><a class="dropdown-item text-danger" href="reception_logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out Front Desk</a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
    </header>

    <!-- Main Container -->
    <main class="container-fluid px-3 px-md-4 py-4" style="max-width: 1480px;">

      <!-- High Priority New Booking Alert Banner -->
      <div v-if="stats.new_requests_count > 0 && !alertDismissed" class="notification-alert-bar mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-white text-danger d-inline-flex align-items-center justify-content-center shadow" style="width: 44px; height: 44px; font-size: 1.4rem;">
            <i class="bi bi-bell-fill"></i>
          </div>
          <div>
            <div class="fw-bold fs-6 lh-sm">
              🛎️ {{ stats.new_requests_count }} NEW ROOM BOOKING {{ stats.new_requests_count === 1 ? 'REQUEST' : 'REQUESTS' }} AWAITING REVIEW!
            </div>
            <div class="small opacity-90">
              <span v-if="latestBooking">
                Guest <strong>{{ latestBooking.guest_name }}</strong> just reserved <strong>{{ latestBooking.room_name }}</strong> (Downpayment: ₱{{ Number(latestBooking.downpayment_amount || 0).toLocaleString() }} &bull; Ref: {{ latestBooking.payment_ref }}).
              </span>
              <span v-else>
                Travelers have submitted room bookings for {{ currentHotel?.title }}.
              </span>
            </div>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-light rounded-pill px-3 py-1 fw-bold text-danger shadow-sm" @click="selectedTab = 'bookings'; activeBookingFilter = 'unread'; markAllAsRead();">
            <i class="bi bi-journal-check me-1"></i> Review Requests Now
          </button>
          <button type="button" class="btn btn-outline-light rounded-pill px-2 py-1" @click="alertDismissed = true" title="Dismiss notification banner">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>

      <!-- "Get Started" 3-Step Setup Onboarding Banner -->
      <div v-if="!onboardingDismissed" class="card border-0 rounded-4 p-3 p-md-4 mb-4 shadow reception-onboarding-banner">
        <div class="row align-items-center g-3">
          <div class="col-12 col-xl-8">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
              <span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="bi bi-stars me-1"></i>GET STARTED TUTORIAL</span>
              <span class="badge bg-white bg-opacity-15 text-white">{{ currentHotel?.title }} Front Desk</span>
              <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-30">Step-by-Step Setup</span>
            </div>
            <h4 class="fw-bold text-white mb-2">Welcome to Your Front Desk! Get Started in 3 Steps:</h4>
            <div class="row g-2 mb-2">
              <div class="col-12 col-md-4">
                <div class="p-2 rounded-3 h-100 cursor-pointer" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); cursor: pointer;" @click="openWizardStep(1)">
                  <div class="fw-bold text-white small mb-1"><i class="bi bi-1-circle-fill text-warning me-1"></i> Compose Rooms</div>
                  <div class="text-secondary small" style="font-size:0.75rem;">Set suite names, numbers, rates &amp; amenities</div>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-2 rounded-3 h-100 cursor-pointer" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); cursor: pointer;" @click="openWizardStep(2)">
                  <div class="fw-bold text-white small mb-1"><i class="bi bi-2-circle-fill text-info me-1"></i> Upload 360° Panorama</div>
                  <div class="text-secondary small" style="font-size:0.75rem;">Upload equirectangular photos per room</div>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-2 rounded-3 h-100 cursor-pointer" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); cursor: pointer;" @click="openWizardStep(3)">
                  <div class="fw-bold text-white small mb-1"><i class="bi bi-3-circle-fill text-success me-1"></i> Set Booking Limits</div>
                  <div class="text-secondary small" style="font-size:0.75rem;">Prevent overbooking with auto "Sold Out" limits</div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-xl-4 text-xl-end d-flex flex-wrap gap-2 justify-content-xl-end">
            <button type="button" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow" @click="openGetStartedModal">
              <i class="bi bi-play-circle-fill me-1"></i> Launch "Get Started" Wizard
            </button>
            <button type="button" class="btn btn-outline-light rounded-pill px-3 py-2 small" @click="dismissOnboardingBanner">
              Dismiss
            </button>
          </div>
        </div>
      </div>

      <!-- Hotel Branch Identity & Hero Bar -->
      <div class="branch-identity-card mb-4">
        <div class="row align-items-center g-3">
          <div class="col-12 col-lg-8">
            <div class="d-flex align-items-center gap-3 flex-wrap">
              <img :src="currentHotel?.image || 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=400&q=80'" :alt="currentHotel?.title" class="rounded-3 shadow-sm object-fit-cover" style="width: 76px; height: 76px; border: 2px solid var(--border);">
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                  <span class="badge bg-primary px-2 py-1" style="font-size:0.75rem;">
                    <i class="bi bi-award-fill me-1"></i>{{ currentHotel?.category_display || 'Luxury Accommodation' }}
                  </span>
                  <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-30" style="font-size:0.72rem;">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ currentHotel?.branch_name || 'Flagship Branch' }}
                  </span>
                  <span class="text-warning small fw-bold"><i class="bi bi-star-fill"></i> {{ currentHotel?.rating }} ({{ currentHotel?.rating_count || 150 }} Reviews)</span>
                </div>
                <h4 class="fw-bold text-white mb-0 lh-sm">{{ currentHotel?.title }}</h4>
                <div class="text-secondary small mt-1">
                  <i class="bi bi-pin-map-fill text-danger me-1"></i>{{ currentHotel?.address }} &bull; {{ currentHotel?.city }}
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-4 text-lg-end">
            <div class="d-flex align-items-center justify-content-lg-end gap-2 flex-wrap">
              <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-2 fw-semibold" @click="selectedTab = 'studio360'">
                <i class="bi bi-camera-reels-fill me-1"></i> 360° Panorama Studio
              </button>
              <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow" @click="openAddRoomModal">
                <i class="bi bi-plus-circle me-1"></i> Add Room Suite
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Executive Metric Stats Cards -->
      <div class="row g-3 mb-4">
        
        <!-- Metric 1: New Booking Requests -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="stat-counter-box" :style="stats.new_requests_count > 0 ? 'border-color: rgba(224,86,36,0.5); background: rgba(224,86,36,0.04);' : ''">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="text-secondary small fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">New Booking Requests</span>
              <span class="badge rounded-circle p-2" :class="stats.new_requests_count > 0 ? 'bg-danger text-white' : 'bg-secondary bg-opacity-25 text-secondary'">
                <i class="bi bi-bell-fill fs-6"></i>
              </span>
            </div>
            <div>
              <div class="d-flex align-items-baseline gap-2">
                <span class="display-6 fw-bold text-white">{{ stats.new_requests_count }}</span>
                <span v-if="stats.new_requests_count > 0" class="badge bg-danger rounded-pill px-2 py-1" style="font-size:0.68rem;">Action Required</span>
                <span v-else class="text-success small fw-semibold"><i class="bi bi-check-circle me-1"></i>All caught up</span>
              </div>
              <div class="small text-secondary mt-1">Pending approval &amp; verification</div>
            </div>
          </div>
        </div>

        <!-- Metric 2: Active Confirmed Bookings -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="stat-counter-box">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="text-secondary small fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Active &amp; Checked-in Stays</span>
              <span class="badge bg-primary bg-opacity-15 text-terracotta rounded-circle p-2">
                <i class="bi bi-calendar-check-fill fs-6"></i>
              </span>
            </div>
            <div>
              <div class="d-flex align-items-baseline gap-2">
                <span class="display-6 fw-bold text-white">{{ stats.active_stays_count }}</span>
                <span class="text-info small fw-semibold">Reservations</span>
              </div>
              <div class="small text-secondary mt-1">Confirmed guest occupants</div>
            </div>
          </div>
        </div>

        <!-- Metric 3: Total Rooms & Capacity Limit -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="stat-counter-box">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="text-secondary small fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Available Suites / Capacity</span>
              <span class="badge bg-success bg-opacity-15 text-success rounded-circle p-2">
                <i class="bi bi-door-open-fill fs-6"></i>
              </span>
            </div>
            <div>
              <div class="d-flex align-items-baseline gap-2">
                <span class="display-6 fw-bold text-white">{{ stats.available_rooms }} <span class="fs-5 text-secondary fw-normal">/ {{ stats.total_rooms }}</span></span>
                <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2 py-1" style="font-size:0.68rem;">Open</span>
              </div>
              <div class="small text-secondary mt-1">Controlled by booking limits</div>
            </div>
          </div>
        </div>

        <!-- Metric 4: Today's Verified Downpayments -->
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="stat-counter-box">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="text-secondary small fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Downpayments Collected</span>
              <span class="badge bg-warning bg-opacity-15 text-warning rounded-circle p-2">
                <i class="bi bi-cash-stack fs-6"></i>
              </span>
            </div>
            <div>
              <div class="d-flex align-items-baseline gap-2">
                <span class="display-6 fw-bold text-success">₱{{ Number(stats.today_downpayment || 0).toLocaleString() }}</span>
              </div>
              <div class="small text-secondary mt-1">Verified via GCash &bull; Maya &bull; Cards</div>
            </div>
          </div>
        </div>

      </div>

      <!-- Navigation Tabs Pill Bar -->
      <div class="d-flex align-items-center gap-2 mb-4 overflow-x-auto pb-2" style="scrollbar-width: thin;">
        <button type="button" class="nav-reception-tab" :class="{ active: selectedTab === 'bookings' }" @click="selectedTab = 'bookings'">
          <i class="bi bi-inbox-fill"></i> Booking Requests &amp; Front Desk
          <span v-if="stats.new_requests_count > 0" class="badge bg-danger rounded-pill ms-1">{{ stats.new_requests_count }} New</span>
        </button>
        <button type="button" class="nav-reception-tab" :class="{ active: selectedTab === 'rooms' }" @click="selectedTab = 'rooms'">
          <i class="bi bi-door-open-fill"></i> Room Inventory &amp; Booking Limits ({{ rooms.length }})
        </button>
        <button type="button" class="nav-reception-tab" :class="{ active: selectedTab === 'studio360' }" @click="selectedTab = 'studio360'">
          <i class="bi bi-camera-reels-fill"></i> 360&deg; Panorama Studio &amp; Uploads
        </button>
        <button type="button" class="nav-reception-tab" :class="{ active: selectedTab === 'tutorial' }" @click="selectedTab = 'tutorial'">
          <i class="bi bi-journal-bookmark-fill text-warning"></i> Step-by-Step Onboarding Tutorial
        </button>
      </div>

      <!-- ======================================================== -->
      <!-- TAB 1: BOOKING REQUESTS & FRONT DESK FEED                -->
      <!-- ======================================================== -->
      <section v-if="selectedTab === 'bookings'">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          
          <!-- Filter Buttons -->
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm rounded-pill px-3" :class="activeBookingFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary'" @click="activeBookingFilter = 'all'">
              All Bookings ({{ bookings.length }})
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3" :class="activeBookingFilter === 'unread' ? 'btn-danger' : 'btn-outline-secondary'" @click="activeBookingFilter = 'unread'">
              <i class="bi bi-bell-fill me-1"></i> New Requests ({{ stats.new_requests_count }})
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3" :class="activeBookingFilter === 'confirmed' ? 'btn-success' : 'btn-outline-secondary'" @click="activeBookingFilter = 'confirmed'">
              Confirmed Stays
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3" :class="activeBookingFilter === 'checked_in' ? 'btn-info text-dark' : 'btn-outline-secondary'" @click="activeBookingFilter = 'checked_in'">
              Checked-In Guests
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3" :class="activeBookingFilter === 'history' ? 'btn-secondary' : 'btn-outline-secondary'" @click="activeBookingFilter = 'history'">
              Checked-Out / Cancelled
            </button>
          </div>

          <!-- Search Input -->
          <div class="position-relative" style="min-width: 260px;">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-secondary ms-3"></i>
            <input type="text" v-model="searchBookingQuery" class="form-control form-control-sm rounded-pill ps-5" placeholder="Search guest name, ref #, room...">
          </div>
        </div>

        <!-- Bookings Feed List -->
        <div v-if="filteredBookings.length > 0" class="d-flex flex-column">
          <div v-for="b in filteredBookings" :key="b.booking_id" class="booking-row-item" :class="{ 'unread-new': b.is_unread }">
            <div class="row align-items-center g-3">
              
              <!-- Guest & Booking Info -->
              <div class="col-12 col-md-4">
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                  <span class="badge rounded-pill" :class="getBookingStatusBadge(b.status)">{{ b.status }}</span>
                  <span v-if="b.is_unread" class="badge bg-danger rounded-pill px-2 py-0" style="font-size:0.65rem;">NEW REQUEST</span>
                  <span class="small font-monospace text-secondary">{{ b.booking_id }}</span>
                </div>
                <h6 class="fw-bold text-white mb-0 lh-sm fs-6">{{ b.guest_name }}</h6>
                <div class="small text-secondary mt-1">
                  <i class="bi bi-telephone-fill me-1 text-info"></i>{{ b.guest_phone || 'No phone' }} &bull;
                  <i class="bi bi-envelope-fill ms-1 me-1 text-warning"></i>{{ b.guest_email }}
                </div>
              </div>

              <!-- Room & Schedule Details -->
              <div class="col-12 col-md-3">
                <div class="small fw-bold text-white">{{ b.room_name }} <span v-if="b.room_number" class="badge bg-secondary bg-opacity-25 ms-1">({{ b.room_number }})</span></div>
                <div class="small text-secondary mt-1">
                  <i class="bi bi-calendar-event me-1 text-primary"></i>Check-in: {{ formatDateTime(b.check_in_time) }}
                </div>
                <div class="small text-secondary">
                  <i class="bi bi-clock-history me-1 text-info"></i>Duration: {{ b.stay_type || 'Overnight Stay' }} ({{ b.nights || 1 }} night)
                </div>
              </div>

              <!-- Payment & Downpayment Info -->
              <div class="col-12 col-md-3">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30" style="font-size:0.7rem;">
                    <i class="bi bi-shield-check me-1"></i>Deposit Paid: ₱{{ Number(b.downpayment_amount || 0).toLocaleString() }}
                  </span>
                  <span class="badge bg-secondary bg-opacity-25 text-light" style="font-size:0.68rem;">{{ b.payment_method }}</span>
                </div>
                <div class="small text-white mt-1">
                  Total Rate: <strong>₱{{ Number(b.amount || 0).toLocaleString() }}</strong>
                  <span v-if="Number(b.balance_due) > 0" class="text-warning small d-block">
                    Balance at Check-in: <strong>₱{{ Number(b.balance_due).toLocaleString() }}</strong>
                  </span>
                </div>
                <div v-if="b.payment_ref" class="text-secondary small font-monospace" style="font-size:0.68rem;">
                  Ref: {{ b.payment_ref }}
                </div>
              </div>

              <!-- Front Desk Action Buttons -->
              <div class="col-12 col-md-2 text-md-end">
                <div class="d-flex align-items-center justify-content-md-end gap-1 flex-wrap">
                  
                  <!-- If New / Pending: Confirm button -->
                  <button v-if="b.status === 'PENDING' || b.is_unread" type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-sm" @click="updateBookingStatus(b.booking_id, 'CONFIRMED')" title="Confirm reservation">
                    <i class="bi bi-check-lg"></i> Confirm
                  </button>

                  <!-- If Confirmed: Check In button -->
                  <button v-if="b.status === 'CONFIRMED'" type="button" class="btn btn-sm btn-info text-dark rounded-pill px-3 py-1 fw-bold shadow-sm" @click="updateBookingStatus(b.booking_id, 'CHECKED_IN')" title="Check guest in">
                    <i class="bi bi-box-arrow-in-right"></i> Check In
                  </button>

                  <!-- If Checked In: Check Out button -->
                  <button v-if="b.status === 'CHECKED_IN'" type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-bold" @click="updateBookingStatus(b.booking_id, 'CHECKED_OUT')" title="Check guest out and release room slot">
                    <i class="bi bi-box-arrow-right"></i> Check Out
                  </button>

                  <!-- View Printable Guest Folio -->
                  <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-1" @click="openGuestFolio(b)" title="Print / View Guest Reservation Slip">
                    <i class="bi bi-printer"></i>
                  </button>

                  <!-- Cancel / Decline -->
                  <button v-if="b.status !== 'CANCELLED' && b.status !== 'CHECKED_OUT'" type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1" @click="promptCancelBooking(b)" title="Cancel / Decline Booking">
                    <i class="bi bi-x-lg"></i>
                  </button>

                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-5 branch-identity-card">
          <i class="bi bi-inbox text-secondary display-3 opacity-50 mb-3 d-block"></i>
          <h5 class="fw-bold text-white mb-1">No Bookings Found</h5>
          <p class="text-secondary small mb-3">There are no reservation requests matching your selected filter.</p>
          <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4" @click="activeBookingFilter = 'all'; searchBookingQuery = '';">
            Reset Filters
          </button>
        </div>
      </section>

      <!-- ======================================================== -->
      <!-- TAB 2: ROOM MANAGEMENT & BOOKING LIMITS                  -->
      <!-- ======================================================== -->
      <section v-if="selectedTab === 'rooms'">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <div>
            <h5 class="fw-bold text-white mb-0">Suites &amp; Room Inventory</h5>
            <div class="small text-secondary">Manage room status, pricing, booking limits, and 360&deg; panoramas.</div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow" @click="openAddRoomModal">
              <i class="bi bi-plus-lg me-1"></i> Add New Suite / Room
            </button>
          </div>
        </div>

        <div class="row g-3">
          <div v-for="rm in rooms" :key="rm.id" class="col-12 col-md-6 col-xl-4">
            <div class="reception-room-card">
              
              <!-- Room Image with 360 Badge -->
              <div class="position-relative">
                <img :src="rm.image" :alt="rm.room_name" class="reception-room-thumb">
                
                <!-- Room Number Badge -->
                <span class="badge position-absolute top-0 start-0 m-2 bg-dark bg-opacity-75 text-white" style="font-size:0.75rem;">
                  <i class="bi bi-door-closed-fill me-1 text-primary"></i>{{ rm.room_number }}
                </span>

                <!-- 360 Status Indicator -->
                <span v-if="rm.custom_360_url" class="badge position-absolute top-0 end-0 m-2 bg-success rounded-pill shadow-sm" style="font-size:0.7rem;">
                  <i class="bi bi-check-circle-fill me-1"></i>360° Photo Active
                </span>
                <span v-else class="badge position-absolute top-0 end-0 m-2 bg-dark bg-opacity-75 text-info rounded-pill" style="font-size:0.7rem;">
                  <i class="bi bi-camera me-1"></i>Sample 360° Active
                </span>

                <!-- Status Badge -->
                <span class="badge position-absolute bottom-0 start-0 m-2" :class="rm.status === 'Available' ? (rm.is_limit_reached ? 'bg-danger' : 'bg-success') : 'bg-warning text-dark'" style="font-size:0.72rem;">
                  {{ rm.is_limit_reached ? 'Sold Out (Limit Reached)' : rm.status }}
                </span>
              </div>

              <!-- Room Details Body -->
              <div class="p-3 d-flex flex-column flex-grow-1 justify-content-between">
                <div>
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="fw-bold text-white mb-0 fs-6">{{ rm.room_name }}</h6>
                    <span class="text-success fw-bold">₱{{ Number(rm.price).toLocaleString() }} <small class="text-secondary fw-normal">/ {{ rm.rate_type === 'hourly' ? rm.duration : 'night' }}</small></span>
                  </div>
                  <div class="small text-secondary mb-2">
                    <i class="bi bi-people me-1"></i>{{ rm.capacity }} &bull; <i class="bi bi-tag me-1"></i>{{ rm.room_type }} &bull; {{ rm.bed_type }}
                  </div>

                  <!-- Booking Limit & Capacity Bar -->
                  <div class="p-2 rounded-3 mb-3 border border-secondary border-opacity-25" style="background: rgba(0,0,0,0.06);">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                      <span class="text-secondary fw-bold" style="font-size:0.7rem; text-transform:uppercase;">
                        Booking Limit: <span class="text-white">{{ rm.booking_limit }} max</span>
                      </span>
                      <span class="fw-bold" :class="rm.is_limit_reached ? 'text-danger' : 'text-info'" style="font-size:0.72rem;">
                        {{ rm.total_booked }} / {{ rm.booking_limit }} Reserved
                      </span>
                    </div>
                    <div class="capacity-progress">
                      <div class="progress-bar" :class="rm.is_limit_reached ? 'bg-danger' : (rm.total_booked > 0 ? 'bg-warning' : 'bg-success')" :style="{ width: Math.min(100, Math.round((rm.total_booked / rm.booking_limit) * 100)) + '%' }"></div>
                    </div>
                    <div v-if="rm.is_limit_reached" class="text-danger small mt-1" style="font-size:0.68rem;">
                      <i class="bi bi-exclamation-triangle-fill me-1"></i>Capacity full. Marked as "Sold Out" on guest map.
                    </div>
                  </div>

                  <!-- Amenities summary -->
                  <div class="text-secondary small text-truncate mb-3" :title="rm.amenities" style="font-size:0.75rem;">
                    <i class="bi bi-stars text-warning me-1"></i>{{ rm.amenities }}
                  </div>
                </div>

                <!-- Action Toolbar -->
                <div class="pt-2 border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                  
                  <!-- 360 Actions -->
                  <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2 py-1 fw-bold" @click="open360UploadModal(rm)" title="Upload custom 360 room photo">
                      <i class="bi bi-camera-fill me-1"></i> Upload 360°
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" @click="previewRoom360(rm)" title="Preview interactive 360 virtual tour">
                      <i class="bi bi-eye"></i> View 360°
                    </button>
                  </div>

                  <!-- Edit / Limit Settings -->
                  <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-bold" @click="openEditRoomModal(rm)">
                    <i class="bi bi-sliders me-1"></i> Edit &amp; Limits
                  </button>

                </div>

              </div>

            </div>
          </div>
        </div>
      </section>

      <!-- ======================================================== -->
      <!-- TAB 3: 360° PANORAMA STUDIO & UPLOADER                   -->
      <!-- ======================================================== -->
      <section v-if="selectedTab === 'studio360'">
        <div class="branch-identity-card mb-4">
          <div class="row align-items-center g-3">
            <div class="col-12 col-lg-8">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-35 px-2 py-1">
                  <i class="bi bi-camera-reels-fill me-1"></i>Virtual Reality Studio
                </span>
                <span class="text-secondary small">Equirectangular 360° Room Engine</span>
              </div>
              <h4 class="fw-bold text-white mb-1">Interactive 360° Panorama Manager</h4>
              <p class="text-secondary small mb-0">
                Upload your own 360-degree equirectangular images for each suite. Travelers can explore your rooms with full gyro navigation before booking.
              </p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
              <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" @click="selectedTab = 'tutorial'">
                <i class="bi bi-lightbulb-fill text-warning me-1"></i> How to take 360° photos
              </button>
            </div>
          </div>
        </div>

        <div class="row g-4">
          
          <!-- Left Column: Upload Form & Room Selector -->
          <div class="col-12 col-lg-5">
            <div class="branch-identity-card h-100">
              <h6 class="fw-bold text-white mb-3"><i class="bi bi-upload text-primary me-2"></i>Upload Room 360° Photo</h6>
              
              <!-- Select Room Target -->
              <div class="mb-3">
                <label class="form-label small text-secondary fw-bold">Select Room / Accommodation Suite</label>
                <select v-model="studioSelectedRoomId" class="form-select bg-dark text-light border-secondary" @change="onStudioRoomSelect">
                  <option v-for="r in rooms" :key="r.id" :value="r.id">
                    {{ r.room_number ? r.room_number + ' - ' : '' }}{{ r.room_name }} ({{ r.room_type }})
                  </option>
                </select>
              </div>

              <!-- Upload Drag & Drop Zone -->
              <div class="upload-dropzone mb-3" @click="triggerFileInput" @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false" @drop.prevent="handleFileDrop" :class="{ dragover: dragOver }">
                <input type="file" ref="fileInputRef" class="d-none" accept=".jpg,.jpeg,.png,.webp" @change="handleFileSelected">
                <div class="display-5 text-primary mb-2"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                <div class="fw-bold text-white small mb-1">
                  {{ selectedUploadFile ? selectedUploadFile.name : 'Click to Browse or Drag & Drop 360° Image' }}
                </div>
                <div class="text-secondary small" style="font-size:0.75rem;">
                  Recommended: 2:1 Aspect Ratio (e.g. 4000x2000px), JPG/PNG up to 20MB
                </div>
              </div>

              <!-- Upload Button -->
              <div class="d-grid mb-3">
                <button type="button" class="btn btn-primary rounded-pill py-2 fw-bold shadow" :disabled="!selectedUploadFile || isUploading" @click="uploadSelectedPanorama">
                  <span v-if="isUploading" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-check-circle-fill me-1"></i>
                  {{ isUploading ? 'Uploading & Applying 360°...' : 'Upload & Set as Room 360° Tour' }}
                </button>
              </div>

              <!-- Fast Preset Sample Picker (Convenient Instant Testing) -->
              <div class="border-top border-secondary border-opacity-25 pt-3">
                <label class="form-label small text-secondary fw-bold mb-2">Or Choose from High-Resolution Tested Panoramas:</label>
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" @click="applyPresetSample('suite')">
                    🏨 Luxury Suite
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" @click="applyPresetSample('bedroom')">
                    🛏️ Master Bedroom
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" @click="applyPresetSample('bathroom')">
                    🛁 Marble Bathroom
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" @click="applyPresetSample('condo')">
                    🏙️ Skyline Terrace
                  </button>
                </div>
              </div>

            </div>
          </div>

          <!-- Right Column: Live Interactive Pannellum Previewer -->
          <div class="col-12 col-lg-7">
            <div class="branch-identity-card h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                  <h6 class="fw-bold text-white mb-0"><i class="bi bi-badge-vr text-info me-2"></i>Live 360° Interactive Simulator</h6>
                  <div class="small text-secondary">Drag and look around 360&deg; to test client experience.</div>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" @click="toggleStudioAutoRotate" title="Toggle Auto-Rotation">
                    <i class="bi" :class="studioRotating ? 'bi-pause-fill' : 'bi-play-fill'"></i> {{ studioRotating ? 'Pause' : 'Spin' }}
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" @click="resetStudioView" title="Reset View Angle">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                  </button>
                </div>
              </div>

              <!-- Pannellum Viewer Mount Container -->
              <div class="position-relative" style="height: 440px; border-radius: var(--r-md); overflow: hidden; border: 2px solid var(--border);">
                <div id="studioPannellumContainer" style="width: 100%; height: 100%;"></div>
                
                <!-- Loading Overlay -->
                <div v-if="studioLoading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-75" style="z-index: 10;">
                  <div class="spinner-border text-primary mb-2"></div>
                  <div class="text-white small fw-bold">Loading 360° Panorama...</div>
                </div>
              </div>

              <!-- Active Panorama Status Footer -->
              <div class="mt-2 d-flex align-items-center justify-content-between small text-secondary">
                <span>Active 360 Photo: <strong class="text-white">{{ currentStudioPanoTitle }}</strong></span>
                <span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-broadcast me-1"></i>Synced with Guest Portal</span>
              </div>

            </div>
          </div>

        </div>
      </section>

      <!-- ======================================================== -->
      <!-- TAB 4: STEP-BY-STEP ONBOARDING & SETUP TUTORIAL          -->
      <!-- ======================================================== -->
      <section v-if="selectedTab === 'tutorial'">
        
        <div class="branch-identity-card mb-4">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-35 px-2 py-1 mb-1">
                <i class="bi bi-journal-check me-1"></i>Official Retailer Guide
              </span>
              <h4 class="fw-bold text-white mb-1">Hotel Branch Reception Setup Tutorial</h4>
              <p class="text-secondary small mb-0">Follow these 5 easy steps to configure your branch, prevent overbooking, and showcase stunning 360° room tours.</p>
            </div>
            <div>
              <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow" @click="simulateDemoBooking">
                <i class="bi bi-play-circle-fill me-1"></i> Simulate Test Booking Request
              </button>
            </div>
          </div>
        </div>

        <!-- Step 1 -->
        <div class="tutorial-step-card">
          <div class="d-flex align-items-start gap-3">
            <div class="step-number-badge">1</div>
            <div class="flex-grow-1">
              <h5 class="fw-bold text-white mb-1">Set Up Your Front Desk Reception Account</h5>
              <p class="text-secondary small mb-3">
                Each hotel branch has a dedicated front desk login. Ensure your staff credentials and telephone contact details are up to date so travelers can reach your front desk immediately upon reservation.
              </p>
              <div class="p-3 rounded-3 border border-secondary border-opacity-25 mb-3" style="background: rgba(0,0,0,0.04);">
                <div class="row g-2 small">
                  <div class="col-12 col-md-4">
                    <span class="text-secondary d-block">Default Login Email:</span>
                    <strong class="text-white">reception.{{ activeHotelId.replace(/[^a-z0-9]/g, '') }}@stayfinder.ph</strong>
                  </div>
                  <div class="col-12 col-md-4">
                    <span class="text-secondary d-block">Default Access Password:</span>
                    <strong class="text-warning">reception123</strong>
                  </div>
                  <div class="col-12 col-md-4">
                    <span class="text-secondary d-block">Branch Assigned:</span>
                    <strong class="text-info">{{ currentHotel?.title }} ({{ currentHotel?.branch_name }})</strong>
                  </div>
                </div>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-3 py-1" @click="selectedTab = 'bookings'">
                  <i class="bi bi-check2-circle me-1"></i> Go to Front Desk View
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="tutorial-step-card">
          <div class="d-flex align-items-start gap-3">
            <div class="step-number-badge">2</div>
            <div class="flex-grow-1">
              <h5 class="fw-bold text-white mb-1">Manage Room Inventory &amp; Set Booking Limits</h5>
              <p class="text-secondary small mb-3">
                To prevent overbooking during high peak season or holidays, you can set a strict <strong>Booking Limit</strong> for each individual room category (e.g. Standard, Executive Suite, Penthouse).
              </p>
              <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100">
                    <div class="fw-bold text-white small mb-1"><i class="bi bi-sliders text-primary me-1"></i>How Booking Limits Work:</div>
                    <ul class="text-secondary small mb-0 ps-3">
                      <li>Set maximum concurrent bookings allowed (e.g., 5 reservations).</li>
                      <li>When active confirmed reservations reach the limit, the room is automatically flagged as <strong>"Sold Out"</strong> on the traveler map.</li>
                      <li>Checking out a guest instantly frees up capacity.</li>
                    </ul>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100">
                    <div class="fw-bold text-white small mb-1"><i class="bi bi-shield-check text-success me-1"></i>Anti-Spam Downpayment Integration:</div>
                    <ul class="text-secondary small mb-0 ps-3">
                      <li>Travelers must complete a 20% downpayment via GCash or Maya to confirm a slot.</li>
                      <li>This guarantees that only legitimate, committed guests take up your room limits.</li>
                    </ul>
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1" @click="selectedTab = 'rooms'">
                <i class="bi bi-door-open me-1"></i> Configure Room Booking Limits
              </button>
            </div>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="tutorial-step-card">
          <div class="d-flex align-items-start gap-3">
            <div class="step-number-badge">3</div>
            <div class="flex-grow-1">
              <h5 class="fw-bold text-white mb-1">Shooting &amp; Uploading 360° Virtual Tour Photos</h5>
              <p class="text-secondary small mb-3">
                Showcasing actual 360° views increases bookings by over 300%. You do <strong>not</strong> need expensive cameras—any standard smartphone can take equirectangular 360° photos!
              </p>
              <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 text-center">
                    <div class="fs-4 text-warning mb-1"><i class="bi bi-phone"></i></div>
                    <div class="fw-bold text-white small mb-1">A. Capture with Phone App</div>
                    <div class="text-secondary small" style="font-size:0.75rem;">
                      Use Google Street View, 360 Panorama, or Ricoh Theta / Insta360 app. Stand in center of room at 1.5m height.
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 text-center">
                    <div class="fs-4 text-info mb-1"><i class="bi bi-aspect-ratio"></i></div>
                    <div class="fw-bold text-white small mb-1">B. Save as Equirectangular (2:1)</div>
                    <div class="text-secondary small" style="font-size:0.75rem;">
                      Ensure photo has a 2:1 width-to-height ratio (e.g. 4000x2000px). Save as standard JPG or PNG format.
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 text-center">
                    <div class="fs-4 text-success mb-1"><i class="bi bi-cloud-arrow-up"></i></div>
                    <div class="fw-bold text-white small mb-1">C. Upload to Suite in 1 Click</div>
                    <div class="text-secondary small" style="font-size:0.75rem;">
                      Go to the 360° Studio tab, select room, drag and drop the photo. Pannellum live viewer will instantly render it!
                    </div>
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-3 py-1" @click="selectedTab = 'studio360'">
                <i class="bi bi-camera-reels me-1"></i> Open 360° Panorama Studio
              </button>
            </div>
          </div>
        </div>

        <!-- Step 4 -->
        <div class="tutorial-step-card">
          <div class="d-flex align-items-start gap-3">
            <div class="step-number-badge">4</div>
            <div class="flex-grow-1">
              <h5 class="fw-bold text-white mb-1">Receiving Real-Time Booking Alerts &amp; Desk Chimes</h5>
              <p class="text-secondary small mb-3">
                Whenever a traveler books a room on the main map, your portal automatically polls the cloud server every 6 seconds and sounds a high-quality hotel desk bell chime.
              </p>
              <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1 fw-bold" @click="playDeskBell">
                  <i class="bi bi-bell-fill me-1"></i> Test Desk Bell Chime Sound
                </button>
                <button type="button" class="btn btn-xs btn-primary rounded-pill px-3 py-1 fw-bold" @click="simulateDemoBooking">
                  <i class="bi bi-magic me-1"></i> Simulate Incoming Guest Booking
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 5 -->
        <div class="tutorial-step-card">
          <div class="d-flex align-items-start gap-3">
            <div class="step-number-badge">5</div>
            <div class="flex-grow-1">
              <h5 class="fw-bold text-white mb-1">Test the Live Traveler Experience</h5>
              <p class="text-secondary small mb-3">
                Verify everything from the traveler's perspective! Check how your hotel pins appear on the Leaflet satellite map, test room availability, and spin your 360° virtual tour.
              </p>
              <a :href="'home.php?locate=' + encodeURIComponent(activeHotelId)" target="_blank" class="btn btn-xs btn-outline-success rounded-pill px-3 py-2 fw-bold">
                <i class="bi bi-box-arrow-up-right me-1"></i> Open Live Guest Map Portal
              </a>
            </div>
          </div>
        </div>

      </section>

    </main>

    <!-- ======================================================== -->
    <!-- MODAL 1: UPLOAD 360° PANORAMA MODAL                      -->
    <!-- ======================================================== -->
    <div class="modal fade" id="upload360Modal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-dark border border-secondary shadow-lg">
          <div class="modal-header border-secondary border-opacity-25">
            <h6 class="modal-title fw-bold">
              <i class="bi bi-camera-reels-fill text-primary me-2"></i>Upload 360° Image for {{ modalRoom?.room_name }}
            </h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3 text-secondary small">
              Upload an equirectangular 360° image (2:1 aspect ratio) for <strong>{{ modalRoom?.room_number }} - {{ modalRoom?.room_name }}</strong>.
            </div>

            <!-- Drag & Drop Zone -->
            <div class="upload-dropzone mb-3" @click="triggerModalFileInput" @dragover.prevent="modalDragOver = true" @dragleave.prevent="modalDragOver = false" @drop.prevent="handleModalFileDrop" :class="{ dragover: modalDragOver }">
              <input type="file" ref="modalFileInputRef" class="d-none" accept=".jpg,.jpeg,.png,.webp" @change="handleModalFileSelected">
              <div class="display-6 text-primary mb-2"><i class="bi bi-cloud-arrow-up-fill"></i></div>
              <div class="fw-bold text-white small mb-1">
                {{ modalUploadFile ? modalUploadFile.name : 'Select or Drag & Drop 360° Photo' }}
              </div>
              <div class="text-secondary small" style="font-size:0.75rem;">JPG/PNG up to 20MB &bull; 2:1 aspect ratio</div>
            </div>

            <!-- Quick Tested Panoramas -->
            <div class="mb-2">
              <label class="form-label small text-secondary fw-bold">Or apply tested sample panorama:</label>
              <div class="d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" @click="applyModalSample('suite')">Luxury Suite</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" @click="applyModalSample('bedroom')">Master Bedroom</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" @click="applyModalSample('bathroom')">Bathroom</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" @click="applyModalSample('condo')">Skyline Terrace</button>
              </div>
            </div>
          </div>
          <div class="modal-footer border-secondary border-opacity-25">
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow" :disabled="!modalUploadFile || isModalUploading" @click="uploadModalPanorama">
              <span v-if="isModalUploading" class="spinner-border spinner-border-sm me-1"></span>
              {{ isModalUploading ? 'Uploading...' : 'Save 360° Photo' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 2: INTERACTIVE PANNELLUM 360 PREVIEW MODAL         -->
    <!-- ======================================================== -->
    <div class="modal fade" id="preview360Modal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-content-dark border border-secondary shadow-lg">
          <div class="modal-header border-secondary border-opacity-25 py-2">
            <div>
              <h6 class="modal-title fw-bold mb-0">
                <i class="bi bi-badge-vr text-info me-2"></i>360° Virtual Tour Preview: {{ previewRoom?.room_name }}
              </h6>
              <div class="small text-secondary" style="font-size:0.75rem;">Interactive spherical panorama preview</div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" @click="destroyModalViewer"></button>
          </div>
          <div class="modal-body p-0 position-relative" style="height: 480px; background: #000;">
            <div id="modalPannellumContainer" style="width: 100%; height: 100%;"></div>
            <div v-if="previewLoading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-75">
              <div class="spinner-border text-primary mb-2"></div>
              <div class="text-white small fw-bold">Loading 360° Scene...</div>
            </div>
          </div>
          <div class="modal-footer border-secondary border-opacity-25 py-2 d-flex justify-content-between">
            <span class="small text-secondary"><i class="bi bi-mouse me-1"></i>Click &amp; drag to look around. Scroll to zoom.</span>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal" @click="destroyModalViewer">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 3: EDIT ROOM & BOOKING LIMIT MODAL                 -->
    <!-- ======================================================== -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-dark border border-secondary shadow-lg" v-if="editingRoom">
          <div class="modal-header border-secondary border-opacity-25">
            <h6 class="modal-title fw-bold">
              <i class="bi bi-sliders text-primary me-2"></i>Edit Suite &amp; Booking Limit
            </h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            
            <div class="mb-3">
              <label class="form-label small text-secondary fw-bold">Room Name</label>
              <input type="text" v-model="editingRoom.room_name" class="form-control">
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Room Number / Code</label>
                <input type="text" v-model="editingRoom.room_number" class="form-control">
              </div>
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Room Category</label>
                <input type="text" v-model="editingRoom.room_type" class="form-control">
              </div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Price (PHP)</label>
                <input type="number" v-model.number="editingRoom.price" class="form-control">
              </div>
              <div class="col-6">
                <label class="form-label small text-warning fw-bold">
                  <i class="bi bi-shield-exclamation me-1"></i>Booking Limit (Max)
                </label>
                <input type="number" v-model.number="editingRoom.booking_limit" min="1" max="50" class="form-control text-warning border-warning fw-bold">
                <div class="text-secondary small mt-1" style="font-size:0.68rem;">Max concurrent bookings allowed before Sold Out</div>
              </div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Capacity</label>
                <input type="text" v-model="editingRoom.capacity" class="form-control">
              </div>
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Bed Configuration</label>
                <input type="text" v-model="editingRoom.bed_type" class="form-control">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small text-secondary fw-bold">Room Availability Status</label>
              <select v-model="editingRoom.status" class="form-select">
                <option value="Available">Available (Accepting Reservations)</option>
                <option value="Occupied">Occupied (Currently in Stay)</option>
                <option value="Cleaning">Cleaning &amp; Housekeeping</option>
                <option value="Under Maintenance">Under Maintenance</option>
                <option value="Sold Out">Sold Out</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small text-secondary fw-bold">Amenities (comma-separated)</label>
              <textarea v-model="editingRoom.amenities" rows="2" class="form-control"></textarea>
            </div>

          </div>
          <div class="modal-footer border-secondary border-opacity-25">
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow" :disabled="isSavingRoom" @click="saveEditingRoom">
              <span v-if="isSavingRoom" class="spinner-border spinner-border-sm me-1"></span>
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 4: ADD NEW ROOM MODAL                              -->
    <!-- ======================================================== -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-dark border border-secondary shadow-lg">
          <div class="modal-header border-secondary border-opacity-25">
            <h6 class="modal-title fw-bold">
              <i class="bi bi-plus-circle text-primary me-2"></i>Add New Suite to {{ currentHotel?.title }}
            </h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            
            <div class="mb-3">
              <label class="form-label small text-secondary fw-bold">Suite / Room Name</label>
              <input type="text" v-model="newRoomForm.room_name" class="form-control" placeholder="e.g. Executive Ocean Sunset Suite">
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Room Number</label>
                <input type="text" v-model="newRoomForm.room_number" class="form-control" placeholder="e.g. Suite 402">
              </div>
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Room Category</label>
                <input type="text" v-model="newRoomForm.room_type" class="form-control" placeholder="e.g. Deluxe Suite">
              </div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Price per Night (PHP)</label>
                <input type="number" v-model.number="newRoomForm.price" class="form-control">
              </div>
              <div class="col-6">
                <label class="form-label small text-warning fw-bold">Booking Limit</label>
                <input type="number" v-model.number="newRoomForm.booking_limit" min="1" max="50" class="form-control text-warning border-warning fw-bold">
              </div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Capacity</label>
                <input type="text" v-model="newRoomForm.capacity" class="form-control">
              </div>
              <div class="col-6">
                <label class="form-label small text-secondary fw-bold">Bed Type</label>
                <input type="text" v-model="newRoomForm.bed_type" class="form-control">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small text-secondary fw-bold">Room Photo URL</label>
              <input type="text" v-model="newRoomForm.image" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label small text-secondary fw-bold">Amenities</label>
              <textarea v-model="newRoomForm.amenities" rows="2" class="form-control"></textarea>
            </div>

          </div>
          <div class="modal-footer border-secondary border-opacity-25">
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow" :disabled="isAddingRoom" @click="submitAddRoom">
              <span v-if="isAddingRoom" class="spinner-border spinner-border-sm me-1"></span>
              Create Suite
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 5: GUEST FOLIO / PRINTABLE RESERVATION SLIP        -->
    <!-- ======================================================== -->
    <div class="modal fade" id="folioModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-dark border-0 shadow-lg" v-if="folioBooking">
          <div class="modal-header border-bottom py-2">
            <div class="d-flex align-items-center gap-2">
              <div class="brand-logo-badge" style="width:32px; height:32px; font-size:1rem;">
                <i class="bi bi-building"></i>
              </div>
              <div>
                <h6 class="modal-title fw-bold mb-0">StayFinder Front Desk Folio</h6>
                <div class="text-secondary small" style="font-size:0.7rem;">Official Guest Check-In Voucher</div>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4" id="printableFolioContent">
            
            <div class="text-center pb-3 border-bottom mb-3 border-secondary border-opacity-25">
              <h5 class="fw-bold mb-0">{{ currentHotel?.title }}</h5>
              <div class="text-secondary small">{{ currentHotel?.address }} &bull; {{ currentHotel?.city }}</div>
              <div class="text-secondary small">Front Desk Contact: {{ currentHotel?.nearest_terminal ? 'Direct Terminal Dispatch' : '+63 (2) 8888-0000' }}</div>
            </div>

            <div class="row g-2 mb-3 small">
              <div class="col-6">
                <span class="text-secondary d-block">Reservation Ref:</span>
                <strong class="font-monospace fs-6">{{ folioBooking.booking_id }}</strong>
              </div>
              <div class="col-6 text-end">
                <span class="text-secondary d-block">Booking Date:</span>
                <strong>{{ formatDateTime(folioBooking.created_at) }}</strong>
              </div>
            </div>

            <div class="p-3 rounded-3 mb-3 border border-secondary border-opacity-25" style="background: rgba(0,0,0,0.03);">
              <div class="row g-2 small">
                <div class="col-12 col-sm-6">
                  <span class="text-secondary d-block">Guest Name:</span>
                  <strong class="fs-6">{{ folioBooking.guest_name }}</strong>
                </div>
                <div class="col-12 col-sm-6 text-sm-end">
                  <span class="text-secondary d-block">Contact Phone:</span>
                  <strong>{{ folioBooking.guest_phone || 'N/A' }}</strong>
                </div>
                <div class="col-12">
                  <span class="text-secondary d-block">Email Address:</span>
                  <strong>{{ folioBooking.guest_email }}</strong>
                </div>
              </div>
            </div>

            <table class="table table-sm table-bordered mb-3 small border-secondary border-opacity-25">
              <tbody>
                <tr>
                  <td class="fw-bold text-secondary" style="width:35%; background: rgba(0,0,0,0.03);">Reserved Suite:</td>
                  <td>{{ folioBooking.room_name }} <span v-if="folioBooking.room_number">({{ folioBooking.room_number }})</span></td>
                </tr>
                <tr>
                  <td class="fw-bold text-secondary" style="background: rgba(0,0,0,0.03);">Check-in Schedule:</td>
                  <td>{{ formatDateTime(folioBooking.check_in_time) }}</td>
                </tr>
                <tr>
                  <td class="fw-bold text-secondary" style="background: rgba(0,0,0,0.03);">Stay Type:</td>
                  <td>{{ folioBooking.stay_type || 'Overnight' }} ({{ folioBooking.nights || 1 }} night)</td>
                </tr>
                <tr>
                  <td class="fw-bold text-secondary" style="background: rgba(0,0,0,0.03);">Downpayment (Paid):</td>
                  <td class="text-success fw-bold">₱{{ Number(folioBooking.downpayment_amount || 0).toLocaleString() }} ({{ folioBooking.payment_method }} &bull; Ref: {{ folioBooking.payment_ref }})</td>
                </tr>
                <tr>
                  <td class="fw-bold text-secondary" style="background: rgba(0,0,0,0.03);">Balance Due at Desk:</td>
                  <td class="text-danger fw-bold">₱{{ Number(folioBooking.balance_due || 0).toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>

            <div class="text-center pt-2">
              <div class="small text-secondary mb-1">Present this slip or digital QR code at front desk.</div>
              <div class="badge bg-dark px-3 py-2 font-monospace text-white" style="letter-spacing: 2px;">
                *{{ folioBooking.booking_id }}*
              </div>
            </div>

          </div>
          <div class="modal-footer border-top py-2 border-secondary border-opacity-25">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-sm btn-primary px-3 fw-bold" @click="printFolio">
              <i class="bi bi-printer me-1"></i> Print Voucher
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- ======================================================== -->
    <!-- MODAL 6: "GET STARTED" SETUP WIZARD TUTORIAL MODAL       -->
    <!-- ======================================================== -->
    <div class="modal fade" id="getStartedModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-dark border border-secondary shadow-lg">
          
          <!-- Modal Header -->
          <div class="modal-header border-secondary border-opacity-25 py-3">
            <div class="d-flex align-items-center gap-2">
              <div class="brand-icon-box" style="width: 38px; height: 38px; font-size: 1.15rem; border-radius: 10px; background: linear-gradient(135deg, #e05624, #c8714a);">
                <i class="bi bi-stars"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold mb-0">
                  Get Started Tutorial: {{ currentHotel?.title }}
                </h5>
                <div class="small text-secondary" style="font-size: 0.72rem;">
                  3-Step interactive onboarding guide to set up rooms, upload 360° panoramas, and configure booking limits
                </div>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <!-- Wizard Step Indicator Progress Bar -->
          <div class="px-4 pt-3 pb-2 border-bottom border-secondary border-opacity-25" style="background: rgba(0,0,0,0.03);">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                <span class="text-warning">Step {{ wizardStep }} of 3:</span>
                <span class="ms-1 fw-bold">
                  {{ wizardStep === 1 ? 'Compose & Manage Rooms' : (wizardStep === 2 ? 'Upload 360° Virtual Tours' : 'Set Booking Limits (Anti-Overbooking)') }}
                </span>
              </span>
              <span class="badge rounded-pill" :class="wizardStep === 3 ? 'bg-success text-white' : 'bg-primary text-white'" style="font-size: 0.68rem;">
                {{ Math.round((wizardStep / 3) * 100) }}% Completed
              </span>
            </div>
            <div class="progress" style="height: 6px; background: rgba(0,0,0,0.08);">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning text-dark" role="progressbar" :style="'width: ' + ((wizardStep / 3) * 100) + '%'"></div>
            </div>
            <!-- Step Tabs -->
            <div class="d-flex justify-content-between mt-2 text-center" style="font-size: 0.75rem;">
              <span class="cursor-pointer" :class="wizardStep === 1 ? 'fw-bold text-warning' : 'text-secondary'" style="cursor: pointer;" @click="wizardStep = 1">
                <i class="bi bi-1-circle-fill me-1"></i> 1. Compose Rooms
              </span>
              <span class="cursor-pointer" :class="wizardStep === 2 ? 'fw-bold text-info' : 'text-secondary'" style="cursor: pointer;" @click="wizardStep = 2">
                <i class="bi bi-2-circle-fill me-1"></i> 2. Upload 360° Photo
              </span>
              <span class="cursor-pointer" :class="wizardStep === 3 ? 'fw-bold text-success' : 'text-secondary'" style="cursor: pointer;" @click="wizardStep = 3">
                <i class="bi bi-3-circle-fill me-1"></i> 3. Set Booking Limits
              </span>
            </div>
          </div>

          <!-- Modal Body with 3 Steps -->
          <div class="modal-body p-4">

            <!-- STEP 1: Compose Rooms -->
            <div v-if="wizardStep === 1">
              <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-warning text-dark fs-6 px-2 py-1"><i class="bi bi-door-open-fill"></i> Step 1</span>
                <h5 class="fw-bold mb-0">How to Compose Rooms &amp; Suites</h5>
              </div>
              <p class="text-secondary small mb-3">
                Compose your room accommodations so travelers browsing <strong>{{ currentHotel?.title }}</strong> on the StayFinder map can view exact room details, bed layouts, pricing, and live availability.
              </p>

              <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 wizard-info-card">
                    <div class="fw-bold small mb-2"><i class="bi bi-pencil-square text-primary me-1"></i> Key Fields to Configure:</div>
                    <ul class="text-secondary small mb-0 ps-3">
                      <li><strong>Suite Name:</strong> e.g. "Executive Ocean Villa", "Deluxe Twin Room".</li>
                      <li><strong>Room Number:</strong> e.g. "Villa 101", "Suite 405".</li>
                      <li><strong>Category &amp; Bed:</strong> 1 King Bed, 2 Queen Beds, Single.</li>
                      <li><strong>Rate / Night:</strong> Nightly or hourly rates in PHP.</li>
                      <li><strong>Amenities:</strong> Wi-Fi, Aircon, Smart TV, Minibar, Balcony.</li>
                    </ul>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 wizard-info-card">
                    <div class="fw-bold small mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i> Current Branch Inventory:</div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <span class="small text-secondary">Configured Rooms:</span>
                      <strong>{{ rooms.length }} Suites</strong>
                    </div>
                    <div class="small text-secondary mb-3">
                      You can edit existing suites or compose brand-new accommodations at any time.
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                      <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" @click="wizardAction('addRoom')">
                        <i class="bi bi-plus-circle me-1"></i> Compose New Room Now
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" @click="wizardAction('viewRooms')">
                        <i class="bi bi-list-ul me-1"></i> View Inventory
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="info-callout text-secondary small">
                <strong>Pro Tip:</strong> Ensure your room names match the actual layout. Clear descriptions with list of amenities improve booking conversion rates by 45%.
              </div>
            </div>

            <!-- STEP 2: Upload 360° Panorama Image -->
            <div v-if="wizardStep === 2">
              <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-info text-dark fs-6 px-2 py-1"><i class="bi bi-camera-reels-fill"></i> Step 2</span>
                <h5 class="fw-bold mb-0">How to Upload 360° Virtual Tour Panorama</h5>
              </div>
              <p class="text-secondary small mb-3">
                Give guests an immersive virtual walk-through of each room accommodation. Every room can have its own customized 360° spherical panoramic photo rendered in high definition!
              </p>

              <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 text-center wizard-info-card">
                    <div class="fs-4 text-warning mb-1"><i class="bi bi-phone"></i></div>
                    <div class="fw-bold small mb-1">1. Take the Photo</div>
                    <div class="text-secondary small" style="font-size: 0.75rem;">
                      Use smartphone panorama mode (iPhone / Android) or any 360 camera app (Google Street View, Insta360, Ricoh Theta).
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 text-center wizard-info-card">
                    <div class="fs-4 text-info mb-1"><i class="bi bi-aspect-ratio"></i></div>
                    <div class="fw-bold small mb-1">2. Equirectangular (2:1)</div>
                    <div class="text-secondary small" style="font-size: 0.75rem;">
                      Save in standard 2:1 width-to-height ratio (e.g. 4000x2000px, .jpg or .png format up to 20MB).
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 text-center wizard-info-card">
                    <div class="fs-4 text-success mb-1"><i class="bi bi-cloud-arrow-up"></i></div>
                    <div class="fw-bold small mb-1">3. Upload &amp; Preview</div>
                    <div class="text-secondary small" style="font-size: 0.75rem;">
                      Drop the photo into the 360° Studio. The Pannellum engine automatically attaches it to the room on the traveler map!
                    </div>
                  </div>
                </div>
              </div>

              <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                <button type="button" class="btn btn-sm btn-info rounded-pill px-3 py-1 text-dark fw-bold" @click="wizardAction('studio360')">
                  <i class="bi bi-camera-reels-fill me-1"></i> Open 360° Panorama Studio
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" @click="wizardAction('sample360')">
                  <i class="bi bi-magic me-1"></i> Apply Preset Sample 360 Photo
                </button>
              </div>

              <div class="info-callout text-secondary small">
                <strong>Live Traveler Sync:</strong> Once uploaded, travelers clicking the "360° Virtual Tour" button on your hotel pin will immediately see and explore your uploaded room in 360°!
              </div>
            </div>

            <!-- STEP 3: Set Booking Limits -->
            <div v-if="wizardStep === 3">
              <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-success text-white fs-6 px-2 py-1"><i class="bi bi-shield-check"></i> Step 3</span>
                <h5 class="fw-bold mb-0">How to Set Room Booking Limits (Anti-Overbooking)</h5>
              </div>
              <p class="text-secondary small mb-3">
                Protect your hotel branch against overbooking during peak weekends and holidays. The booking limit guarantees that only the exact number of slots you allow can be reserved.
              </p>

              <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 wizard-info-card">
                    <div class="fw-bold small mb-2"><i class="bi bi-sliders text-warning me-1"></i> How Booking Limits Work:</div>
                    <ul class="text-secondary small mb-0 ps-3">
                      <li>Each room category has an adjustable <strong>Booking Limit</strong> (e.g. 5, 8, or 10 units).</li>
                      <li>When active confirmed reservations reach the limit:
                        <span class="badge bg-danger rounded-pill ms-1 text-white">Auto "Sold Out"</span>
                      </li>
                      <li>Travelers on the map see the room marked as "Sold Out" and cannot book further.</li>
                      <li>When a guest checks out or cancels, the slot instantly opens back up!</li>
                    </ul>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 wizard-info-card">
                    <div class="fw-bold small mb-2"><i class="bi bi-shield-lock-fill text-success me-1"></i> Anti-Spam Security:</div>
                    <ul class="text-secondary small mb-0 ps-3">
                      <li>To confirm any reservation slot, guests must deposit a <strong>20% downpayment</strong> via GCash, Maya, or GrabPay.</li>
                      <li>Eliminates fake spam bookings and no-shows.</li>
                      <li>Remaining 80% balance is collected by your front desk upon arrival.</li>
                    </ul>
                    <div class="mt-3">
                      <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold text-white" @click="wizardAction('setLimits')">
                        <i class="bi bi-sliders me-1"></i> Configure Room Limits Now
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="info-callout text-secondary small">
                <strong>Desk Bell Notification:</strong> Keep your speaker audio turned ON! Whenever a guest reserves a slot within your booking limit, your desk bell will chime and display the glowing alert bar.
              </div>
            </div>

          </div>

          <!-- Modal Footer with Navigation Buttons -->
          <div class="modal-footer border-secondary border-opacity-25 py-3 d-flex justify-content-between align-items-center">
            <div>
              <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" :disabled="wizardStep <= 1" @click="prevWizardStep">
                <i class="bi bi-arrow-left me-1"></i> Previous
              </button>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button v-if="wizardStep < 3" type="button" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm" @click="nextWizardStep">
                Next Step <i class="bi bi-arrow-right ms-1"></i>
              </button>
              <button v-else type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold text-white shadow-sm" @click="finishGetStarted">
                <i class="bi bi-check-circle-fill me-1"></i> Complete &amp; Start Managing
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>

  <!-- Vue 3 Application Logic -->
  <script>
    const { createApp, ref, computed, onMounted, nextTick } = Vue;

    createApp({
      setup() {
        const activeHotelId = ref('<?= $assignedHotelId ?>');
        const currentUserName = ref('<?= htmlspecialchars($userName) ?>');
        const currentUserEmail = ref('<?= htmlspecialchars($userEmail) ?>');
        const currentTheme = ref(document.documentElement.getAttribute('data-theme') || 'dark');
        const isAdmin = ref(<?= json_encode($isAdmin) ?>);
        const autoWelcome = ref(<?= json_encode($autoWelcome) ?>);
        const onboardingDismissed = ref(false);
        const wizardStep = ref(1);
        let getStartedModalInstance = null;
        
        // Navigation & tabs
        const selectedTab = ref('bookings'); // 'bookings' | 'rooms' | 'studio360' | 'tutorial'
        const activeBookingFilter = ref('all');
        const searchBookingQuery = ref('');

        // Data stores
        const currentHotel = ref(null);
        const allBranches = ref([]);
        const rooms = ref([]);
        const bookings = ref([]);
        const stats = ref({
          new_requests_count: 0,
          active_stays_count: 0,
          total_rooms: 0,
          available_rooms: 0,
          today_downpayment: 0
        });

        const latestBooking = ref(null);
        const alertDismissed = ref(false);
        const soundEnabled = ref(true);

        // 360 Studio state
        const studioSelectedRoomId = ref('');
        const selectedUploadFile = ref(null);
        const dragOver = ref(false);
        const isUploading = ref(false);
        const studioLoading = ref(false);
        const studioRotating = ref(false);
        let studioPannellumViewer = null;

        // Modals state
        const modalRoom = ref(null);
        const modalUploadFile = ref(null);
        const modalDragOver = ref(false);
        const isModalUploading = ref(false);

        const previewRoom = ref(null);
        const previewLoading = ref(false);
        let previewModalViewer = null;

        const editingRoom = ref(null);
        const isSavingRoom = ref(false);

        const newRoomForm = ref({
          room_name: '',
          room_number: '',
          room_type: 'Deluxe Suite',
          price: 5500,
          booking_limit: 8,
          capacity: '2 Adults',
          bed_type: '1 King Bed',
          amenities: 'Fast Wi-Fi, Air Conditioning, Hot Rain Shower, Smart TV, City View',
          image: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'
        });
        const isAddingRoom = ref(false);

        const folioBooking = ref(null);

        // Computed
        const filteredBookings = computed(() => {
          let list = bookings.value || [];
          if (activeBookingFilter.value === 'unread') {
            list = list.filter(b => b.is_unread || b.status === 'PENDING');
          } else if (activeBookingFilter.value === 'confirmed') {
            list = list.filter(b => b.status === 'CONFIRMED');
          } else if (activeBookingFilter.value === 'checked_in') {
            list = list.filter(b => b.status === 'CHECKED_IN');
          } else if (activeBookingFilter.value === 'history') {
            list = list.filter(b => b.status === 'CHECKED_OUT' || b.status === 'CANCELLED');
          }

          if (searchBookingQuery.value.trim()) {
            const q = searchBookingQuery.value.toLowerCase();
            list = list.filter(b => 
              (b.guest_name && b.guest_name.toLowerCase().includes(q)) ||
              (b.booking_id && b.booking_id.toLowerCase().includes(q)) ||
              (b.room_name && b.room_name.toLowerCase().includes(q)) ||
              (b.payment_ref && b.payment_ref.toLowerCase().includes(q))
            );
          }
          return list;
        });

        const currentStudioPanoTitle = computed(() => {
          const r = rooms.value.find(rm => rm.id === studioSelectedRoomId.value);
          return r ? (r.room_number ? r.room_number + ' - ' : '') + r.room_name : 'No Room Selected';
        });

        // ── Web Audio API Desk Bell Chime Generator ─────────────────
        const playDeskBell = () => {
          if (!soundEnabled.value) return;
          try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            
            // Dual chime harmonics: 880Hz (A5) and 1760Hz (A6) with gentle brass decay
            const now = ctx.currentTime;
            
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(880, now);
            gain1.gain.setValueAtTime(0.35, now);
            gain1.gain.exponentialRampToValueAtTime(0.0001, now + 1.2);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 1.2);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(1760, now + 0.05);
            gain2.gain.setValueAtTime(0.2, now + 0.05);
            gain2.gain.exponentialRampToValueAtTime(0.0001, now + 1.4);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.05);
            osc2.stop(now + 1.4);
          } catch(e) {
            console.warn('[Audio] Bell chime sound blocked or unsupported:', e);
          }
        };

        const toggleSound = () => {
          soundEnabled.value = !soundEnabled.value;
          if (soundEnabled.value) {
            playDeskBell();
          }
        };

        const toggleTheme = () => {
          let curr = document.documentElement.getAttribute('data-theme') || 'dark';
          let next = curr === 'dark' ? 'light' : 'dark';
          document.documentElement.setAttribute('data-theme', next);
          localStorage.setItem('stayfinder_theme', next);
          currentTheme.value = next;
        };

        // ── Load Branch Data ────────────────────────────────────────
        const loadBranchData = async () => {
          try {
            const res = await axios.get(`api.php?action=reception_get_data&hotel_id=${encodeURIComponent(activeHotelId.value)}`);
            if (res.data.status === 'success') {
              currentHotel.value = res.data.hotel;
              allBranches.value = res.data.branches || [];
              rooms.value = res.data.rooms || [];
              bookings.value = res.data.bookings || [];
              stats.value = res.data.stats || stats.value;

              if (rooms.value.length > 0 && !studioSelectedRoomId.value) {
                studioSelectedRoomId.value = rooms.value[0].id;
                nextTick(() => {
                  initStudioPannellum();
                });
              }

              // Check if unread requests exist -> chime
              if (stats.value.new_requests_count > 0 && !alertDismissed.value) {
                playDeskBell();
              }
            }
          } catch(err) {
            console.error('[Reception] Error loading branch data:', err);
          }
        };

        // Switch active hotel branch
        const switchBranch = async (hotelId) => {
          activeHotelId.value = hotelId;
          alertDismissed.value = false;
          await axios.get(`api.php?action=reception_switch_account&hotel_id=${encodeURIComponent(hotelId)}`);
          await loadBranchData();
          onStudioRoomSelect();
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `Switched Front Desk to ${currentHotel.value?.title}`,
            showConfirmButton: false,
            timer: 2500
          });
        };

        // ── Poll for New Booking Requests Every 6s ──────────────────
        let pollTimer = null;
        let lastUnreadCount = 0;

        const startPolling = () => {
          pollTimer = setInterval(async () => {
            try {
              const res = await axios.get(`api.php?action=reception_poll&hotel_id=${encodeURIComponent(activeHotelId.value)}`);
              if (res.data && res.data.status === 'success') {
                const unread = res.data.unread_count;
                if (unread > lastUnreadCount && unread > 0) {
                  // New booking detected!
                  stats.value.new_requests_count = unread;
                  latestBooking.value = res.data.latest_booking;
                  alertDismissed.value = false;
                  playDeskBell();
                  
                  // Reload bookings list in background
                  const bkRes = await axios.get(`api.php?action=reception_get_data&hotel_id=${encodeURIComponent(activeHotelId.value)}`);
                  if (bkRes.data.status === 'success') {
                    bookings.value = bkRes.data.bookings || [];
                    stats.value = bkRes.data.stats || stats.value;
                  }
                }
                lastUnreadCount = unread;
              }
            } catch(e) {}
          }, 6000);
        };

        const markAllAsRead = async () => {
          try {
            await axios.post('api.php?action=reception_mark_read', { hotel_id: activeHotelId.value });
            stats.value.new_requests_count = 0;
            bookings.value.forEach(b => b.is_unread = false);
          } catch(e) {}
        };

        // ── Booking Status Updates ──────────────────────────────────
        const updateBookingStatus = async (bookingId, newStatus) => {
          try {
            const res = await axios.post('api.php?action=reception_update_booking_status', {
              booking_id: bookingId,
              status: newStatus
            });
            if (res.data.status === 'success') {
              const bk = bookings.value.find(b => b.booking_id === bookingId);
              if (bk) {
                bk.status = newStatus;
                bk.is_unread = false;
              }
              await loadBranchData();
              Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: res.data.message || `Booking ${newStatus}`,
                showConfirmButton: false,
                timer: 2000
              });
            }
          } catch(err) {
            Swal.fire('Error', 'Unable to update booking status.', 'error');
          }
        };

        const promptCancelBooking = (booking) => {
          Swal.fire({
            title: 'Decline / Cancel Booking?',
            text: `Are you sure you want to cancel reservation ${booking.booking_id} for ${booking.guest_name}? This will free up the room capacity.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Cancel Reservation'
          }).then((res) => {
            if (res.isConfirmed) {
              updateBookingStatus(booking.booking_id, 'CANCELLED');
            }
          });
        };

        // ── 360 Studio Pannellum Viewer ─────────────────────────────
        const initStudioPannellum = () => {
          if (typeof pannellum === 'undefined') return;
          const r = rooms.value.find(rm => rm.id === studioSelectedRoomId.value);
          const panoUrl = r?.custom_360_url || 'img/pano_bedroom.jpg';

          if (studioPannellumViewer) {
            try { studioPannellumViewer.destroy(); } catch(e){}
            studioPannellumViewer = null;
          }

          studioLoading.value = true;
          try {
            studioPannellumViewer = pannellum.viewer('studioPannellumContainer', {
              type: 'equirectangular',
              panorama: panoUrl,
              autoLoad: true,
              autoRotate: studioRotating.value ? -2 : 0,
              compass: false,
              showControls: true,
              hfov: 100
            });
            studioPannellumViewer.on('load', () => {
              studioLoading.value = false;
            });
          } catch(err) {
            studioLoading.value = false;
          }
        };

        const onStudioRoomSelect = () => {
          initStudioPannellum();
        };

        const toggleStudioAutoRotate = () => {
          studioRotating.value = !studioRotating.value;
          if (studioPannellumViewer) {
            studioPannellumViewer.setAutoRotate(studioRotating.value ? -2 : 0);
          }
        };

        const resetStudioView = () => {
          if (studioPannellumViewer) {
            studioPannellumViewer.setPitch(0);
            studioPannellumViewer.setYaw(0);
            studioPannellumViewer.setHfov(100);
          }
        };

        // ── File Upload Handlers (Studio) ───────────────────────────
        const fileInputRef = ref(null);
        const triggerFileInput = () => {
          if (fileInputRef.value) fileInputRef.value.click();
        };
        const handleFileSelected = (e) => {
          if (e.target.files && e.target.files[0]) {
            selectedUploadFile.value = e.target.files[0];
          }
        };
        const handleFileDrop = (e) => {
          dragOver.value = false;
          if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            selectedUploadFile.value = e.dataTransfer.files[0];
          }
        };

        const uploadSelectedPanorama = async () => {
          if (!selectedUploadFile.value || !studioSelectedRoomId.value) return;
          isUploading.value = true;
          try {
            const formData = new FormData();
            formData.append('room_id', studioSelectedRoomId.value);
            formData.append('panorama_file', selectedUploadFile.value);

            const res = await axios.post('api.php?action=reception_upload_360', formData, {
              headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (res.data.status === 'success') {
              const r = rooms.value.find(rm => rm.id === studioSelectedRoomId.value);
              if (r) r.custom_360_url = res.data.custom_360_url;
              selectedUploadFile.value = null;
              initStudioPannellum();
              Swal.fire('360° Panorama Uploaded!', res.data.message, 'success');
            } else {
              Swal.fire('Upload Error', res.data.message || 'Failed to upload photo.', 'error');
            }
          } catch(err) {
            Swal.fire('Upload Failed', 'An error occurred during file upload.', 'error');
          } finally {
            isUploading.value = false;
          }
        };

        const applyPresetSample = async (sampleKey) => {
          if (!studioSelectedRoomId.value) return;
          try {
            const res = await axios.post('api.php?action=reception_upload_360', {
              room_id: studioSelectedRoomId.value,
              sample_pano_key: sampleKey
            });
            if (res.data.status === 'success') {
              const r = rooms.value.find(rm => rm.id === studioSelectedRoomId.value);
              if (r) r.custom_360_url = res.data.custom_360_url;
              initStudioPannellum();
              Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Sample 360° Panorama Applied!',
                showConfirmButton: false,
                timer: 2000
              });
            }
          } catch(e){}
        };

        // ── Modal 1: Upload from Room Card ──────────────────────────
        let uploadModalInstance = null;
        const modalFileInputRef = ref(null);

        const open360UploadModal = (room) => {
          modalRoom.value = room;
          modalUploadFile.value = null;
          const el = document.getElementById('upload360Modal');
          if (el) {
            uploadModalInstance = bootstrap.Modal.getOrCreateInstance(el);
            uploadModalInstance.show();
          }
        };

        const triggerModalFileInput = () => {
          if (modalFileInputRef.value) modalFileInputRef.value.click();
        };

        const handleModalFileSelected = (e) => {
          if (e.target.files && e.target.files[0]) {
            modalUploadFile.value = e.target.files[0];
          }
        };

        const handleModalFileDrop = (e) => {
          modalDragOver.value = false;
          if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            modalUploadFile.value = e.dataTransfer.files[0];
          }
        };

        const uploadModalPanorama = async () => {
          if (!modalUploadFile.value || !modalRoom.value) return;
          isModalUploading.value = true;
          try {
            const formData = new FormData();
            formData.append('room_id', modalRoom.value.id);
            formData.append('panorama_file', modalUploadFile.value);

            const res = await axios.post('api.php?action=reception_upload_360', formData, {
              headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (res.data.status === 'success') {
              modalRoom.value.custom_360_url = res.data.custom_360_url;
              if (uploadModalInstance) uploadModalInstance.hide();
              Swal.fire('Success', '360° Photo saved and active!', 'success');
            }
          } catch(err) {
            Swal.fire('Error', 'File upload failed.', 'error');
          } finally {
            isModalUploading.value = false;
          }
        };

        const applyModalSample = async (sampleKey) => {
          if (!modalRoom.value) return;
          try {
            const res = await axios.post('api.php?action=reception_upload_360', {
              room_id: modalRoom.value.id,
              sample_pano_key: sampleKey
            });
            if (res.data.status === 'success') {
              modalRoom.value.custom_360_url = res.data.custom_360_url;
              if (uploadModalInstance) uploadModalInstance.hide();
              Swal.fire('Sample Applied', '360° view updated!', 'success');
            }
          } catch(e){}
        };

        // ── Modal 2: Preview 360 Panorama Modal ─────────────────────
        let previewModalInstance = null;

        const previewRoom360 = (room) => {
          previewRoom.value = room;
          const el = document.getElementById('preview360Modal');
          if (el) {
            previewModalInstance = bootstrap.Modal.getOrCreateInstance(el);
            previewModalInstance.show();
            nextTick(() => {
              initModalViewer(room);
            });
          }
        };

        const initModalViewer = (room) => {
          if (typeof pannellum === 'undefined') return;
          const panoUrl = room.custom_360_url || 'img/pano_bedroom.jpg';
          destroyModalViewer();
          previewLoading.value = true;
          try {
            previewModalViewer = pannellum.viewer('modalPannellumContainer', {
              type: 'equirectangular',
              panorama: panoUrl,
              autoLoad: true,
              autoRotate: -1.5,
              compass: false,
              showControls: true
            });
            previewModalViewer.on('load', () => {
              previewLoading.value = false;
            });
          } catch(e) {
            previewLoading.value = false;
          }
        };

        const destroyModalViewer = () => {
          if (previewModalViewer) {
            try { previewModalViewer.destroy(); } catch(e){}
            previewModalViewer = null;
          }
        };

        // ── Modal 3: Edit Room & Limits ─────────────────────────────
        let editModalInstance = null;

        const openEditRoomModal = (room) => {
          editingRoom.value = JSON.parse(JSON.stringify(room));
          const el = document.getElementById('editRoomModal');
          if (el) {
            editModalInstance = bootstrap.Modal.getOrCreateInstance(el);
            editModalInstance.show();
          }
        };

        const saveEditingRoom = async () => {
          if (!editingRoom.value) return;
          isSavingRoom.value = true;
          try {
            const res = await axios.post('api.php?action=reception_update_room', editingRoom.value);
            if (res.data.status === 'success') {
              await loadBranchData();
              if (editModalInstance) editModalInstance.hide();
              Swal.fire('Saved!', res.data.message, 'success');
            }
          } catch(err) {
            Swal.fire('Error', 'Unable to save room details.', 'error');
          } finally {
            isSavingRoom.value = false;
          }
        };

        // ── Modal 4: Add New Room ───────────────────────────────────
        let addRoomModalInstance = null;

        const openAddRoomModal = () => {
          const el = document.getElementById('addRoomModal');
          if (el) {
            addRoomModalInstance = bootstrap.Modal.getOrCreateInstance(el);
            addRoomModalInstance.show();
          }
        };

        const submitAddRoom = async () => {
          if (!newRoomForm.value.room_name) {
            Swal.fire('Warning', 'Please enter a room name.', 'warning');
            return;
          }
          isAddingRoom.value = true;
          try {
            const payload = { ...newRoomForm.value, hotel_id: activeHotelId.value };
            const res = await axios.post('api.php?action=reception_add_room', payload);
            if (res.data.status === 'success') {
              await loadBranchData();
              if (addRoomModalInstance) addRoomModalInstance.hide();
              Swal.fire('Suite Added!', res.data.message, 'success');
            }
          } catch(err) {
            Swal.fire('Error', 'Unable to create room.', 'error');
          } finally {
            isAddingRoom.value = false;
          }
        };

        // ── Modal 5: Printable Folio ────────────────────────────────
        let folioModalInstance = null;

        const openGuestFolio = (booking) => {
          folioBooking.value = booking;
          const el = document.getElementById('folioModal');
          if (el) {
            folioModalInstance = bootstrap.Modal.getOrCreateInstance(el);
            folioModalInstance.show();
          }
        };

        const printFolio = () => {
          window.print();
        };

        // ── Simulate Demo Booking for Testing ───────────────────────
        const simulateDemoBooking = async () => {
          try {
            const sampleGuests = ['Capt. James Vance', 'Engr. Bea Tan', 'Dr. Roberto Luna', 'Atty. Sofia Reyes', 'Carlos Mendoza'];
            const randomGuest = sampleGuests[Math.floor(Math.random() * sampleGuests.length)];
            const targetRoom = rooms.value[0] || { id: 'rm-sample-1', room_name: 'Executive Deluxe Suite', price: 6500, room_number: 'Suite 201' };

            const payload = {
              hotel_id: activeHotelId.value,
              hotel_title: currentHotel.value?.title || 'Luxury Resort',
              room_id: targetRoom.id,
              room_name: targetRoom.room_name,
              room_number: targetRoom.room_number || 'Room 101',
              guest_name: randomGuest,
              guest_email: randomGuest.toLowerCase().replace(/[^a-z]/g, '') + '@gmail.com',
              guest_phone: '+63 917 ' + Math.floor(1000000 + Math.random() * 9000000),
              stay_type: 'Overnight Stay',
              check_in_time: new Date().toISOString().slice(0, 19).replace('T', ' '),
              nights: 1,
              amount: targetRoom.price,
              payment_method: 'GCash',
              downpayment_amount: Math.round(targetRoom.price * 0.2),
              balance_due: Math.round(targetRoom.price * 0.8),
              payment_ref: 'GC-' + Math.floor(100000 + Math.random() * 900000)
            };

            const res = await axios.post('api.php?action=book_hotel', payload);
            if (res.data.status === 'success') {
              await loadBranchData();
              selectedTab.value = 'bookings';
              activeBookingFilter.value = 'unread';
              alertDismissed.value = false;
              playDeskBell();
              Swal.fire({
                title: '🛎️ Demo Booking Simulated!',
                html: `Guest <strong>${randomGuest}</strong> has reserved <strong>${targetRoom.room_name}</strong> with GCash downpayment (Ref: ${payload.payment_ref}).<br><br>The front desk bell has chimed!`,
                icon: 'success',
                confirmButtonColor: '#C8714A'
              });
            }
          } catch(err) {
            console.error('Demo simulation error:', err);
          }
        };

        // Format helpers
        const formatDateTime = (dtStr) => {
          if (!dtStr) return 'N/A';
          try {
            const d = new Date(dtStr);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
          } catch(e) {
            return dtStr;
          }
        };

        const getBookingStatusBadge = (status) => {
          switch(status) {
            case 'CONFIRMED': return 'bg-success text-white';
            case 'CHECKED_IN': return 'bg-info text-dark';
            case 'CHECKED_OUT': return 'bg-secondary text-white';
            case 'CANCELLED': return 'bg-danger text-white';
            default: return 'bg-warning text-dark';
          }
        };

        // ── Get Started Setup Wizard Controls ───────────────────────
        const openGetStartedModal = () => {
          const el = document.getElementById('getStartedModal');
          if (el) {
            getStartedModalInstance = bootstrap.Modal.getOrCreateInstance(el);
            getStartedModalInstance.show();
          }
        };

        const openWizardStep = (step) => {
          wizardStep.value = step;
          openGetStartedModal();
        };

        const nextWizardStep = () => {
          if (wizardStep.value < 3) wizardStep.value++;
        };

        const prevWizardStep = () => {
          if (wizardStep.value > 1) wizardStep.value--;
        };

        const finishGetStarted = () => {
          if (getStartedModalInstance) {
            getStartedModalInstance.hide();
          }
          onboardingDismissed.value = true;
          localStorage.setItem('reception_onboarded_' + activeHotelId.value, '1');
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Setup Completed! Welcome to Front Desk.',
            showConfirmButton: false,
            timer: 2500
          });
        };

        const dismissOnboardingBanner = () => {
          onboardingDismissed.value = true;
          localStorage.setItem('reception_onboarded_' + activeHotelId.value, '1');
        };

        const wizardAction = (action) => {
          if (getStartedModalInstance) {
            getStartedModalInstance.hide();
          }
          if (action === 'addRoom') {
            openAddRoomModal();
          } else if (action === 'viewRooms') {
            selectedTab.value = 'rooms';
          } else if (action === 'studio360') {
            selectedTab.value = 'studio360';
          } else if (action === 'sample360') {
            selectedTab.value = 'studio360';
            if (rooms.value.length > 0) {
              studioSelectedRoomId.value = rooms.value[0].id;
              applyPresetSample('suite');
            }
          } else if (action === 'setLimits') {
            selectedTab.value = 'rooms';
            nextTick(() => {
              const firstInput = document.querySelector('.reception-room-card input[type="number"]');
              if (firstInput) {
                firstInput.focus();
                firstInput.classList.add('border-warning');
              }
            });
          }
        };

        onMounted(() => {
          loadBranchData();
          startPolling();

          // Auto-trigger Get Started wizard if first time or welcome=1
          const isDone = localStorage.getItem('reception_onboarded_' + activeHotelId.value);
          if (isDone === '1' && !autoWelcome.value) {
            onboardingDismissed.value = true;
          } else {
            setTimeout(() => {
              openGetStartedModal();
            }, 800);
          }

          // Real-time theme sync across tabs
          window.addEventListener('storage', (e) => {
            if (e.key === 'stayfinder_theme' && e.newValue) {
              document.documentElement.setAttribute('data-theme', e.newValue);
              currentTheme.value = e.newValue;
            }
          });
        });

        return {
          activeHotelId, currentUserName, currentUserEmail, currentTheme,
          isAdmin, autoWelcome, onboardingDismissed, wizardStep,
          selectedTab, activeBookingFilter, searchBookingQuery,
          currentHotel, allBranches, rooms, bookings, stats,
          filteredBookings, latestBooking, alertDismissed, soundEnabled,
          studioSelectedRoomId, selectedUploadFile, dragOver, isUploading,
          studioLoading, studioRotating, currentStudioPanoTitle,
          modalRoom, modalUploadFile, modalDragOver, isModalUploading,
          previewRoom, previewLoading, editingRoom, isSavingRoom,
          newRoomForm, isAddingRoom, folioBooking,
          playDeskBell, toggleSound, toggleTheme, switchBranch, markAllAsRead,
          updateBookingStatus, promptCancelBooking,
          initStudioPannellum, onStudioRoomSelect, toggleStudioAutoRotate, resetStudioView,
          fileInputRef, triggerFileInput, handleFileSelected, handleFileDrop,
          uploadSelectedPanorama, applyPresetSample,
          open360UploadModal, modalFileInputRef, triggerModalFileInput,
          handleModalFileSelected, handleModalFileDrop, uploadModalPanorama, applyModalSample,
          previewRoom360, destroyModalViewer,
          openEditRoomModal, saveEditingRoom,
          openAddRoomModal, submitAddRoom,
          openGuestFolio, printFolio, simulateDemoBooking,
          formatDateTime, getBookingStatusBadge,
          openGetStartedModal, openWizardStep, nextWizardStep, prevWizardStep,
          finishGetStarted, dismissOnboardingBanner, wizardAction
        };
      }
    }).mount('#receptionApp');
  </script>

</body>
</html>
