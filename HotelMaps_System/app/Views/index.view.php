<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Automatically clear temporary guest session on the landing page so first-time visitors always see Sign In / Get Started
if (isset($_SESSION['role']) && $_SESSION['role'] === 'guest') {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['role']);
}
// An authenticated user must have an active registered account, not a guest pass
$isLoggedIn = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') !== 'guest') && (strpos((string)$_SESSION['user_id'], 'guest_') !== 0);
$userName = $_SESSION['user_name'] ?? 'Traveler';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ComfortVue Philippines - Express Stays & Interactive Maps</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css?v=1.1">
  <link rel="stylesheet" href="css/landing.css?v=1.1">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="landing-body">
  <!-- Distinct Landing Page Navigation Header -->
  <nav class="navbar landing-navbar sticky-top">
    <div class="container-fluid px-4 px-md-5 d-flex align-items-center justify-content-between">
      <a href="index.php" class="d-flex align-items-center gap-3 text-decoration-none">
        <div class="brand-logo-badge"><i class="bi bi-compass"></i></div>
        <div>
          <div class="d-flex align-items-center gap-2">
            <span class="fs-5 fw-bold text-mocha tracking-tight">ComfortVue</span>
            <span class="badge" style="background:rgba(107,143,113,0.15); color:var(--sage); font-size:0.65rem; border:1px solid rgba(107,143,113,0.3);">METRO MANILA</span>
          </div>
          <div class="text-secondary d-none d-sm-block" style="font-size: 0.72rem;">Motels • Cozy Airbnbs • Short-Term Rentals</div>
        </div>
      </a>

      <!-- Right Side User Authentication / Get Started CTA -->
      <div class="d-flex align-items-center gap-2 flex-nowrap">
        <?php if ($isLoggedIn): ?>
          <span class="landing-user-badge text-nowrap">
            <i class="bi bi-person-check-fill text-success"></i> <?= htmlspecialchars($userName) ?>
          </span>
          <a href="dashboard.php" class="btn btn-landing-getstarted text-nowrap">
            <i class="bi bi-grid-fill me-1"></i> Dashboard
          </a>
          <a href="logout.php" class="btn btn-landing-signin text-danger border-danger border-opacity-25 text-nowrap" title="Sign Out">
            <i class="bi bi-box-arrow-right me-1"></i> Sign Out
          </a>
        <?php else: ?>
          <a href="auth.php?action=login" class="btn btn-landing-signin text-nowrap">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
          </a>
          <a href="auth.php?action=register" class="btn btn-landing-getstarted text-nowrap">
            <i class="bi bi-arrow-right-circle-fill me-1"></i> Get Started
          </a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <header class="hero-section text-white position-relative">
    <div class="container py-5">
      <div class="row justify-content-center">

        <!-- Center: Headline, CTAs, Stats -->
        <div class="col-lg-10 col-xl-9 text-center">
          <div class="hero-badge-pill">
            <i class="bi bi-stars text-warning"></i> Metro Manila's Premier Accommodations Guide
          </div>
          <h1 class="hero-heading">
            Discover Metro Manila's
            <span class="gradient-title">Hotels, Motels &amp; Rentals</span>
          </h1>
          <p class="hero-lead-text mx-auto">
            Explore 5-star hotels, express motels, Airbnbs, and staycations across Metro Manila. Book premier hotels by the night, or choose express hourly and overnight rates for motels.
          </p>

          <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
            <?php if ($isLoggedIn): ?>
              <a href="home.php" class="btn btn-lg btn-hero-primary px-5 py-3 rounded-pill fw-bold shadow-lg">
                <i class="bi bi-grid-fill me-2"></i> Browse All Stays & Bookings <i class="bi bi-arrow-right-short fs-4 align-middle"></i>
              </a>
              <a href="#hotel-infographic" class="btn btn-lg btn-hero-secondary px-4 py-3 rounded-pill fw-semibold">
                <i class="bi bi-journal-richtext me-2"></i> View Accommodations Guide ↓
              </a>
            <?php else: ?>
              <a href="auth.php?action=register" class="btn btn-lg btn-hero-primary px-5 py-3 rounded-pill fw-bold shadow-lg">
                <i class="bi bi-lightning-charge-fill me-2"></i> Get Started Free <i class="bi bi-arrow-right-short fs-4 align-middle"></i>
              </a>
              <a href="#hotel-infographic" class="btn btn-lg btn-hero-secondary px-4 py-3 rounded-pill fw-semibold">
                <i class="bi bi-stars me-2"></i> Explore Accommodations Guide ↓
              </a>
            <?php endif; ?>
          </div>

          <!-- Quick Sign-in Choices Directly in Hero -->
          <?php if (!$isLoggedIn): ?>
            <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mb-4">
              <span class="text-secondary small me-1">Quick Access:</span>
              <a href="auth.php?provider=google" class="hero-signin-option text-decoration-none" title="Quick Google / Gmail Sign-In">
                <i class="bi bi-google text-danger"></i> Google / Gmail
              </a>
              <a href="auth.php?provider=guest" class="hero-signin-option text-decoration-none" title="Instant Guest Pass (No Password Required)">
                <i class="bi bi-person-fill text-warning"></i> Continue as Guest
              </a>
            </div>
          <?php endif; ?>

          <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
            <span class="stat-metric-pill"><i class="bi bi-check-circle-fill text-success"></i> Luxury Hotels, Motels &amp; Condos</span>
            <span class="stat-metric-pill"><i class="bi bi-door-open-fill text-info"></i> 100+ Room Options</span>
            <span class="stat-metric-pill"><i class="bi bi-geo-alt-fill text-danger"></i> Metro Manila Focus</span>
            <span class="stat-metric-pill"><i class="bi bi-shield-check text-primary"></i> 100% Real-Time Status</span>
          </div>
        </div>

      </div>
    </div>
  </header>

  <!-- Hotel Promotional Infographic Showcase -->
  <section id="hotel-infographic" class="infographic-section container py-5">

    <!-- Trust Metric Strip -->
    <div class="row g-3 mb-5">
      <div class="col-6 col-md-3">
        <div class="trust-metric-box">
          <div class="fs-3 fw-bolder text-white">15,400+</div>
          <div class="text-secondary small fw-medium">Guests Hosted Across Manila</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="trust-metric-box">
          <div class="fs-3 fw-bolder text-info">₱1,850</div>
          <div class="text-secondary small fw-medium">Avg. Saved per Short Stay</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="trust-metric-box">
          <div class="fs-3 fw-bolder text-success">99.4%</div>
          <div class="text-secondary small fw-medium">Verified Hygiene Rating</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="trust-metric-box">
          <div class="fs-3 fw-bolder text-warning">2 Mins</div>
          <div class="text-secondary small fw-medium">Instant Walk-In Check-In</div>
        </div>
      </div>
    </div>

    <!-- Section Header & Filter Controls -->
    <div class="text-center mb-5">
      <div class="infographic-tag-pill">
        <i class="bi bi-stars"></i> Curated Stays & Visual Guide
      </div>
      <h2 class="fw-extrabold text-white display-6">
        World-Class Accommodations in <span class="gradient-title">Metro Manila</span>
      </h2>
      <p class="text-secondary small mx-auto" style="max-width: 650px;">
        High-definition visual preview of Metro Manila’s premier 5-star hotels, luxury presidential suites, and waterfront casino resorts with real-time guest analytics.
      </p>

      <!-- Category Filter Tabs -->
      <div class="d-flex justify-content-center gap-2 flex-wrap mt-3" id="infographicFilters">
        <button type="button" class="infographic-filter-btn active" data-filter="all">All Accommodations</button>
        <button type="button" class="infographic-filter-btn" data-filter="motel">Motels</button>
        <button type="button" class="infographic-filter-btn" data-filter="airbnb">AirBnBs</button>
        <button type="button" class="infographic-filter-btn" data-filter="rental">Long-Term Rentals</button>
      </div>
    </div>

    <!-- Infographic Cards Grid with Famous Hotels -->
    <div class="row g-4" id="infographicCardsGrid">

      <!-- Card 1: Hotel Sogo -->
      <div class="col-lg-4 col-md-6 infographic-card-item" data-category="motel">
        <div class="infographic-card">
          <div class="infographic-img-container">
            <img src="https://images.unsplash.com/photo-1596436889106-be35e843f974?auto=format&fit=crop&w=800&q=80" alt="Hotel Sogo" class="infographic-img" loading="lazy">
            <span class="infographic-badge-floating">
              <i class="bi bi-star-fill text-warning"></i> 4.1 Rating (1,200 Reviews)
            </span>
            <span class="infographic-promo-badge">
              <i class="bi bi-shield-check me-1"></i> Drive-In Ready
            </span>
          </div>
          <div class="infographic-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h4 class="text-white fw-bold mb-1 fs-5">Hotel Sogo Mabini</h4>
                <div class="text-secondary small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Manila • Walk-In Ready</div>
              </div>
              <div class="text-end">
                <div class="infographic-price-tag">₱450</div>
                <div class="text-secondary" style="font-size: 0.72rem;">Starting 3hr Rate</div>
              </div>
            </div>

            <!-- Visual Infographic Stat Meters -->
            <div class="my-3">
              <div class="infographic-metric-item">
                <div class="d-flex justify-content-between">
                  <span class="text-white-50"><i class="bi bi-eye text-info me-1"></i> Total Views</span>
                  <span class="text-info fw-semibold">1,280+ Live Views</span>
                </div>
                <div class="infographic-meter-bar">
                  <div class="infographic-meter-fill bg-info" style="width: 92%;"></div>
                </div>
              </div>
              <div class="infographic-metric-item">
                <div class="d-flex justify-content-between">
                  <span class="text-white-50"><i class="bi bi-bag-check text-warning me-1"></i> Total Bookings</span>
                  <span class="text-warning fw-semibold">450 Verified Reservations</span>
                </div>
                <div class="infographic-meter-bar">
                  <div class="infographic-meter-fill bg-warning" style="width: 88%;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-1 mb-4">
              <span class="stay-tag"><i class="bi bi-car-front-fill me-1"></i>Private Garage</span>
              <span class="stay-tag"><i class="bi bi-clock me-1"></i>24/7 Wash Rates</span>
            </div>

            <div class="mt-auto">
              <a href="home.php" class="btn btn-outline-info w-100 rounded-pill fw-semibold py-2">
                Explore Suites & Room Details <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 2: Shangri-La The Fort -->
      <div class="col-lg-4 col-md-6 infographic-card-item" data-category="hotel">
        <div class="infographic-card">
          <div class="infographic-img-container">
            <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80" alt="Shangri-La The Fort" class="infographic-img" loading="lazy">
            <span class="infographic-badge-floating">
              <i class="bi bi-building-fill text-info"></i> BGC Ultra Luxury
            </span>
            <span class="infographic-promo-badge">
              <i class="bi bi-stars me-1"></i> 4 Suite Categories
            </span>
          </div>
          <div class="infographic-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h4 class="text-white fw-bold mb-1 fs-5">Shangri-La The Fort</h4>
                <div class="text-secondary small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>BGC Taguig • Horizon Club</div>
              </div>
              <div class="text-end">
                <div class="infographic-price-tag">₱14,200</div>
                <div class="text-secondary" style="font-size: 0.72rem;">Starting Nightly Rate</div>
              </div>
            </div>

            <!-- Visual Infographic Stat Meters -->
            <div class="my-3">
              <div class="infographic-metric-item">
                <div class="d-flex justify-content-between">
                  <span class="text-white-50"><i class="bi bi-eye text-info me-1"></i> Total Views</span>
                  <span class="text-info fw-semibold">1,540+ Live Views</span>
                </div>
                <div class="infographic-meter-bar">
                  <div class="infographic-meter-fill bg-info" style="width: 96%;"></div>
                </div>
              </div>
              <div class="infographic-metric-item">
                <div class="d-flex justify-content-between">
                  <span class="text-white-50"><i class="bi bi-bag-check text-warning me-1"></i> Total Bookings</span>
                  <span class="text-warning fw-semibold">62 Verified Reservations</span>
                </div>
                <div class="infographic-meter-bar">
                  <div class="infographic-meter-fill bg-warning" style="width: 94%;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-1 mb-4">
              <span class="stay-tag"><i class="bi bi-dribbble me-1"></i>Kerry Sports</span>
              <span class="stay-tag"><i class="bi bi-gem me-1"></i>Horizon Club</span>
              <span class="stay-tag"><i class="bi bi-water me-1"></i>Infinity Pool</span>
            </div>

            <div class="mt-auto">
              <a href="home.php" class="btn btn-outline-info w-100 rounded-pill fw-semibold py-2">
                Explore Suites & Room Details <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 3: Okada Manila -->
      <div class="col-lg-4 col-md-6 infographic-card-item" data-category="casino">
        <div class="infographic-card">
          <div class="infographic-img-container">
            <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80" alt="Okada Manila" class="infographic-img" loading="lazy">
            <span class="infographic-badge-floating">
              <i class="bi bi-suit-spade-fill text-warning"></i> Integrated Casino Resort
            </span>
            <span class="infographic-promo-badge">
              <i class="bi bi-water me-1"></i> The Fountain Show
            </span>
          </div>
          <div class="infographic-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h4 class="text-white fw-bold mb-1 fs-5">Okada Manila</h4>
                <div class="text-secondary small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Entertainment City • Manila Bay</div>
              </div>
              <div class="text-end">
                <div class="infographic-price-tag">₱11,800</div>
                <div class="text-secondary" style="font-size: 0.72rem;">Starting Nightly Rate</div>
              </div>
            </div>

            <!-- Visual Infographic Stat Meters -->
            <div class="my-3">
              <div class="infographic-metric-item">
                <div class="d-flex justify-content-between">
                  <span class="text-white-50"><i class="bi bi-eye text-info me-1"></i> Total Views</span>
                  <span class="text-info fw-semibold">2,190+ Live Views</span>
                </div>
                <div class="infographic-meter-bar">
                  <div class="infographic-meter-fill bg-info" style="width: 100%;"></div>
                </div>
              </div>
              <div class="infographic-metric-item">
                <div class="d-flex justify-content-between">
                  <span class="text-white-50"><i class="bi bi-bag-check text-warning me-1"></i> Total Bookings</span>
                  <span class="text-warning fw-semibold">85 Verified Reservations</span>
                </div>
                <div class="infographic-meter-bar">
                  <div class="infographic-meter-fill bg-warning" style="width: 100%;"></div>
                </div>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-1 mb-4">
              <span class="stay-tag"><i class="bi bi-water me-1"></i>Fountain View</span>
              <span class="stay-tag"><i class="bi bi-disc me-1"></i>Cove Manila</span>
              <span class="stay-tag"><i class="bi bi-hot-tub me-1"></i>Jacuzzi Suite</span>
            </div>

            <div class="mt-auto">
              <a href="home.php" class="btn btn-outline-info w-100 rounded-pill fw-semibold py-2">
                Explore Suites & Room Details <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>


    <!-- Side-by-Side Infographic Comparison Banner -->
    <div class="comparison-banner">
      <div class="text-center mb-4">
        <h3 class="text-white fw-bold mb-2">The ComfortVue Advantage</h3>
        <p class="text-secondary small mb-0">See why savvy travelers save thousands of pesos on Metro Manila accommodations</p>
      </div>

      <div class="row g-4 align-items-stretch">
        <div class="col-md-6">
          <div class="comparison-card-bad">
            <div class="d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-x-circle-fill text-danger fs-4"></i>
              <h5 class="text-white fw-bold mb-0">Traditional Manila Hotels</h5>
            </div>
            <div class="comparison-item">
              <i class="bi bi-x text-danger fs-5 flex-shrink-0"></i>
              <span class="text-secondary">Mandatory 24-hour pricing even if you only need a quick 4-hour nap or shower.</span>
            </div>
            <div class="comparison-item">
              <i class="bi bi-x text-danger fs-5 flex-shrink-0"></i>
              <span class="text-secondary">Strict 2:00 PM check-in and 12:00 PM checkout; early check-in incur heavy penalty fees.</span>
            </div>
            <div class="comparison-item">
              <i class="bi bi-x text-danger fs-5 flex-shrink-0"></i>
              <span class="text-secondary">Front desk wait times of 15-30 minutes with paperwork and credit card deposits.</span>
            </div>
            <div class="comparison-item">
              <i class="bi bi-x text-danger fs-5 flex-shrink-0"></i>
              <span class="text-secondary">Unexpected 12% VAT and 10% service charges added on final bill.</span>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="comparison-card-good">
            <div class="d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-check-circle-fill text-success fs-4"></i>
              <h5 class="text-white fw-bold mb-0">ComfortVue Stays & Suites</h5>
            </div>
            <div class="comparison-item">
              <i class="bi bi-check-lg text-success fs-5 flex-shrink-0"></i>
              <span class="text-light">Flexible 3hr, 6hr, 12hr micro-blocks — save up to 65% on every stopover.</span>
            </div>
            <div class="comparison-item">
              <i class="bi bi-check-lg text-success fs-5 flex-shrink-0"></i>
              <span class="text-light">24/7 Any-Hour Walk-In ready whenever your flight arrives or schedule demands.</span>
            </div>
            <div class="comparison-item">
              <i class="bi bi-check-lg text-success fs-5 flex-shrink-0"></i>
              <span class="text-light">Instant digital keyless pass or private garage entry in under 2 minutes.</span>
            </div>
            <div class="comparison-item">
              <i class="bi bi-check-lg text-success fs-5 flex-shrink-0"></i>
              <span class="text-light">100% transparent pricing in Philippine Pesos with zero hidden surcharges.</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Interactive Stay Savings Calculator -->
    <div class="savings-calc-box">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-calculator-fill text-info fs-4"></i>
            <h4 class="text-white fw-bold mb-0">Live Stay Savings Estimator</h4>
          </div>
          <p class="text-secondary small mb-3">
            Select your required stopover duration to see how much you save with ComfortVue compared to standard Manila hotel rates:
          </p>

          <div class="d-flex gap-2 flex-wrap mb-3" id="calcDurationButtons">
            <button type="button" class="calc-chip-btn active" data-hours="3" data-price="950" data-standard="2800">3-Hour Transit Stop</button>
            <button type="button" class="calc-chip-btn" data-hours="6" data-price="1300" data-standard="3200">6-Hour Daytime Rest</button>
            <button type="button" class="calc-chip-btn" data-hours="12" data-price="1950" data-standard="3800">12-Hour Half-Day</button>
            <button type="button" class="calc-chip-btn" data-hours="24" data-price="2900" data-standard="4500">Overnight (24h)</button>
          </div>
          <div class="text-white-50" style="font-size: 0.78rem;">
            <i class="bi bi-info-circle text-info me-1"></i> Based on verified 2026 average rates across Makati, BGC, and Pasay hotel hubs.
          </div>
        </div>

        <div class="col-lg-5">
          <div class="calc-result-display">
            <div class="text-success small fw-bold text-uppercase letter-spacing-1 mb-1">
              Estimated Guest Savings
            </div>
            <div class="display-6 fw-extrabold text-white mb-1" id="calcSavingsAmount">₱1,850 Saved</div>
            <div class="text-light small mb-3" id="calcSavingsBreakdown">
              You pay <strong class="text-info">₱950</strong> instead of standard <span class="text-decoration-line-through text-white-50">₱2,800</span> (66% Cheaper)
            </div>
            <a href="auth.php?action=register" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow">
              <i class="bi bi-lightning-fill me-1"></i> Claim Your Stay Pass
            </a>
          </div>
        </div>
      </div>
    </div>

  </section>

  <!-- Category Showcase -->
  <section id="accommodations" class="container py-5">
    <div class="text-center mb-5">
      <h6 class="text-info text-uppercase fw-bold letter-spacing-1">Diverse Accommodations</h6>
      <h2 class="fw-extrabold text-white">Choose Your Desired Stay Option</h2>
      <p class="text-secondary small">From discreet quick-rest stopovers to luxury weekend high-rise penthouses</p>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-highlight-card">
          <div class="feature-icon-badge" style="background: rgba(200, 113, 74, 0.15); color: var(--terracotta);">
            <i class="bi bi-clock-history"></i>
          </div>
          <h4 class="text-white fw-bold mb-2">Hourly Motels & Inns</h4>
          <p class="text-secondary small mb-3">
            Designed for quick transit travelers, rest stopovers, and walk-in guests with flexible 3-hour, 6-hour, and 12-hour booking blocks.
          </p>
          <div class="d-flex flex-wrap gap-1 mb-4">
            <span class="stay-tag">Walk-In Ready</span>
            <span class="stay-tag">Private Garage</span>
            <span class="stay-tag">24/7 Check-in</span>
          </div>
          <a href="home.php" class="text-decoration-none text-info fw-semibold small">
            View Motel Pins <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="feature-highlight-card">
          <div class="feature-icon-badge" style="background: rgba(107, 143, 113, 0.15); color: var(--sage);">
            <i class="bi bi-building"></i>
          </div>
          <h4 class="text-white fw-bold mb-2">Condos & Airbnbs</h4>
          <p class="text-secondary small mb-3">
            Urban high-rise units situated across Makati and BGC Taguig featuring modern kitchenettes, infinity pools, and skyline views.
          </p>
          <div class="d-flex flex-wrap gap-1 mb-4">
            <span class="stay-tag">Self Check-in</span>
            <span class="stay-tag">Infinity Pool</span>
            <span class="stay-tag">High-Speed Wi-Fi</span>
          </div>
          <a href="home.php" class="text-decoration-none text-success fw-semibold small">
            View Condo Pins <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="feature-highlight-card">
          <div class="feature-icon-badge" style="background: rgba(212, 133, 42, 0.15); color: var(--amber);">
            <i class="bi bi-mortarboard-fill"></i>
          </div>
          <h4 class="text-white fw-bold mb-2">Function & Study Halls</h4>
          <p class="text-secondary small mb-3">
            Quiet co-working spaces, private acoustic study pods, and professional event conference halls equipped with projector rigs.
          </p>
          <div class="d-flex flex-wrap gap-1 mb-4">
            <span class="stay-tag">Silent Pods</span>
            <span class="stay-tag">Free Coffee</span>
            <span class="stay-tag">Audio-Visual Rig</span>
          </div>
          <a href="home.php" class="text-decoration-none text-primary fw-semibold small">
            View Facility Pins <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Technical Highlights -->
  <section id="features" class="container py-5 border-top border-secondary border-opacity-25">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="p-4 rounded-4 stay-card h-100">
          <i class="bi bi-layers-half text-primary fs-2 mb-3 d-inline-block"></i>
          <h5 class="text-white fw-bold">Multi-Layer Portable Map</h5>
          <p class="text-secondary small mb-0">Switch seamlessly between high-res Satellite World Imagery, Street navigation, and Dark Matter views without watermark limitations.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 rounded-4 stay-card h-100">
          <i class="bi bi-google text-danger fs-2 mb-3 d-inline-block"></i>
          <h5 class="text-white fw-bold">Google & Gmail SSO Ready</h5>
          <p class="text-secondary small mb-0">Single-click account access using simulated and native OAuth verification workflows for seamless guest reservations.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 rounded-4 stay-card h-100">
          <i class="bi bi-database-check text-success fs-2 mb-3 d-inline-block"></i>
          <h5 class="text-white fw-bold">MySQL Relational Backend</h5>
          <p class="text-secondary small mb-0">Complete inventory tables, booking records, user logs, and real-time custom coordinate pinning support.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="landing-footer text-center">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-bold text-white">ComfortVue Philippines</span>
        <span>• Famous Hotels & Nationwide Branches System</span>
      </div>
      <div class="d-flex align-items-center flex-wrap gap-3">
        <a href="home.php" class="text-secondary text-decoration-none hover-text-white">Explore Hotels & Suites</a>
        <a href="auth.php?action=login" class="text-secondary text-decoration-none hover-text-white">Sign In</a>
        <a href="auth.php?action=register" class="text-secondary text-decoration-none hover-text-white">Get Started</a>
        <a href="admin_login.php" class="text-secondary text-decoration-none hover-text-white text-opacity-75" title="Administrator Access"><i class="bi bi-shield-lock me-1"></i>Admin</a>
      </div>
    </div>
  </footer>

  <!-- Infographic Interactive Controls Script -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // 1. Infographic Filter Tabs
      const filterBtns = document.querySelectorAll('#infographicFilters .infographic-filter-btn');
      const cards = document.querySelectorAll('#infographicCardsGrid .infographic-card-item');

      filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          filterBtns.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');

          const category = btn.getAttribute('data-filter');
          cards.forEach(card => {
            if (category === 'all' || card.getAttribute('data-category') === category) {
              card.style.display = 'block';
            } else {
              card.style.display = 'none';
            }
          });
        });
      });

      // 2. Stay Savings Calculator
      const calcBtns = document.querySelectorAll('#calcDurationButtons .calc-chip-btn');
      const savingsAmtEl = document.getElementById('calcSavingsAmount');
      const breakdownEl = document.getElementById('calcSavingsBreakdown');

      calcBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          calcBtns.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');

          const price = parseInt(btn.getAttribute('data-price'), 10);
          const standard = parseInt(btn.getAttribute('data-standard'), 10);
          const saved = standard - price;
          const percent = Math.round((saved / standard) * 100);

          if (savingsAmtEl && breakdownEl) {
            savingsAmtEl.textContent = `₱${saved.toLocaleString()} Saved`;
            breakdownEl.innerHTML = `You pay <strong class="text-info">₱${price.toLocaleString()}</strong> instead of standard <span class="text-decoration-line-through text-white-50">₱${standard.toLocaleString()}</span> (${percent}% Cheaper)`;
          }
        });
      });
    });
  </script>
</body>
</html>