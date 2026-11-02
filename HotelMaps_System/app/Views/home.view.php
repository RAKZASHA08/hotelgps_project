<?php
// Privacy & Security: Ensure session cookies are secure and protected
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}
require_once dirname(__DIR__, 2) . '/db.php';

// Unique Session ID for client privacy & booking security
$currentSessionId = session_id();

// Check if user is logged in
$isLoggedIn = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') !== 'guest') && (strpos((string)$_SESSION['user_id'], 'guest_') !== 0);
$userName = $_SESSION['user_name'] ?? 'Guest Traveler';
$userEmail = $_SESSION['user_email'] ?? 'guest@comfortvue.ph';
$userRole = $_SESSION['role'] ?? 'guest';
$userPhone = $_SESSION['user_phone'] ?? '+63 917 555 4321';
$isVerified = false;
$isAdmin = ($userRole === 'admin') || (($_SESSION['user_email'] ?? '') === 'admin@comfortvue.ph');

if ($isLoggedIn && $pdo && !empty($userEmail)) {
    try {
        $stmt = $pdo->prepare("SELECT is_verified, phone, role FROM users WHERE email = ?");
        $stmt->execute([$userEmail]);
        $uRow = $stmt->fetch();
        if ($uRow) {
            $isVerified = (bool)($uRow['is_verified'] == 1);
            $_SESSION['is_verified'] = $isVerified ? 1 : 0;
            if (!empty($uRow['role'])) {
                $userRole = $uRow['role'];
                $_SESSION['role'] = $userRole;
            }
            if ($userRole === 'admin') {
                $isAdmin = true;
            }
            if (!empty($uRow['phone'])) {
                $userPhone = $uRow['phone'];
            }
        }
    } catch (\Exception $e) {
        $isVerified = !empty($_SESSION['is_verified']);
    }
} else {
    $isVerified = !empty($_SESSION['is_verified']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="StayFinder PH - Find motels, Airbnbs, and short-term rentals in Metro Manila.">
  <title>StayFinder PH - Metro Manila Motels, AirBnBs &amp; Rentals</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Lora:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <!-- Pannellum 360° Viewer -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
  
  <!-- JS Libraries: Vue 3 (local), Bootstrap 5, Leaflet, Axios (local) -->
  <script src="js/vendor/vue.global.prod.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="js/vendor/axios.min.js"></script>
  <!-- Pannellum JS (must load after DOM is available) -->
  <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div id="app" v-cloak>
  <!-- Top Navigation Header -->
  <header class="app-header">
    <div class="container-fluid px-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
      <a href="index.php" class="d-flex align-items-center gap-3 text-decoration-none">
        <div class="brand-logo-badge"><i class="bi bi-compass"></i></div>
        <div>
          <div class="d-flex align-items-center gap-2">
            <span class="fs-5 fw-bold tracking-tight" style="color: var(--mocha);">StayFinder PH</span>
            <span class="badge" style="background: rgba(200,113,74,0.12); color: var(--terracotta); border: 1px solid rgba(200,113,74,0.3); font-size: 0.68rem;">
              <i class="bi bi-patch-check-fill me-1"></i>Metro Manila
            </span>
          </div>
          <div style="font-size: 0.72rem; color: var(--text-muted);">Motels • Condo Staycations • Short-Term Rentals</div>
        </div>
      </a>

      <!-- Navigation & Action Controls -->
      <div class="d-flex align-items-center flex-wrap gap-2">
        <a href="dashboard.php" class="btn-nav-pill text-decoration-none"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <button class="btn-nav-pill active"><i class="bi bi-map"></i> Map</button>
        <button class="btn-nav-pill" @click="openBookingsModal">
          <i class="bi bi-journal-check"></i> My Bookings
          <span class="badge rounded-pill ms-1" style="background:var(--sage); font-size: 0.65rem;">{{ userBookings.length }} Active</span>
        </button>
        <a href="reception.php" class="btn-nav-pill text-decoration-none" title="Hotel Front Desk & Retailer Portal">
          <i class="bi bi-building-gear text-info"></i> Reception
        </a>

        <!-- Account Verification Badge / Action Button -->
        <template v-if="accountVerified">
          <span class="badge badge-account-verified px-3 py-2 shadow-sm">
            <i class="bi bi-patch-check-fill"></i> Account Verified
          </span>
        </template>
        <template v-else>
          <button class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" @click="openVerifyModal" title="Verify your actual account">
            <i class="bi bi-shield-exclamation text-warning"></i> Verify Account
          </button>
        </template>

        <!-- Theme Toggle Button (Dark / Light Mode) -->
        <button class="btn-nav-pill px-3" id="themeToggleBtn" title="Toggle Dark/Light Mode">
          <i class="bi bi-moon-stars text-warning"></i>
          <span class="d-none d-sm-inline fw-semibold ms-1" id="themeToggleLabel" style="font-size:0.78rem;">Theme</span>
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
                  <span v-if="accountVerified" class="badge badge-account-verified px-2 py-1" style="font-size:0.7rem;">
                    <i class="bi bi-patch-check-fill me-1"></i>Verified Traveler
                  </span>
                  <span v-else class="badge bg-warning text-dark" style="font-size:0.65rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i>Unverified Account
                  </span>
                </div>
              </div>
            </li>
            <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
            <li v-if="!accountVerified">
              <a class="dropdown-item text-warning" href="javascript:void(0)" @click="openVerifyModal">
                <i class="bi bi-shield-check me-2"></i>Verify Account with 6-Digit PIN
              </a>
            </li>
            <li>
              <a class="dropdown-item text-light" href="profile.php">
                <i class="bi bi-person-gear me-2"></i>Account &amp; Profile Settings
              </a>
            </li>
            <li>
              <a class="dropdown-item text-light" href="javascript:void(0)" @click="openBookingsModal">
                <i class="bi bi-journal-text me-2"></i>View My Reservations ({{ userBookings.length }})
              </a>
            </li>
            <li>
              <a class="dropdown-item text-warning" href="reception.php">
                <i class="bi bi-building-gear me-2 text-warning"></i>Hotel Front Desk / Retailer Portal
              </a>
            </li>
            <?php if ($userRole === 'guest'): ?>
              <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
              <li><a class="dropdown-item text-info" href="auth.php?action=register"><i class="bi bi-person-plus me-2"></i>Create Real Account</a></li>
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


  <!-- Streamlined Filter & Search Bar -->
  <section class="filter-bar">
    <div class="container-fluid px-3 px-md-4">
      <div class="d-flex flex-nowrap align-items-center gap-3 overflow-x-auto pb-2" style="scrollbar-width: none; -ms-overflow-style: none;">
        <!-- Search Input -->
        <div class="position-relative flex-grow-1" style="min-width: 220px; max-width: 320px;">
          <i class="bi bi-search position-absolute top-50 translate-middle-y text-secondary ms-3"></i>
          <input type="text" v-model="searchQuery" class="form-control search-input-box" placeholder="Search hotels, branch, suites...">
        </div>

        <!-- Category Filter Pills -->
        <div class="d-flex flex-nowrap align-items-center gap-2" style="overflow-x:auto; scrollbar-width:thin;">
          <button class="pill-filter-btn" :class="{ active: selectedCategory === 'all' && sortBy !== 'nearest' }" @click="selectedCategory = 'all'; if (sortBy === 'nearest') sortBy = 'featured';">
            <i class="bi bi-grid-fill"></i> All <span class="badge bg-secondary ms-1">{{ stays.length }}</span>
          </button>
          <!-- Nearest to GPS Location Filter Pill -->
          <button class="pill-filter-btn" :class="{ active: sortBy === 'nearest' }" @click="toggleNearMeSort" title="Sort accommodations nearest to your location">
            <i class="bi bi-geo-alt-fill text-danger"></i> Near Me <span class="badge bg-secondary ms-1">GPS</span>
          </button>
          <button class="pill-filter-btn" :class="{ active: selectedCategory === 'hotel' }" @click="selectedCategory = 'hotel'">
            <i class="bi bi-building text-warning"></i> Hotels 🏨 <span class="badge bg-secondary ms-1">{{ countHotels }}</span>
          </button>
          <!-- Commuter-Friendly Walk-in filter -->
          <button class="pill-filter-btn commuter-pill" :class="{ active: commuterOnly }" @click="commuterOnly = !commuterOnly" title="Show only walk-in hourly stays near transit">
            <i class="bi bi-train-front-fill"></i> Walk-in / Commuter
            <span class="badge ms-1" :class="commuterOnly ? 'bg-white text-terracotta' : 'bg-secondary'">{{ commuterCount }}</span>
          </button>
          <button class="pill-filter-btn" :class="{ active: selectedCategory === 'motel' }" @click="selectedCategory = 'motel'">
            <i class="bi bi-car-front-fill text-primary"></i> Motels &amp; Rentals 🚗 <span class="badge bg-secondary ms-1">{{ countMotelsAndRentals }}</span>
          </button>
          <button class="pill-filter-btn" :class="{ active: selectedCategory === 'airbnb' }" @click="selectedCategory = 'airbnb'">
            <i class="bi bi-key-fill text-info"></i> AirBnB / Condos 🔑 <span class="badge bg-secondary ms-1">{{ countAirbnbs }}</span>
          </button>
        </div>

        <!-- Quick Filters: Quick Locate Destination, District Select, Open Suites Toggle, Reset -->
        <div class="d-flex flex-nowrap align-items-center gap-2 ms-auto">
          <!-- Quick Destination Selector -->
          <div class="d-flex align-items-center gap-1">
            <i class="bi bi-crosshair2 text-info small"></i>
            <select v-model="selectedBranchId" class="form-select form-select-sm bg-dark text-light border-secondary" style="width: auto; max-width: 190px; font-size: 0.8rem;" @change="onBranchChange" title="Quick locate and zoom to any hotel destination">
              <option value="all">★ Locate Destination...</option>
              <option v-for="h in stays" :key="h.id" :value="h.id">{{ h.title }} ({{ h.branch_name }})</option>
            </select>
          </div>

          <!-- District Dropdown -->
          <div class="d-flex align-items-center gap-1">
            <i class="bi bi-geo-alt-fill text-danger small"></i>
            <select v-model="selectedDistrict" class="form-select form-select-sm bg-dark text-light border-secondary" style="width: auto; min-width: 150px; font-size: 0.8rem;">
              <option value="all">All Locations ({{ stays.length }})</option>
              <option v-for="dist in districtsList" :key="dist" :value="dist">
                {{ dist }} ({{ countByDistrict(dist) }})
              </option>
            </select>
          </div>

          <!-- Open Suites Only Toggle -->
          <button class="pill-filter-btn btn-sm" :class="{ active: onlyAvailableRooms }" @click="onlyAvailableRooms = !onlyAvailableRooms" title="Show branches with open room availability only">
            <i class="bi bi-door-open-fill text-success"></i> Available Only
          </button>

          <!-- Reset Filter -->
          <button class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" @click="resetFilters" title="Reset All Filters">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Main View Split (Cards Pane on Left, Portable Leaflet Map on Right) -->
  <main class="main-workspace">
    <div class="container-fluid h-100 p-0">
      <div class="row g-0 h-100 flex-column flex-lg-row flex-lg-nowrap">
        
        <!-- Cards List Column -->
        <div class="col-12 col-lg-5 col-xl-5 cards-pane">
          <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <div class="d-flex align-items-center gap-2">
              <span class="fw-bold text-white fs-6">Metro Manila Accommodations</span>
              <span class="badge rounded-pill bg-primary px-2 py-1">{{ filteredStays.length }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="text-secondary small d-none d-sm-inline"><i class="bi bi-arrow-down-up me-1"></i>Sort:</span>
              <select v-model="sortBy" class="form-select form-select-sm bg-dark text-light border-secondary" style="width: auto; font-size: 0.82rem;">
                <option value="nearest">📍 Nearest to My Location</option>
                <option value="featured">Featured / Best Match</option>
                <option value="rating">Rating: High to Low</option>
                <option value="views">Most Popular (Views)</option>
                <option value="bookings">Most Booked</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
              </select>
            </div>
          </div>

          <!-- 📍 Nearest & Convenient Recommended Stays Carousel Shelf -->
          <div v-if="nearestConvenientStays.length > 0" class="nearest-shelf-container">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill bg-danger px-2 py-1"><i class="bi bi-geo-alt-fill me-1"></i>Near You</span>
                <span class="fw-bold text-white small">Nearest Convenient Recommendations</span>
              </div>
              <button class="btn btn-xs text-white-50 p-0" style="font-size:0.75rem;" @click="toggleNearMeSort">
                {{ sortBy === 'nearest' ? '✓ Showing Nearest First' : 'Sort All by Distance →' }}
              </button>
            </div>
            <div class="nearest-scroll-track">
              <div v-for="rec in nearestConvenientStays" :key="'rec-' + rec.id" class="nearest-rec-card" @click="selectHotelCard(rec)">
                <div class="nearest-img-box position-relative">
                  <img :src="rec.image" :alt="rec.title" class="w-100 h-100 object-fit-cover">
                  <span class="distance-pill-badge position-absolute bottom-0 start-0 m-1" style="font-size:0.65rem;">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ getStayDistanceText(rec) }}
                  </span>
                  <span class="badge position-absolute top-0 end-0 m-1 bg-dark bg-opacity-75 text-warning" style="font-size:0.65rem;">
                    ★ {{ rec.rating }}
                  </span>
                </div>
                <div class="p-2">
                  <div class="fw-bold text-white text-truncate small mb-1" :title="rec.title">{{ rec.title }}</div>
                  <div class="text-secondary text-truncate" style="font-size:0.7rem;"><i class="bi bi-geo-alt me-1"></i>{{ rec.city }}</div>
                  <div class="d-flex align-items-center justify-content-between mt-1">
                    <span class="text-success fw-bold small">₱{{ Number(rec.price).toLocaleString() }}</span>
                    <button class="btn btn-xs btn-outline-info py-0 px-2" style="font-size:0.68rem;" @click.stop="openRoomDetailsModal(rec)">Suites</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Hotel Cards Stack -->
          <div class="d-flex flex-column gap-3 pb-5">
            <div v-for="stay in filteredStays" :key="stay.id" class="stay-card" :class="{ 'active-selected': selectedStay?.id === stay.id }" @click="selectHotelCard(stay)">
              <div class="row g-0 h-100">
                <!-- Hotel Image -->
                <div class="col-12 col-sm-4 position-relative">
                  <div class="stay-card-img-wrapper h-100" style="min-height: 180px;">
                    <img :src="stay.image" :alt="stay.title" class="stay-card-img">
                    <span class="badge-category-pill" :class="'badge-' + stay.category">{{ stay.categoryDisplay }}</span>
                    <span class="badge-rating-pill"><i class="bi bi-star-fill text-warning"></i> {{ stay.rating }}</span>
                    <span class="badge-availability" :class="stay.statusBadgeClass"><i class="bi bi-dot fs-6"></i> {{ stay.statusText }}</span>
                    <span v-if="stay.instant" class="badge-instant">Verified</span>
                  </div>
                </div>

                <!-- Hotel Info Body -->
                <div class="col-12 col-sm-8 p-3 d-flex flex-column justify-content-between">
                  <div>
                    <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-1">
                      <h6 class="text-white fw-bold mb-0 lh-sm fs-6">{{ stay.title }}</h6>
                      <span class="badge" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 0.7rem;">
                        <i class="bi bi-geo-alt-fill me-1"></i>{{ stay.branch_name }}
                      </span>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                      <span class="text-info fw-semibold" style="font-size: 0.72rem;">{{ stay.brand }}</span>
                      <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-30" style="font-size: 0.65rem;">{{ stay.city }}</span>
                      <!-- Distance badge from current location / origin -->
                      <span class="distance-pill-badge" :title="'Distance to ' + stay.title" @click.stop="startDirections(stay)">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ getStayDistanceText(stay) }}
                      </span>
                      <!-- Commuter transit badge (only for walk-in / hourly stays) -->
                      <span v-if="stay.nearest_terminal && stay.nearest_terminal !== 'Commuter Hub'" class="badge" style="background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); font-size:0.62rem;" :title="stay.transit_type + ' — ' + stay.walk_time">
                        <i class="bi bi-train-front-fill me-1"></i>{{ stay.walk_time }}
                      </span>
                    </div>
                    
                    <p class="text-secondary small mb-1 d-flex align-items-center gap-1" style="font-size: 0.76rem;">
                      <i class="bi bi-geo-alt text-danger"></i> {{ stay.address }}
                    </p>
                    <!-- Nearest terminal info for commuter-friendly spots -->
                    <p v-if="stay.nearest_terminal && stay.nearest_terminal !== 'Commuter Hub'" class="mb-1 d-flex align-items-center gap-1" style="font-size: 0.72rem; color: var(--sage);">
                      <i class="bi bi-train-front-fill"></i> {{ stay.nearest_terminal }}
                    </p>

                    <!-- Live Availability Badge -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <span :class="stay.availableRoomsCount > 0 ? 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25'" style="font-size: 0.72rem;">
                        <i class="bi" :class="stay.availableRoomsCount > 0 ? 'bi-door-open-fill' : 'bi-door-closed-fill'"></i>
                        {{ stay.availableRoomsCount }} of {{ stay.rooms?.length || 0 }} Suites Available
                      </span>
                    </div>

                    <!-- Amenities tags -->
                    <div class="d-flex flex-wrap gap-1 mb-2">
                      <span v-for="tag in (stay.tags || []).slice(0, 3)" :key="tag" class="stay-tag">{{ tag }}</span>
                    </div>
                  </div>

                  <!-- Rate & Action Buttons -->
                  <div class="pt-2 border-top border-secondary border-opacity-25 mt-auto">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                      <div>
                        <span class="text-secondary small d-block" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Starting From</span>
                        <div class="text-white fw-bold">
                          <span class="fs-5 text-white">₱{{ Number(stay.price).toLocaleString() }}</span>
                          <span class="text-secondary small"> / {{ stay.durationLabel }}</span>
                        </div>
                        <!-- Hourly rate quick-glance for motels/sogo -->
                        <div v-if="stay.hourly_rates && (stay.category === 'motel' || stay.category === 'sogo')" class="d-flex flex-wrap gap-1 mt-1">
                          <span v-if="stay.hourly_rates['2hr']" class="badge" style="background:rgba(200,113,74,0.12); color:var(--terracotta); border:1px solid rgba(200,113,74,0.3); font-size:0.6rem;">2hr ₱{{ stay.hourly_rates['2hr'] }}</span>
                          <span v-if="stay.hourly_rates['3hr']" class="badge" style="background:rgba(200,113,74,0.15); color:var(--terracotta); border:1px solid rgba(200,113,74,0.35); font-size:0.6rem;">3hr ₱{{ stay.hourly_rates['3hr'] }}</span>
                          <span v-if="stay.hourly_rates['6hr']" class="badge" style="background:rgba(200,113,74,0.12); color:var(--terracotta); border:1px solid rgba(200,113,74,0.3); font-size:0.6rem;">6hr ₱{{ stay.hourly_rates['6hr'] }}</span>
                          <span v-if="stay.hourly_rates['12hr']" class="badge" style="background:rgba(107,143,113,0.12); color:var(--sage); border:1px solid rgba(107,143,113,0.3); font-size:0.6rem;">12hr ₱{{ stay.hourly_rates['12hr'] }}</span>
                        </div>
                      </div>
                      <div class="d-flex flex-wrap gap-1">
                        <button class="btn btn-outline-info btn-sm px-2 text-nowrap d-flex align-items-center gap-1" @click.stop="locateDestination(stay)" title="Locate & Zoom to Destination on Map">
                          <i class="bi bi-crosshair2 text-info"></i> Locate
                        </button>
                        <button class="btn btn-primary btn-sm px-2 text-nowrap fw-semibold d-flex align-items-center gap-1 shadow-sm" @click.stop="openRoomDetailsModal(stay)">
                          <i class="bi bi-door-open-fill"></i> Suites & Book
                        </button>
                        <button class="btn btn-outline-secondary btn-sm px-2" @click.stop="startDirections(stay)" title="Show route on map">
                          <i class="bi bi-sign-turn-right-fill text-info"></i>
                        </button>
                        <button class="btn btn-sm px-2 border-danger" 
                                :class="savedLocations.includes(stay.id.toString()) ? 'btn-danger text-white' : 'btn-outline-danger border-opacity-50 text-danger'"
                                @click.stop="saveDestination(stay.id)" 
                                title="Save to Library">
                          <i class="bi" :class="savedLocations.includes(stay.id.toString()) ? 'bi-heart-fill' : 'bi-heart'"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredStays.length === 0" class="text-center py-5">
              <i class="bi bi-search text-secondary display-4 d-block mb-3"></i>
              <h5 class="text-white fw-bold">No hotels found</h5>
              <p class="text-secondary small mb-3">Try clearing search filters or selecting All Hotels</p>
              <button class="btn btn-sm btn-primary rounded-pill px-4" @click="resetFilters">Reset Filters</button>
            </div>
          </div>
        </div>

        <!-- Leaflet Portable Map Column -->
        <div class="col-12 col-lg-7 col-xl-7 map-pane-container">
          <div id="leafletMap"></div>

          <!-- Multi-Layer Switching Tools & GPS Location Control -->
          <div class="map-overlay-layers">
            <button class="map-layer-btn" :class="{ active: currentTileLayer === 'street' }" @click="switchMapLayer('street')">
              <i class="bi bi-map me-1"></i> Street
            </button>
            <button class="map-layer-btn" :class="{ active: currentTileLayer === 'satellite' }" @click="switchMapLayer('satellite')">
              <i class="bi bi-globe-americas me-1"></i> Satellite
            </button>
            <button class="map-layer-btn" :class="{ active: currentTileLayer === 'dark' }" @click="switchMapLayer('dark')">
              <i class="bi bi-moon-stars me-1"></i> Dark Map
            </button>
            <div class="vr bg-secondary opacity-50 my-1"></div>
            <!-- GPS User Geolocation Button -->
            <button class="map-layer-btn" :class="{ 'btn-locating': isLocatingUser }" @click="locateUserPosition" title="Detect GPS Location & Route from My Location">
              <i class="bi bi-crosshair me-1" :class="{ 'spin-icon': isLocatingUser }"></i>
              <span class="d-none d-sm-inline">{{ isLocatingUser ? 'Locating...' : 'Locate Me' }}</span>
              <span class="d-sm-none">GPS</span>
            </button>
          </div>

          <!-- GPS Minimized Directions Floating Capsule Pill -->
          <div v-if="directionsActive && directionsTarget && isDirectionsMinimized" class="gps-nav-minimized shadow-lg">
            <div class="d-flex align-items-center gap-2 cursor-pointer" @click="isDirectionsMinimized = false" title="Click to expand road directions">
              <span class="badge bg-primary rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1">
                <i :class="getModeIcon(selectedTravelMode)"></i>
                <span class="fw-bold">{{ activeModeData.etaFormatted }}</span>
              </span>
              <span class="text-white fw-bold small text-truncate" style="max-width: 170px;">
                {{ directionsTarget.title }}
              </span>
              <span class="text-secondary small d-none d-sm-inline">
                • {{ activeModeData.distanceFormatted }}
              </span>
              <span v-if="activeModeData.trafficDelayText" class="badge" :class="currentTrafficInfo.tagClass" style="font-size: 0.65rem;">
                {{ activeModeData.trafficDelayText }}
              </span>
            </div>
            <div class="d-flex align-items-center gap-1 ms-2">
              <button type="button" class="btn-hud-icon-btn" @click="isDirectionsMinimized = false" title="Expand Full Navigation HUD">
                <i class="bi bi-arrows-angle-expand"></i>
              </button>
              <button type="button" class="btn-hud-icon-btn btn-hud-close" @click="clearDirections" title="Close directions">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>

          <!-- GPS Map Navigation HUD & Multi-Modal Routing System -->
          <div v-if="directionsActive && directionsTarget && !isDirectionsMinimized" class="gps-nav-hud">
            <!-- Header: Title, Origin Selector, Status & Minimize / Close -->
            <div class="gps-nav-header">
              <div class="flex-grow-1 me-2 min-w-0">
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                  <span v-if="isCalculatingRoute" class="gps-status-badge">
                    <span class="spinner-border spinner-border-sm me-1" style="width: 10px; height: 10px;"></span> Calculating Shortest Path...
                  </span>
                  <span v-else-if="isLiveGpsTracking" class="gps-status-badge live-active">
                    <span class="gps-radar-indicator"></span> Live GPS Auto-Tracking Active
                  </span>
                  <span v-else class="gps-status-badge">
                    <i class="bi bi-shield-check me-1"></i> Shortest Road Route
                  </span>
                  <!-- Origin Selector Dropdown -->
                  <div class="dropdown d-inline-block">
                    <button class="btn btn-xs btn-outline-secondary dropdown-toggle rounded-pill py-0 px-2 text-light" style="font-size: 0.68rem;" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-geo-alt me-1 text-primary"></i>From: {{ getCurrentOriginLabel() }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size: 0.75rem;">
                      <li><h6 class="dropdown-header text-muted text-uppercase" style="font-size: 0.65rem;">Select Starting Point</h6></li>
                      <li><button class="dropdown-item" :class="{ active: originPreset === 'gps' }" @click="switchOriginPreset('gps')"><i class="bi bi-crosshair me-2 text-info"></i>My Current Live GPS</button></li>
                      <li><button class="dropdown-item" :class="{ active: originPreset === 'naia' }" @click="switchOriginPreset('naia')"><i class="bi bi-airplane me-2 text-warning"></i>NAIA Terminal 3 Airport</button></li>
                      <li><button class="dropdown-item" :class="{ active: originPreset === 'makati' }" @click="switchOriginPreset('makati')"><i class="bi bi-buildings me-2 text-success"></i>Makati CBD (Ayala)</button></li>
                      <li><button class="dropdown-item" :class="{ active: originPreset === 'manila' }" @click="switchOriginPreset('manila')"><i class="bi bi-bank me-2 text-primary"></i>Manila City Hall</button></li>
                      <li><button class="dropdown-item" :class="{ active: originPreset === 'bgc' }" @click="switchOriginPreset('bgc')"><i class="bi bi-geo-fill me-2 text-info"></i>BGC Taguig</button></li>
                    </ul>
                  </div>
                </div>
                <h6 class="text-white fw-bold mb-0 lh-sm text-truncate">{{ directionsTarget.title }}</h6>
                <div class="text-secondary small text-truncate" style="font-size: 0.72rem;">
                  <i class="bi bi-pin-map-fill text-danger me-1"></i>{{ directionsTarget.address }}
                </div>
              </div>
              <div class="d-flex align-items-center gap-1 ms-auto flex-shrink-0">
                <button type="button" class="btn-hud-icon-btn" @click="isDirectionsMinimized = true" title="Minimize directions to small pill">
                  <i class="bi bi-dash-lg"></i>
                </button>
                <button type="button" class="btn-hud-icon-btn btn-hud-close" @click="clearDirections" title="Close navigation">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </div>

            <!-- Travel Tool Mode Tabs (5 Modes) -->
            <div class="travel-mode-tabs">
              <!-- Car -->
              <button type="button" class="travel-mode-tab" :class="{ active: selectedTravelMode === 'car' }" @click="switchTravelMode('car')" title="Car / Automobile Road Route">
                <i class="bi bi-car-front-fill tab-icon"></i>
                <span class="tab-label">Car</span>
                <span class="tab-eta">{{ travelEstimates.car.etaFormatted }}</span>
              </button>
              <!-- Motorcycle -->
              <button type="button" class="travel-mode-tab" :class="{ active: selectedTravelMode === 'motorcycle' }" @click="switchTravelMode('motorcycle')" title="Motorcycle / Scooter Lane-Filtered Route">
                <i class="bi bi-speedometer2 tab-icon"></i>
                <span class="tab-label">Motorcycle</span>
                <span class="tab-eta">{{ travelEstimates.motorcycle.etaFormatted }}</span>
              </button>
              <!-- Bicycle -->
              <button type="button" class="travel-mode-tab" :class="{ active: selectedTravelMode === 'bicycle' }" @click="switchTravelMode('bicycle')" title="Bicycle / Cycling Route">
                <i class="bi bi-bicycle tab-icon"></i>
                <span class="tab-label">Bicycle</span>
                <span class="tab-eta">{{ travelEstimates.bicycle.etaFormatted }}</span>
              </button>
              <!-- Train -->
              <button type="button" class="travel-mode-tab" :class="{ active: selectedTravelMode === 'train', 'disabled-mode': !travelEstimates.train.available }" @click="switchTravelMode('train')" title="Metro Rail Transit / LRT / MRT">
                <i class="bi bi-train-front-fill tab-icon"></i>
                <span class="tab-label">Train</span>
                <span class="tab-eta">{{ travelEstimates.train.available ? travelEstimates.train.etaFormatted : 'N/A' }}</span>
              </button>
              <!-- Airplane -->
              <button type="button" class="travel-mode-tab" :class="{ active: selectedTravelMode === 'airplane', 'disabled-mode': !travelEstimates.airplane.available }" @click="switchTravelMode('airplane')" title="Commercial Air Flight">
                <i class="bi bi-airplane-fill tab-icon"></i>
                <span class="tab-label">Flight</span>
                <span class="tab-eta">{{ travelEstimates.airplane.available ? travelEstimates.airplane.etaFormatted : 'N/A' }}</span>
              </button>
            </div>

            <!-- Traffic Condition Selector Bar -->
            <div class="traffic-selector-row">
              <div class="d-flex align-items-center gap-1">
                <i class="bi bi-broadcast text-warning" style="font-size: 0.75rem;"></i>
                <span class="text-secondary small fw-bold" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.4px;">Traffic:</span>
              </div>
              <div class="traffic-pill-group">
                <button type="button" class="traffic-pill-btn" :class="{ active: trafficCondition === 'auto', 'traffic-auto': trafficCondition === 'auto' }" @click="setTrafficCondition('auto')" title="Auto-detect traffic delay by current local time of day">
                  <i class="bi bi-clock-history"></i> Auto-Live
                </button>
                <button type="button" class="traffic-pill-btn" :class="{ active: trafficCondition === 'light', 'traffic-light': trafficCondition === 'light' }" @click="setTrafficCondition('light')" title="Simulate light / off-peak traffic (+5% delay)">
                  <span class="traffic-dot" style="background: #10b981;"></span> Light
                </button>
                <button type="button" class="traffic-pill-btn" :class="{ active: trafficCondition === 'moderate', 'traffic-moderate': trafficCondition === 'moderate' }" @click="setTrafficCondition('moderate')" title="Simulate moderate daytime traffic (+35% delay)">
                  <span class="traffic-dot" style="background: #f59e0b;"></span> Moderate
                </button>
                <button type="button" class="traffic-pill-btn" :class="{ active: trafficCondition === 'heavy', 'traffic-heavy': trafficCondition === 'heavy' }" @click="setTrafficCondition('heavy')" title="Simulate heavy peak rush hour traffic (+85% delay)">
                  <span class="traffic-dot" style="background: #ef4444;"></span> Rush Hour
                </button>
                <button type="button" class="traffic-pill-btn" :class="{ active: trafficCondition === 'severe', 'traffic-severe': trafficCondition === 'severe' }" @click="setTrafficCondition('severe')" title="Simulate severe payday Friday / rainy gridlock (+130% delay)">
                  <span class="traffic-dot" style="background: #b91c1c;"></span> Gridlock
                </button>
              </div>
              <span class="traffic-delay-tag" :class="currentTrafficInfo.tagClass">
                {{ currentTrafficInfo.badgeText }}
              </span>
            </div>

            <!-- Active Mode Metric Card -->
            <div class="hud-metric-card">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="hud-metric-main">
                  <span class="hud-metric-time">{{ activeModeData.etaFormatted }}</span>
                  <span class="hud-metric-dist">• {{ activeModeData.distanceFormatted }}</span>
                  <span class="hud-badge-tag"><i class="bi bi-info-circle-fill me-1"></i>{{ activeModeData.badge }}</span>
                </div>
                <div class="d-flex align-items-center gap-1 flex-wrap">
                  <span v-if="activeModeData.trafficDelayText" class="badge" :class="currentTrafficInfo.tagClass" style="font-size: 0.68rem; border: 1px solid rgba(0,0,0,0.1);">
                    <i class="bi bi-hourglass-split me-1"></i>{{ activeModeData.trafficDelayText }}
                  </span>
                  <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-30 small" style="font-size: 0.68rem;">
                    <i class="bi bi-speedometer me-1"></i>{{ activeModeData.speed || 'Est. Speed' }}
                  </span>
                  <span v-if="selectedTravelMode === 'bicycle' && travelEstimates.bicycle.calories" class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 small" style="font-size: 0.68rem;">
                    <i class="bi bi-fire me-1"></i>{{ travelEstimates.bicycle.calories }} kcal
                  </span>
                </div>
              </div>
              <!-- Traffic Range Subtitle -->
              <div v-if="activeModeData.rangeText" class="text-secondary small mt-1" style="font-size: 0.72rem;">
                <i class="bi bi-reception-3 me-1 text-info"></i>{{ activeModeData.rangeText }}
              </div>
              <div class="hud-insight-text text-truncate" :title="activeModeData.insight" style="cursor: pointer;">
                <i class="bi bi-info-circle me-1 text-info"></i>{{ activeModeData.insight }}
              </div>
            </div>

            <!-- HUD Action Buttons & Navigation Deep Links -->
            <div class="hud-actions-row">
              <button type="button" class="btn btn-hud-tool" :class="isLiveGpsTracking ? 'btn-success text-white' : 'btn-outline-success'" @click="toggleLiveGpsTracking" title="Toggle automatic GPS position tracking">
                <span v-if="isLiveGpsTracking" class="gps-radar-indicator me-1"></span>
                <i v-else class="bi bi-crosshair me-1"></i>
                {{ isLiveGpsTracking ? 'Tracking ON' : 'Auto-Track GPS' }}
              </button>
              <button type="button" class="btn btn-hud-tool btn-outline-info" @click="showTurnSteps = !showTurnSteps" :disabled="turnByTurnSteps.length === 0" title="Toggle turn-by-turn road steps">
                <i class="bi bi-sign-turn-right me-1"></i> Steps ({{ turnByTurnSteps.length }})
              </button>
              <button type="button" class="btn btn-hud-tool btn-outline-light" @click="zoomToDestination(directionsTarget)">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Goal
              </button>
              <button type="button" class="btn btn-hud-tool btn-outline-light" @click="zoomToOrigin">
                <i class="bi bi-crosshair text-info me-1"></i> Start
              </button>
              <button type="button" class="btn btn-hud-tool btn-outline-secondary" @click="fitRouteBounds">
                <i class="bi bi-arrows-fullscreen me-1"></i> Fit
              </button>
              <div class="ms-auto d-flex gap-1">
                <a :href="getGoogleMapsUrl()" target="_blank" class="btn btn-hud-tool btn-outline-secondary" title="Open shortest route in Google Maps app">
                  <i class="bi bi-box-arrow-up-right me-1 text-primary"></i> G-Maps
                </a>
                <a :href="getWazeUrl()" target="_blank" class="btn btn-hud-tool btn-outline-secondary" title="Open shortest route in Waze app">
                  <i class="bi bi-signpost-2 me-1 text-warning"></i> Waze
                </a>
              </div>
            </div>

            <!-- Turn-by-Turn Guidance Drawer (Expandable) -->
            <div v-if="showTurnSteps && turnByTurnSteps.length > 0" class="turn-steps-drawer mt-2">
              <div class="d-flex justify-content-between align-items-center mb-1 text-secondary px-1" style="font-size: 0.7rem; font-weight: 700;">
                <span>TURN-BY-TURN ROAD DIRECTIONS</span>
                <span class="text-info cursor-pointer" @click="showTurnSteps = false"><i class="bi bi-chevron-up"></i> Collapse</span>
              </div>
              <div v-for="(step, sIdx) in turnByTurnSteps" :key="sIdx" class="turn-step-item">
                <div class="turn-step-icon"><i class="bi" :class="step.icon"></i></div>
                <div class="turn-step-text">{{ step.instruction }}</div>
                <div class="turn-step-meta">{{ step.distText }} • {{ step.durationText }}</div>
              </div>
            </div>
          </div>

          <!-- Map Legend Bar -->
          <div class="map-legend-bar">
            <button class="btn btn-sm btn-outline-light rounded-pill px-2 py-0 border-0" @click="fitAllPins" title="Fit all pins in view">
              <i class="bi bi-fullscreen me-1 text-primary"></i> <span style="font-size: 0.75rem;">Fit Pins</span>
            </button>
            <div class="vr bg-secondary opacity-50 my-1"></div>
            <div class="d-flex align-items-center gap-1 small text-light" style="font-size: 0.75rem;">
              <span class="rounded-circle d-inline-block" style="width: 9px; height: 9px; background: #E27D60;"></span> Motels
            </div>
            <div class="d-flex align-items-center gap-1 small text-light" style="font-size: 0.75rem;">
              <span class="rounded-circle d-inline-block" style="width: 9px; height: 9px; background: #38bdf8;"></span> AirBnB
            </div>
            <div class="d-flex align-items-center gap-1 small text-light" style="font-size: 0.75rem;">
              <span class="rounded-circle d-inline-block" style="width: 9px; height: 9px; background: #8FBC8F;"></span> Rentals
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <!-- ============================================== -->
  <!-- MODAL 1: HOTEL ROOM DETAILS MODAL PER HOTEL    -->
  <!-- ============================================== -->
  <div class="modal fade" id="roomDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content modal-content-dark">
        <div class="modal-header border-bottom border-secondary border-opacity-25" v-if="roomDetailsHotel">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
              <span class="badge" :class="'badge-' + roomDetailsHotel.category">{{ roomDetailsHotel.categoryDisplay }}</span>
              <span class="badge" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4);"><i class="bi bi-geo-alt-fill me-1"></i>{{ roomDetailsHotel.branch_name }}</span>
              <span class="text-warning small fw-bold"><i class="bi bi-star-fill"></i> {{ roomDetailsHotel.rating }}</span>
              <span class="text-secondary small">• {{ roomDetailsHotel.city }}</span>
            </div>
            <h4 class="modal-title fw-bold text-white">{{ roomDetailsHotel.title }}</h4>
            <div class="text-secondary small d-flex align-items-center gap-2 flex-wrap">
              <span><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ roomDetailsHotel.address }}</span>
              <span>•</span>
              <span class="font-monospace text-info small"><i class="bi bi-crosshair me-1"></i>{{ Number(roomDetailsHotel.lat).toFixed(6) }}, {{ Number(roomDetailsHotel.lng).toFixed(6) }}</span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-sm" @click="routeFromModal(roomDetailsHotel)" title="Get shortest road route and travel estimations">
              <i class="bi bi-sign-turn-right-fill me-1"></i> Road Directions
            </button>
            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-bold shadow-sm" @click="streetViewActive = !streetViewActive" title="View Street Map">
              <i class="bi bi-geo-alt-fill me-1"></i> Street View
            </button>
            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1" @click="locateFromModal(roomDetailsHotel)" title="Close modal and zoom to this hotel on map">
              <i class="bi bi-crosshair2 me-1"></i> Locate on Map
            </button>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" @click="destroyPannellum"></button>
          </div>
        </div>
        <div class="modal-body" v-if="roomDetailsHotel">
          
          <!-- Street View Iframe Container -->
          <div v-if="streetViewActive" class="mb-4 rounded-4 overflow-hidden border border-warning position-relative shadow-sm" style="height: 420px; background: #e5e3df;">
            <div class="position-absolute top-50 start-50 translate-middle text-secondary z-0"><i class="bi bi-compass fs-1"></i></div>
            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 z-3 rounded-circle shadow-sm" @click="streetViewActive = false"><i class="bi bi-x-lg"></i></button>
            <iframe class="position-relative z-1" :src="`https://maps.google.com/maps?q=&layer=c&cbll=${roomDetailsHotel.lat},${roomDetailsHotel.lng}&cbp=11,0,0,0,0&output=svembed`" width="100%" height="100%" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
          </div>
          
          <!-- 360° Virtual Tour Section (Tabbed: CSS-pan | Pannellum) -->
          <div v-if="roomDetailsHotel.showroom && roomDetailsHotel.showroom.rooms" class="mb-4">
            <!-- Section header with mode toggle -->
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <h6 class="fw-bold mb-0 d-flex align-items-center gap-2" style="color:var(--mocha)">
                <i class="bi bi-badge-3d" style="color:var(--terracotta)"></i> 360° Virtual Tour
              </h6>
              <div class="d-flex gap-1">
                <button class="pano-room-btn" :class="{ active: !pannellumActive }" @click.stop="destroyPannellum()">
                  <i class="bi bi-image me-1"></i>Interactive Preview
                </button>
                <button class="pano-room-btn" :class="{ active: pannellumActive }" @click.stop="initPannellum()">
                  <i class="bi bi-vr me-1"></i>Full Spherical 360°
                </button>
              </div>
            </div>

            <!-- TAB A: CSS drag-pan preview (fast, responsive) -->
            <div v-if="!pannellumActive" class="showroom-viewer-wrapper position-relative"
                 id="showroomViewerWrapper"
                 @pointerdown="startPanoDrag"
                 @pointermove="onPanoDrag"
                 @pointerup="endPanoDrag"
                 @pointercancel="endPanoDrag"
                 @mouseenter="pausePanoAutoPan"
                 @mouseleave="resumePanoAutoPan"
                 @wheel.prevent="onPanoWheel"
                 @dblclick="resetPanoView">

              <!-- Pano Canvas Stage (Image + Hotspots transform together so door stays locked under button!) -->
              <div class="showroom-pano-stage"
                   :style="{ transform: 'scale(' + panoZoom + ') translateX(' + panoPanX + 'px)' }">
                <img :src="currentPanoRoom.image_360" class="showroom-pano-canvas" :class="{ loading: panoLoading }" draggable="false">

                <!-- Steady Door Portal Buttons anchored to room stage -->
                <button v-for="(spot, idx) in currentPanoRoom.hotspots" :key="'hs'+idx"
                        class="pano-portal-btn shadow-lg"
                        :style="{ left: spot.x + '%', top: spot.y + '%' }"
                        @pointerdown.stop @mousedown.stop
                        @click.stop="switchPanoRoom(spot.target)"
                        type="button"
                        :title="'Enter ' + spot.label">
                  <span class="portal-badge"><i class="bi bi-door-open-fill"></i></span>
                  <span class="portal-name">{{ spot.label }}</span>
                  <i class="bi bi-chevron-right portal-arrow"></i>
                </button>
              </div>

              <!-- Top Room Switcher Pills -->
              <div class="pano-controls">
                <button v-for="(rm, key) in roomDetailsHotel.showroom.rooms" :key="key"
                        class="pano-room-btn shadow-sm" :class="{ active: currentPanoKey === key }"
                        @pointerdown.stop @mousedown.stop
                        @click.stop="switchPanoRoom(key)">
                  {{ rm.title }}
                </button>
              </div>

              <!-- Floating Control Toolbar for Tab A with Walk-Through Quick Buttons -->
              <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 z-3 d-flex align-items-center gap-1 px-3 py-1 rounded-pill shadow"
                   style="background:rgba(18,20,24,0.92); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.2);">
                <span class="text-white small fw-bold me-1 opacity-75 d-none d-md-inline" style="font-size:0.75rem;">
                  <i class="bi bi-door-open me-1"></i>Walk Through:
                </span>
                <button v-for="(rm, key) in roomDetailsHotel.showroom.rooms" :key="'tb_dock_'+key"
                        class="btn btn-xs rounded-pill px-2 py-1 fw-bold text-nowrap"
                        :style="{
                          background: currentPanoKey === key ? 'var(--terracotta)' : 'rgba(255,255,255,0.12)',
                          color: '#fff',
                          border: currentPanoKey === key ? '1px solid #fff' : '1px solid rgba(255,255,255,0.2)'
                        }"
                        @pointerdown.stop @mousedown.stop
                        @click.stop="switchPanoRoom(key)">
                  {{ rm.title }}
                </button>
                <div class="vr bg-secondary opacity-50 mx-1" style="height:16px;"></div>
                <button class="btn btn-xs text-white rounded-circle p-1"
                        :style="{ background: panoAutoRotating ? 'var(--terracotta)' : 'transparent' }"
                        @click.stop="togglePanoAutoPan"
                        :title="panoAutoRotating ? 'Pause Auto-Pan' : 'Play Auto-Pan'">
                  <i class="bi" :class="panoAutoRotating ? 'bi-pause-fill' : 'bi-play-fill'" style="font-size:0.95rem;"></i>
                </button>
                <button class="btn btn-xs text-white rounded-circle p-1" @click.stop="zoomPanoPreview(0.2)" title="Zoom In (+)">
                  <i class="bi bi-zoom-in" style="font-size:0.9rem;"></i>
                </button>
                <button class="btn btn-xs text-white rounded-circle p-1" @click.stop="zoomPanoPreview(-0.2)" title="Zoom Out (-)">
                  <i class="bi bi-zoom-out" style="font-size:0.9rem;"></i>
                </button>
                <button class="btn btn-xs text-white rounded-circle p-1" @click.stop="resetPanoView" title="Reset View">
                  <i class="bi bi-arrow-counterclockwise" style="font-size:0.9rem;"></i>
                </button>
                <button class="btn btn-xs text-white rounded-circle p-1" @click.stop="togglePreviewFullscreen" title="Fullscreen">
                  <i class="bi bi-arrows-fullscreen" style="font-size:0.8rem;"></i>
                </button>
              </div>

              <div class="pano-hint" :class="{ hidden: panoPanX !== 0 || panoLoading }">
                <i class="bi bi-arrows-move me-1"></i> Drag to look around · Mouse wheel to zoom · Click hotspots to move
              </div>
              <div class="pano-loading" :class="{ done: !panoLoading }">
                <div class="pano-spinner"></div>
              </div>
              <div class="pano-room-label">{{ currentPanoRoom.title }}</div>
            </div>

            <!-- TAB B: Full Pannellum 360 viewer (real spherical, hotspot nav, synced with Leaflet) -->
            <div v-if="pannellumActive">
              <!-- Scene switcher row - dynamically generated from active stay's showroom -->
              <div class="d-flex gap-2 mb-2 flex-wrap">
                <button v-for="(cfg, sceneId) in activePannellumScenes" :key="sceneId"
                        class="pano-room-btn shadow-sm" :class="{ active: pannellumCurrentScene === sceneId }"
                        @click.stop="switchPannellumScene(sceneId)">
                  <i class="bi bi-compass me-1"></i>{{ cfg.title }}
                </button>
              </div>
              <!-- Pannellum mounts here via initPannellum() -->
              <div class="position-relative">
                <div id="pannellum-container" style="width:100%;height:420px;border-radius:var(--r-lg);overflow:hidden;border:2px solid var(--border);"></div>
                
                <!-- Controls dock inside Tab B -->
                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 z-3 d-flex align-items-center gap-1 px-3 py-1 rounded-pill shadow"
                     style="background:rgba(18,20,24,0.92); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.2);">
                  <span class="text-white small fw-bold me-1 opacity-75 d-none d-md-inline" style="font-size:0.75rem;">
                    <i class="bi bi-door-open me-1"></i>Walk Through:
                  </span>
                  <button v-for="(cfg, sKey) in activePannellumScenes" :key="'tb_dock_b_'+sKey"
                          class="btn btn-xs rounded-pill px-2 py-1 fw-bold text-nowrap"
                          :style="{
                            background: pannellumCurrentScene === sKey ? 'var(--terracotta)' : 'rgba(255,255,255,0.12)',
                            color: '#fff',
                            border: pannellumCurrentScene === sKey ? '1px solid #fff' : '1px solid rgba(255,255,255,0.2)'
                          }"
                          @click.stop="switchPannellumScene(sKey)">
                    {{ cfg.title }}
                  </button>
                  <div class="vr bg-secondary opacity-50 mx-1" style="height:16px;"></div>
                  <button class="btn btn-xs text-white rounded-circle p-1"
                          :style="{ background: pannellumAutoRotating ? 'var(--terracotta)' : 'transparent' }"
                          @click.stop="togglePannellumAutoRotate"
                          :title="pannellumAutoRotating ? 'Pause Rotation (Space)' : 'Play Rotation (Space)'">
                    <i class="bi" :class="pannellumAutoRotating ? 'bi-pause-fill' : 'bi-play-fill'" style="font-size:1rem;"></i>
                  </button>
                  <button class="btn btn-xs text-white rounded-circle p-1" @click.stop="zoomPannellum(-10)" title="Zoom In (+)">
                    <i class="bi bi-zoom-in" style="font-size:0.95rem;"></i>
                  </button>
                  <button class="btn btn-xs text-white rounded-circle p-1" @click.stop="zoomPannellum(10)" title="Zoom Out (-)">
                    <i class="bi bi-zoom-out" style="font-size:0.95rem;"></i>
                  </button>
                  <button class="btn btn-xs text-white rounded-circle p-1" @click.stop="resetPannellumView" title="Reset View (H)">
                    <i class="bi bi-compass" style="font-size:0.95rem;"></i>
                  </button>
                  <div class="vr bg-secondary opacity-50 mx-1" style="height:16px;"></div>
                  <button class="btn btn-xs text-white rounded-circle p-1" @click.stop="togglePannellumFullscreen" title="Fullscreen (F)">
                    <i class="bi bi-arrows-fullscreen" style="font-size:0.85rem;"></i>
                  </button>
                </div>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap gap-2">
                <span class="small" style="color:var(--text-muted)">
                  <i class="bi bi-info-circle me-1"></i>
                  Click <strong>arrow hotspots</strong> inside the viewer to walk between rooms. Map &amp; scenes sync automatically.
                </span>
                <span class="badge" style="background:rgba(200,113,74,0.12);color:var(--terracotta);border:1px solid rgba(200,113,74,0.3);">
                  <i class="bi bi-link-45deg me-1"></i>Synced with Map
                </span>
              </div>
            </div>
          </div>

          <!-- Room list grid -->
          <div class="mb-3 d-flex justify-content-between align-items-center">
            <h6 class="text-white fw-bold mb-0" style="color:var(--mocha)!important">
              <i class="bi bi-door-open-fill text-primary me-2"></i>Available Suites &amp; Rooms
            </h6>
            <span class="text-secondary small">Live room status tracked in real-time</span>
          </div>

          <div class="row g-3">
            <div v-for="room in roomDetailsHotel.rooms" :key="room.id" class="col-12 col-md-6 col-lg-6">
              <div class="room-detail-card h-100 d-flex flex-column">
                <div class="room-detail-img-wrapper">
                  <img :src="room.image" :alt="room.room_name" class="room-detail-img">
                  <span class="badge bg-dark bg-opacity-75 text-light position-absolute top-0 start-0 m-2 border border-secondary border-opacity-50">
                    {{ room.room_type }}
                  </span>
                  <!-- Current Room Status Badge -->
                  <span class="badge position-absolute top-0 end-0 m-2" :class="getRoomStatusBadgeClass(room.status)">
                    <i class="bi me-1" :class="getRoomStatusIcon(room.status)"></i>{{ room.status }}
                  </span>
                </div>
                <div class="p-3 d-flex flex-column flex-grow-1 justify-content-between">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1 flex-wrap">
                      <h5 class="text-white fw-bold mb-0 fs-6">{{ room.room_name }}</h5>
                      <span v-if="room.room_number" class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 font-monospace px-2 py-1" style="font-size:0.75rem;">
                        <i class="bi bi-door-closed-fill me-1"></i>{{ room.room_number }}
                      </span>
                    </div>

                    <div class="d-flex align-items-center gap-1 mb-2" style="cursor:pointer;" @click.stop="openRoomReviewsModal(room)" title="Click to Read / Write Review">
                      <div class="text-warning small" style="font-size:0.75rem;">
                        <i class="bi" v-for="n in 5" :key="'sc_'+room.id+'_'+n" :class="getStarClass(room.avg_rating, n)"></i>
                      </div>
                      <span class="text-secondary ms-1" style="font-size:0.75rem;">
                        {{ Number(room.avg_rating).toFixed(1) }} ({{ room.review_count }} Reviews)
                      </span>
                    </div>
                    
                    <!-- Specifications -->
                    <div class="d-flex align-items-center gap-2 text-secondary small mb-2 flex-wrap" style="font-size:0.75rem;">
                      <span><i class="bi bi-aspect-ratio text-info me-1"></i>{{ room.size_sqm }} sqm</span>
                      <span>•</span>
                      <span><i class="bi bi-hdd-stack text-warning me-1"></i>{{ room.bed_type }}</span>
                      <span>•</span>
                      <span><i class="bi bi-people text-success me-1"></i>{{ room.capacity }}</span>
                    </div>

                    <!-- Amenities summary -->
                    <p class="text-white-50 small mb-3" style="font-size:0.76rem; line-height:1.4;">
                      <i class="bi bi-check-circle text-success me-1"></i>{{ room.amenities }}
                    </p>
                  </div>

                  <div class="pt-3 px-3 pb-3 border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between mt-auto">
                    <div>
                      <div class="text-secondary" style="font-size:0.65rem; font-weight:700;">NIGHTLY RATE</div>
                      <div class="text-white fw-bold fs-5">₱{{ Number(room.price).toLocaleString() }}</div>
                      <div class="d-flex align-items-center gap-2 mt-1">
                        <div class="text-secondary" style="font-size:0.68rem;">
                          <i class="bi bi-ticket-perforated me-1 text-info"></i>{{ room.total_booked }} / {{ room.booking_limit || 10 }} Booked
                        </div>
                        <span v-if="room.total_booked >= (room.booking_limit || 10) || room.status === 'Sold Out'" class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50" style="font-size:0.65rem;">
                          <i class="bi bi-slash-circle me-1"></i>Sold Out
                        </span>
                        <span v-else-if="(room.booking_limit || 10) - room.total_booked <= 2" class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50" style="font-size:0.65rem;">
                          <i class="bi bi-fire me-1"></i>Only {{ (room.booking_limit || 10) - room.total_booked }} left!
                        </span>
                        <span v-else class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50" style="font-size:0.65rem;">
                          {{ (room.booking_limit || 10) - room.total_booked }} slots left
                        </span>
                      </div>
                    </div>
                    
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <button class="btn btn-outline-info px-3 py-2 fw-semibold rounded-3 shadow-sm" @click="openRoom360Modal(room)" title="View 360° Virtual Room">
                        <i class="bi bi-vr me-1"></i> 360° View
                      </button>
                      <!-- Action Button Depending on Current Room Status & Booking Limit -->
                      <template v-if="room.status === 'Available' && room.total_booked < (room.booking_limit || 10)">
                        <button class="btn btn-primary px-3 py-2 fw-semibold rounded-3 shadow-sm" @click="bookSpecificRoom(room)">
                          <i class="bi bi-calendar-check me-1"></i> Reserve Suite
                        </button>
                      </template>
                      <template v-else-if="room.total_booked >= (room.booking_limit || 10) || room.status === 'Sold Out'">
                        <button class="btn btn-outline-danger px-3 py-2 fw-semibold rounded-3" disabled title="This room type has reached its official booking limit">
                          <i class="bi bi-lock-fill me-1"></i> Fully Booked
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn btn-outline-secondary px-3 py-2 fw-semibold rounded-3" disabled title="This room suite is currently not available">
                          <i class="bi bi-slash-circle me-1"></i> {{ room.status }}
                        </button>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  <!-- ============================================== -->
  <!-- MODAL 1.5: 360 ROOM VIEW MODAL                 -->
  <!-- ============================================== -->
  <div class="modal fade" id="room360Modal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down">
      <div class="modal-content modal-content-dark bg-dark border-secondary shadow-2xl" style="border-radius:var(--r-lg); overflow:hidden;">
        <!-- Header -->
        <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3" style="background:rgba(18, 20, 24, 0.95);" v-if="active360Room">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge rounded-pill" style="background:rgba(200,113,74,0.2); color:var(--terracotta); border:1px solid rgba(200,113,74,0.4);">
              <i class="bi bi-vr me-1"></i>360° Virtual Tour
            </span>
            <h5 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
              {{ active360Room.room_name }}
            </h5>
            <span class="badge bg-secondary rounded-pill opacity-75 small" v-if="active360Room.bed_type">
              {{ active360Room.bed_type }}
            </span>
            <span class="badge rounded-pill" style="background:rgba(122,158,126,0.2); color:var(--sage); border:1px solid rgba(122,158,126,0.3);">
              ₱{{ Number(active360Room.price).toLocaleString() }} <span class="small opacity-75">/ {{ active360Room.duration || 'Stay' }}</span>
            </span>
          </div>

          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm fw-bold rounded-pill px-3 shadow-sm"
                    style="background:var(--terracotta); color:#fff; border:none;"
                    @click="bookFrom360Modal"
                    title="Reserve this room">
              <i class="bi bi-calendar-check me-1"></i>Book Room
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-circle"
                    @click="toggleModalFullscreen"
                    title="Toggle Fullscreen (F)">
              <i class="bi bi-fullscreen text-white"></i>
            </button>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" title="Close (Esc)"></button>
          </div>
        </div>

        <!-- Body / 360 Viewer Canvas -->
        <div class="modal-body p-0 position-relative" style="height: 65vh; min-height: 440px; background:#0b0c0e;">
          <!-- Pannellum Container -->
          <div id="panoContainer" style="width: 100%; height: 100%;"></div>


          <!-- Loading Spinner Overlay -->
          <div v-if="modal360Loading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center"
               style="background:rgba(11,12,14,0.85); z-index:25; pointer-events:none;">
            <div class="spinner-border text-primary mb-2" role="status" style="width:3rem; height:3rem; border-width:3px; color:var(--terracotta)!important;"></div>
            <span class="text-white small fw-semibold tracking-wide">Loading 360° Panorama...</span>
          </div>

          <!-- Top-Left Scene Switcher Floating Dock (Steady UI) -->
          <div class="position-absolute top-0 start-0 m-3 z-3 d-flex gap-2 flex-wrap" v-if="modalScenes && Object.keys(modalScenes).length > 1">
            <button v-for="(sc, sKey) in modalScenes" :key="sKey"
                    class="btn btn-sm rounded-pill px-3 py-1 fw-bold shadow"
                    :style="{
                      background: currentModalScene === sKey ? 'var(--terracotta)' : 'rgba(18,20,24,0.88)',
                      color: '#ffffff',
                      border: currentModalScene === sKey ? '2px solid #ffffff' : '1px solid rgba(255,255,255,0.3)',
                      backdropFilter: 'blur(8px)'
                    }"
                    @click.stop="switchModalScene(sKey)">
              <i :class="sc.icon || 'bi bi-compass'" class="me-1"></i>{{ sc.title }}
            </button>
          </div>

          <!-- Bottom-Center Interactive Controls & Scene Dock (Steady UI) -->
          <div class="position-absolute bottom-0 start-50 translate-middle-x mb-3 z-3 d-flex align-items-center gap-2 px-3 py-2 rounded-pill shadow-lg"
               style="background:rgba(18,20,24,0.92); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.18);">
            
            <!-- Direct Steady Scene Buttons -->
            <div class="d-flex align-items-center gap-1 me-1" v-if="modalScenes && Object.keys(modalScenes).length > 1">
              <button v-for="(sc, sKey) in modalScenes" :key="'dock_'+sKey"
                      class="btn btn-sm px-3 rounded-pill fw-bold text-nowrap shadow-sm"
                      :style="{ 
                        background: currentModalScene === sKey ? 'var(--terracotta)' : 'rgba(255,255,255,0.12)',
                        color: '#fff',
                        border: currentModalScene === sKey ? '1px solid #fff' : '1px solid rgba(255,255,255,0.2)'
                      }"
                      @click.stop="switchModalScene(sKey)">
                <i :class="sc.icon || 'bi bi-compass'" class="me-1"></i>{{ sc.title }}
              </button>
            </div>

            <div class="vr bg-secondary opacity-50 mx-1" style="height:24px;" v-if="modalScenes && Object.keys(modalScenes).length > 1"></div>

            <!-- Auto-Rotate Play/Pause -->
            <button class="btn btn-sm text-white rounded-circle p-2 d-flex align-items-center justify-content-center"
                    :style="{ background: modalAutoRotate ? 'var(--terracotta)' : 'rgba(255,255,255,0.1)' }"
                    @click.stop="toggleModalAutoRotate"
                    :title="modalAutoRotate ? 'Pause Rotation (Space)' : 'Start Auto-Rotation (Space)'"
                    style="width:36px; height:36px;">
              <i class="bi" :class="modalAutoRotate ? 'bi-pause-fill' : 'bi-play-fill'" style="font-size:1.1rem;"></i>
            </button>

            <!-- Zoom In -->
            <button class="btn btn-sm text-white rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="background:rgba(255,255,255,0.1); width:36px; height:36px;"
                    @click.stop="zoomModalPano(-10)"
                    title="Zoom In (+)">
              <i class="bi bi-zoom-in" style="font-size:1rem;"></i>
            </button>

            <!-- Zoom Out -->
            <button class="btn btn-sm text-white rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="background:rgba(255,255,255,0.1); width:36px; height:36px;"
                    @click.stop="zoomModalPano(10)"
                    title="Zoom Out (-)">
              <i class="bi bi-zoom-out" style="font-size:1rem;"></i>
            </button>

            <!-- Reset View -->
            <button class="btn btn-sm text-white rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="background:rgba(255,255,255,0.1); width:36px; height:36px;"
                    @click.stop="resetModalPanoView"
                    title="Reset View (H)">
              <i class="bi bi-compass" style="font-size:1rem;"></i>
            </button>

            <div class="vr bg-secondary opacity-50 mx-1" style="height:24px;"></div>

            <!-- Fullscreen -->
            <button class="btn btn-sm text-white rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="background:rgba(255,255,255,0.1); width:36px; height:36px;"
                    @click.stop="toggleModalFullscreen"
                    title="Fullscreen (F)">
              <i class="bi bi-arrows-fullscreen" style="font-size:0.9rem;"></i>
            </button>
          </div>

          <!-- Bottom-Left Keyboard Shortcut Guide Pill -->
          <div class="position-absolute bottom-0 start-0 m-3 z-3 d-none d-md-flex align-items-center gap-2 px-3 py-1 rounded-pill"
               style="background:rgba(0,0,0,0.6); color:rgba(255,255,255,0.7); font-size:0.7rem; border:1px solid rgba(255,255,255,0.1);">
            <i class="bi bi-keyboard me-1 text-white"></i>
            <span>Drag / [←][→][↑][↓] look · [+] [-] zoom · [Space] rotate · [F] fullscreen</span>
          </div>

          <!-- Bottom-Right Scene Title Badge -->
          <div class="position-absolute bottom-0 end-0 m-3 z-3 d-flex align-items-center gap-1 px-3 py-1 rounded-pill shadow-sm"
               style="background:rgba(18,20,24,0.85); color:#fff; font-size:0.75rem; font-weight:700; border:1px solid rgba(255,255,255,0.15);">
            <i class="bi bi-eye text-terracotta me-1"></i>
            <span>{{ currentModalSceneTitle }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- MODAL 1.5: ROOM REVIEWS MODAL                  -->
  <!-- ============================================== -->
  <div class="modal fade" id="roomReviewsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg" style="background:var(--bg-card); border-radius:var(--r-lg);">
        <div class="modal-header border-bottom border-secondary border-opacity-25" style="background:var(--bg-glass);">
          <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" v-if="reviewsRoom">
            <i class="bi bi-star-fill text-warning"></i>
            Reviews for {{ reviewsRoom.room_name }}
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4 text-white">
          
          <div class="mb-4 text-center">
            <h2 class="display-4 text-warning fw-bold mb-0">{{ reviewsRoom ? Number(reviewsRoom.avg_rating).toFixed(1) : '0.0' }}</h2>
            <div class="text-warning mb-1 fs-5">
              <i class="bi" v-for="n in 5" :key="'main_star_'+n" :class="getStarClass(reviewsRoom ? reviewsRoom.avg_rating : 0, n)"></i>
            </div>
            <p class="text-secondary small m-0">Based on {{ reviewsRoom ? reviewsRoom.review_count : 0 }} reviews</p>
          </div>

          <hr class="border-secondary border-opacity-25">

          <h6 class="fw-bold mb-3">Add Your Review</h6>
          <div class="mb-3">
            <label class="form-label text-secondary small">Your Rating</label>
            <div class="d-flex gap-2">
              <i v-for="n in 5" :key="'rate_'+n" 
                 class="bi text-warning fs-4 cursor-pointer" 
                 :class="n <= reviewForm.rating ? 'bi-star-fill' : 'bi-star'"
                 style="cursor: pointer;"
                 @click="reviewForm.rating = n"></i>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label text-secondary small">Your Review</label>
            <textarea class="form-control" v-model="reviewForm.comment" rows="3" placeholder="Tell us about your experience..." style="border-radius:var(--r-md);"></textarea>
          </div>
          <button class="btn btn-primary w-100 fw-bold mb-4 rounded-pill" @click="submitReview" :disabled="submittingReview">
            <span v-if="submittingReview" class="spinner-border spinner-border-sm me-2"></span>
            Submit Review
          </button>

          <h6 class="fw-bold mb-3 mt-2">Past Reviews</h6>
          <div v-if="loadingReviews" class="text-center py-4">
            <div class="spinner-border text-primary"></div>
          </div>
          <div v-else-if="roomReviewsList.length === 0" class="text-center text-secondary py-3">
            <i class="bi bi-chat-left-dots fs-1 mb-2 d-block"></i>
            No reviews yet. Be the first!
          </div>
          <div v-else class="d-flex flex-column gap-3">
            <div v-for="rev in roomReviewsList" :key="rev.created_at" class="p-3 rounded-3 border border-secondary border-opacity-25" style="background: var(--cream-deep);">
              <div class="d-flex justify-content-between mb-2">
                <span class="fw-bold small" style="color: var(--text-primary);"><i class="bi bi-person-circle text-info me-2"></i>{{ rev.user_name }}</span>
                <span class="text-warning small">
                  <i v-for="n in 5" :key="'rev_star_'+n" class="bi" :class="n <= rev.rating ? 'bi-star-fill' : 'bi-star'"></i>
                </span>
              </div>
              <p class="mb-1" style="font-size:0.8rem; color: var(--text-secondary);">{{ rev.comment }}</p>
              <div class="text-secondary text-end" style="font-size:0.7rem;">{{ formatDateTime(rev.created_at) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- MODAL 2: RESERVATION BOOKING MODAL             -->
  <!-- ============================================== -->
  <div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content modal-content-dark">
        <div class="modal-header">
          <div>
            <h5 class="modal-title d-flex align-items-center gap-2">
              <i class="bi bi-calendar2-check" style="color:var(--terracotta)"></i> Make a Reservation
            </h5>
            <div class="small" style="color:var(--text-muted)" v-if="bookingTarget">
              <i class="bi bi-geo-alt-fill me-1" style="color:var(--terracotta)"></i>{{ bookingTarget.title }} — {{ bookingTarget.branch_name }}
            </div>
            <!-- Commuter transit info badge inside booking modal -->
            <div v-if="bookingTarget && bookingTarget.nearest_terminal && bookingTarget.nearest_terminal !== 'Commuter Hub'" class="d-flex align-items-center gap-2 mt-1 flex-wrap">
              <span class="badge" style="background:rgba(56,189,248,0.12); color:var(--sage); border:1px solid rgba(56,189,248,0.3); font-size:0.68rem;">
                <i class="bi bi-train-front-fill me-1"></i>{{ bookingTarget.nearest_terminal }}
              </span>
              <span class="badge" style="background:rgba(107,143,113,0.1); color:var(--sage); border:1px solid rgba(107,143,113,0.3); font-size:0.68rem;">
                <i class="bi bi-sign-turn-right-fill me-1"></i>{{ bookingTarget.walk_time }}
              </span>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body" v-if="bookingTarget">
          
          <!-- STEP 1: FORM ENTRY -->
          <div v-if="bookingStep === 'form'">
            <form @submit.prevent="proceedToReview">
              
              <!-- Guest Information -->
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small text-secondary d-flex justify-content-between">
                    <span>Guest Full Name</span>
                    <span v-if="bookingErrors.guestName" class="text-danger small">{{ bookingErrors.guestName }}</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text modal-input border-end-0 text-secondary"><i class="bi bi-person"></i></span>
                    <input type="text" v-model="bookingForm.guestName" class="form-control modal-input border-start-0" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label small text-secondary d-flex justify-content-between">
                    <span>Mobile Contact (Philippines)</span>
                    <span v-if="bookingErrors.guestPhone" class="text-danger small">{{ bookingErrors.guestPhone }}</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text modal-input border-end-0 text-secondary"><i class="bi bi-telephone"></i></span>
                    <input type="tel" v-model="bookingForm.guestPhone" class="form-control modal-input border-start-0" placeholder="+63 9XX XXX XXXX" required>
                  </div>
                </div>
              </div>

              <!-- Suite Selection & Stay Duration / Type -->
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small" style="color:var(--text-secondary)">Select Room / Unit</label>
                  <select v-model="bookingForm.selectedRoomId" class="form-select modal-input" @change="onRoomSelectChange">
                    <option v-for="rm in (bookingTarget.rooms || [])" :key="rm.id" :value="rm.id" :disabled="rm.status !== 'Available' || (rm.booking_limit && rm.total_booked >= rm.booking_limit)">
                      {{ rm.room_number ? '[' + rm.room_number + '] ' : '' }}{{ rm.room_name }} — ₱{{ Number(rm.price).toLocaleString() }}/{{ rm.rate_type || 'night' }} ({{ rm.bed_type }}) [{{ rm.total_booked }}/{{ rm.booking_limit || 10 }} Booked{{ (rm.booking_limit && rm.total_booked >= rm.booking_limit) ? ' • FULLY BOOKED' : (' • ' + Math.max(0, (rm.booking_limit || 10) - rm.total_booked) + ' Left') }}]
                    </option>
                  </select>
                </div>

                <!-- DURATION & RATE TYPE SELECTOR -->
                <div class="col-md-6">
                  <!-- FOR MOTELS & SHORT RENTALS: Choice between HOURLY and NIGHTLY -->
                  <div v-if="isMotelOrRentalTarget">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <label class="form-label small mb-0" style="color:var(--text-secondary)">Stay Duration Mode</label>
                      <span class="badge rounded-pill" :style="bookingForm.bookingMode === 'hourly' ? 'background:rgba(200,113,74,0.18); color:var(--terracotta);' : 'background:rgba(107,143,113,0.18); color:var(--sage);'">
                        {{ bookingForm.bookingMode === 'hourly' ? '⏱ Hourly Wash/Mid' : '🌙 Overnight / Nightly' }}
                      </span>
                    </div>

                    <!-- Mode Toggle Buttons -->
                    <div class="btn-group w-100 mb-2" role="group">
                      <button type="button" class="btn btn-sm py-1 fw-semibold" 
                              :class="bookingForm.bookingMode === 'hourly' ? 'btn-primary' : 'btn-outline-secondary'"
                              @click="setBookingMode('hourly')">
                        ⏱ Hourly (Wash/Mid)
                      </button>
                      <button type="button" class="btn btn-sm py-1 fw-semibold" 
                              :class="bookingForm.bookingMode === 'nightly' ? 'btn-primary' : 'btn-outline-secondary'"
                              @click="setBookingMode('nightly')">
                        🌙 Nightly / Nights
                      </button>
                    </div>

                    <!-- Hourly Duration Select -->
                    <select v-if="bookingForm.bookingMode === 'hourly'" v-model="bookingForm.hourlySlot" class="form-select modal-input" @change="onHourlySlotChange">
                      <optgroup label="⚡ Express Wash Rates">
                        <option value="2hr">⚡ 2 Hours — ₱{{ bookingTarget?.hourly_rates?.['2hr'] ?? selectedRoomPrice }}</option>
                        <option value="3hr" selected>🚿 3 Hours (Wash Rate) — ₱{{ bookingTarget?.hourly_rates?.['3hr'] ?? selectedRoomPrice }}</option>
                      </optgroup>
                      <optgroup label="🕕 Mid-Day Stay">
                        <option value="6hr">🕕 6 Hours — ₱{{ bookingTarget?.hourly_rates?.['6hr'] ?? selectedRoomPrice }}</option>
                        <option value="7hr">🕖 7 Hours — ₱{{ computedHourlyRate('7hr') }}</option>
                        <option value="8hr">🕗 8 Hours — ₱{{ computedHourlyRate('8hr') }}</option>
                      </optgroup>
                      <optgroup label="🌙 Extended / Layover">
                        <option value="12hr">🌙 12 Hours (Half-day) — ₱{{ bookingTarget?.hourly_rates?.['12hr'] ?? selectedRoomPrice }}</option>
                        <option value="24hr">🛌 24 Hours (Full Day) — ₱{{ bookingTarget?.hourly_rates?.['24hr'] ?? selectedRoomPrice }}</option>
                      </optgroup>
                    </select>

                    <!-- Nightly Duration Select -->
                    <select v-else v-model.number="bookingForm.nights" class="form-select modal-input" @change="onNightsChange">
                      <option :value="1">1 Night / Overnight Stay</option>
                      <option :value="2">2 Nights (Weekend)</option>
                      <option :value="3">3 Nights</option>
                      <option :value="5">5 Nights</option>
                      <option :value="7">7 Nights / Weekly Rate</option>
                      <option :value="30">30 Days / Monthly Rate</option>
                    </select>
                  </div>

                  <!-- FOR HOTELS (AND AIRBNB): NIGHTS ONLY -->
                  <div v-else>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <label class="form-label small mb-0" style="color:var(--text-secondary)">🌙 Number of Nights</label>
                      <span class="badge rounded-pill" style="background:rgba(200,113,74,0.18); color:var(--terracotta); border:1px solid rgba(200,113,74,0.3); font-size:0.68rem;">
                        {{ isHotelTarget ? '🏨 Hotel Nightly Stay' : 'Nightly Stay' }}
                      </span>
                    </div>
                    <select v-model.number="bookingForm.nights" class="form-select modal-input" @change="onNightsChange">
                      <option :value="1">1 Night / Standard Nightly</option>
                      <option :value="2">2 Nights (Weekend Getaway)</option>
                      <option :value="3">3 Nights (Extended Weekend)</option>
                      <option :value="4">4 Nights</option>
                      <option :value="5">5 Nights (Holiday Package)</option>
                      <option :value="7">7 Nights / 1 Week</option>
                      <option :value="14">14 Nights / 2 Weeks</option>
                      <option :value="30">30 Nights / Long Stay</option>
                    </select>
                    <div class="form-text small" style="color:var(--text-muted); font-size:0.72rem;">
                      <i class="bi bi-info-circle me-1"></i>Hotels are booked by night. Standard check-in: 2:00 PM.
                    </div>
                  </div>
                </div>
              </div>

              <!-- Check-In Date and Time -->
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small text-secondary d-flex justify-content-between">
                    <span>Target Check-In Date & Time</span>
                    <span v-if="bookingErrors.checkInTime" class="text-danger small">{{ bookingErrors.checkInTime }}</span>
                  </label>
                  <input type="datetime-local" v-model="bookingForm.checkInTime" class="form-control modal-input" :min="minDateTime" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small" style="color:var(--text-secondary)">Stay Package Type</label>
                  <input type="text" class="form-control modal-input text-white" :value="isMotelOrRentalTarget && bookingForm.bookingMode === 'hourly' ? bookingForm.stayDurationType : (bookingForm.nights + ' Night(s) Stay')" readonly style="background:rgba(255,255,255,0.05)!important; cursor:default;">
                </div>
              </div>

              <!-- Price Calculation Summary Box with Anti-Spam Downpayment -->
              <div class="booking-summary-box mb-4">
                <!-- Hourly rate breakdown for walk-in motel stays in hourly mode -->
                <div v-if="isMotelOrRentalTarget && bookingForm.bookingMode === 'hourly'" class="mb-3 pb-2" style="border-bottom:1px dashed var(--border);">
                  <div class="small fw-bold mb-2" style="color:var(--terracotta); letter-spacing:0.5px; text-transform:uppercase;">⏱ Hourly Rate Sheet (Walk-in)</div>
                  <div class="d-flex flex-wrap gap-2">
                    <span v-for="(rate, slot) in (bookingTarget?.hourly_rates || {})" :key="slot"
                      class="px-2 py-1 rounded-2" 
                      :style="bookingForm.hourlySlot === slot ? 'background:var(--terracotta); color:#fff; font-size:0.72rem; font-weight:700;' : 'background:var(--cream-deep); color:var(--text-secondary); border:1px solid var(--border); font-size:0.72rem; cursor:pointer;'"
                      @click="bookingForm.hourlySlot = slot; onHourlySlotChange()">
                      {{ slot }} — ₱{{ Number(rate).toLocaleString() }}
                    </span>
                  </div>
                </div>

                <div class="d-flex justify-content-between small mb-1" style="color:var(--text-secondary)">
                  <span>{{ (isMotelOrRentalTarget && bookingForm.bookingMode === 'hourly') ? 'Rate (' + bookingForm.hourlySlot + '):' : 'Nightly Rate:' }}</span>
                  <span style="color:var(--mocha);font-weight:600">
                    ₱{{ Number((isMotelOrRentalTarget && bookingForm.bookingMode === 'hourly') ? calculateTotalAmount : selectedRoomPrice).toLocaleString() }}
                    {{ (isMotelOrRentalTarget && bookingForm.bookingMode === 'hourly') ? '' : ' / night' }}
                  </span>
                </div>

                <div class="d-flex justify-content-between small mb-1" style="color:var(--text-secondary)">
                  <span>Duration:</span>
                  <span style="color:var(--mocha);font-weight:600">
                    {{ (isMotelOrRentalTarget && bookingForm.bookingMode === 'hourly') ? bookingForm.stayDurationType : (bookingForm.nights + ' Night(s)') }}
                  </span>
                </div>

                <div class="d-flex justify-content-between small mb-1" style="color:var(--text-secondary)">
                  <span>Taxes &amp; Local Fees:</span>
                  <span style="color:var(--sage);font-weight:600">Included</span>
                </div>

                <div class="d-flex justify-content-between pt-2 mb-2" style="border-top:1.5px solid var(--border)">
                  <span style="color:var(--mocha);font-weight:700">Total Stay Amount:</span>
                  <span style="color:var(--terracotta);font-weight:800;font-size:1.15rem">₱{{ Number(calculateTotalAmount).toLocaleString() }}</span>
                </div>

                <!-- Anti-Spam Reservation Downpayment Breakdown -->
                <div class="p-2 rounded-2 mt-2" style="background:rgba(224,86,36,0.08); border:1px solid rgba(224,86,36,0.22);">
                  <div class="d-flex justify-content-between small align-items-center">
                    <span class="text-white fw-semibold">
                      <i class="bi bi-shield-check text-warning me-1"></i>Reservation Down Payment (20%):
                    </span>
                    <span class="fw-bold fs-6" style="color:var(--terracotta)">₱{{ Number(computedDownpaymentAmount).toLocaleString() }}</span>
                  </div>
                  <div class="d-flex justify-content-between small text-secondary mt-1" style="font-size:0.74rem;">
                    <span>Remaining Balance upon Hotel Check-In:</span>
                    <span class="text-white fw-semibold">₱{{ Number(computedBalanceDue).toLocaleString() }}</span>
                  </div>
                </div>
              </div>

              <!-- Action Button to Review Details -->
              <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow">
                <i class="bi bi-arrow-right-circle-fill me-1"></i> Review & Select Payment Mode
              </button>
            </form>
          </div>

          <!-- STEP 2: CONFIRMATION REVIEW ACTION SCREEN -->
          <div v-else-if="bookingStep === 'review'">
            <!-- Anti-Spam Security Shield Banner -->
            <div class="anti-spam-shield-banner mb-3">
              <div class="d-flex align-items-start gap-2">
                <i class="bi bi-shield-lock-fill text-warning fs-4"></i>
                <div>
                  <div class="fw-bold small text-white">Anti-Spam Security & Reservation Policy</div>
                  <div class="small text-secondary" style="font-size:0.75rem;">
                    To protect room allocations against automated bots and fake booking spams, an initial <strong>20% booking down payment (₱{{ Number(computedDownpaymentAmount).toLocaleString() }})</strong> is required via verified online payment. The remaining balance of <strong>₱{{ Number(computedBalanceDue).toLocaleString() }}</strong> is paid directly at the front desk upon check-in.
                  </div>
                </div>
              </div>
            </div>

            <!-- Booking Summary Review Box -->
            <div class="booking-review-box mb-3">
              <div class="row g-2 small">
                <div class="col-sm-4" style="color:var(--text-muted)">Property:</div>
                <div class="col-sm-8 fw-bold" style="color:var(--mocha)">{{ bookingTarget.title }}</div>

                <div class="col-sm-4 text-secondary">Room Suite & Unit #:</div>
                <div class="col-sm-8 text-info fw-bold">
                  <span v-if="selectedRoomNumber" class="badge bg-primary font-monospace me-2"><i class="bi bi-door-closed-fill me-1"></i>{{ selectedRoomNumber }}</span>
                  {{ selectedRoomName }}
                </div>

                <div class="col-sm-4 text-secondary">Guest Name:</div>
                <div class="col-sm-8 text-white">{{ bookingForm.guestName }}</div>

                <div class="col-sm-4 text-secondary">Contact Number:</div>
                <div class="col-sm-8 text-white">{{ bookingForm.guestPhone }}</div>

                <div class="col-sm-4 text-secondary">Check-In Date/Time:</div>
                <div class="col-sm-8 text-white">{{ formatDateTime(bookingForm.checkInTime) }}</div>

                <div class="col-sm-4 text-secondary">Duration &amp; Stay:</div>
                <div class="col-sm-8 text-white">
                  <span v-if="isMotelOrRentalTarget && bookingForm.bookingMode === 'hourly'" class="badge rounded-pill" style="background:rgba(200,113,74,0.25); color:var(--terracotta); font-size:0.75rem;">
                    ⏱ {{ bookingForm.stayDurationType }}
                  </span>
                  <span v-else class="badge rounded-pill" style="background:rgba(107,143,113,0.25); color:var(--sage); font-size:0.75rem;">
                    🌙 {{ bookingForm.nights }} Night{{ bookingForm.nights > 1 ? 's' : '' }} Stay
                  </span>
                </div>

                <div class="col-12"><hr class="border-secondary border-opacity-25 my-2"></div>

                <div class="col-sm-4 text-secondary">Total Stay Cost:</div>
                <div class="col-sm-8 text-white fw-bold">₱{{ Number(calculateTotalAmount).toLocaleString() }}</div>

                <div class="col-sm-4 text-warning fw-bold">Down Payment Due Now:</div>
                <div class="col-sm-8 text-success fw-bold fs-5">₱{{ Number(computedDownpaymentAmount).toLocaleString() }}</div>

                <div class="col-sm-4 text-secondary">Balance at Front Desk:</div>
                <div class="col-sm-8 text-secondary">₱{{ Number(computedBalanceDue).toLocaleString() }}</div>
              </div>
            </div>

            <!-- Online Payment Modes Selector -->
            <div class="mb-4">
              <label class="form-label small fw-bold mb-2 text-white d-flex align-items-center justify-content-between">
                <span><i class="bi bi-wallet2 text-warning me-1"></i>Select Online Mode of Payment</span>
                <span class="badge bg-secondary bg-opacity-50 text-white-50" style="font-size:0.65rem;">Instant Verification</span>
              </label>
              <div class="payment-mode-grid">
                <!-- GCash -->
                <div class="payment-mode-card" :class="{ selected: selectedPaymentMethod === 'gcash' }" @click="selectedPaymentMethod = 'gcash'">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="payment-brand-badge" style="background:#007DFE;">GCash</span>
                    <i v-if="selectedPaymentMethod === 'gcash'" class="bi bi-check-circle-fill text-success"></i>
                  </div>
                  <div class="text-white small fw-bold">GCash e-Wallet</div>
                  <div class="text-secondary" style="font-size:0.68rem;">Mobile / QR Ph</div>
                </div>

                <!-- Maya / PayMaya -->
                <div class="payment-mode-card" :class="{ selected: selectedPaymentMethod === 'maya' }" @click="selectedPaymentMethod = 'maya'">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="payment-brand-badge" style="background:#289F4C;">Maya</span>
                    <i v-if="selectedPaymentMethod === 'maya'" class="bi bi-check-circle-fill text-success"></i>
                  </div>
                  <div class="text-white small fw-bold">Maya / PayMaya</div>
                  <div class="text-secondary" style="font-size:0.68rem;">Wallet / Scan QR</div>
                </div>

                <!-- GrabPay -->
                <div class="payment-mode-card" :class="{ selected: selectedPaymentMethod === 'grabpay' }" @click="selectedPaymentMethod = 'grabpay'">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="payment-brand-badge" style="background:#00B14F;">GrabPay</span>
                    <i v-if="selectedPaymentMethod === 'grabpay'" class="bi bi-check-circle-fill text-success"></i>
                  </div>
                  <div class="text-white small fw-bold">GrabPay PH</div>
                  <div class="text-secondary" style="font-size:0.68rem;">Direct Transfer</div>
                </div>

                <!-- Visa / Mastercard -->
                <div class="payment-mode-card" :class="{ selected: selectedPaymentMethod === 'card' }" @click="selectedPaymentMethod = 'card'">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="payment-brand-badge" style="background:#6366F1;">Cards</span>
                    <i v-if="selectedPaymentMethod === 'card'" class="bi bi-check-circle-fill text-success"></i>
                  </div>
                  <div class="text-white small fw-bold">Visa / Mastercard</div>
                  <div class="text-secondary" style="font-size:0.68rem;">Debit or Credit</div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary w-40 py-2 fw-semibold rounded-3" @click="bookingStep = 'form'">
                <i class="bi bi-arrow-left me-1"></i> Edit Details
              </button>
              <button type="button" class="btn btn-primary w-60 py-2 fw-bold rounded-3 shadow" @click="proceedToPaymentGateway">
                <i class="bi bi-lock-fill me-1"></i> Proceed to Pay ₱{{ Number(computedDownpaymentAmount).toLocaleString() }}
              </button>
            </div>
          </div>

          <!-- STEP 3: INTERACTIVE ONLINE PAYMENT GATEWAY (GCASH / MAYA / CARDS) -->
          <div v-else-if="bookingStep === 'payment'">
            <div class="payment-summary-box mb-3">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                  <span class="payment-brand-badge px-3 py-1 fs-6" :style="{ background: getPaymentBadgeBg(selectedPaymentMethod) }">
                    {{ selectedPaymentMethod.toUpperCase() }}
                  </span>
                  <div>
                    <div class="text-white fw-bold small">Online Downpayment Gateway</div>
                    <div class="text-secondary" style="font-size:0.7rem;">Official Anti-Spam Reservation Portal</div>
                  </div>
                </div>
                <div class="text-end">
                  <span class="text-secondary small d-block" style="font-size:0.65rem;">DOWNPAYMENT AMOUNT</span>
                  <span class="text-success fw-bold fs-5">₱{{ Number(computedDownpaymentAmount).toLocaleString() }}</span>
                </div>
              </div>
            </div>

            <!-- E-Wallet Mode Toggle (Mobile / QR Ph) -->
            <div v-if="['gcash', 'maya', 'grabpay'].includes(selectedPaymentMethod)" class="btn-group w-100 mb-3" role="group">
              <button type="button" class="btn btn-sm py-1 fw-semibold" :class="payMethodMode === 'number' ? 'btn-primary' : 'btn-outline-secondary'" @click="payMethodMode = 'number'">
                <i class="bi bi-phone me-1"></i> Mobile / Account Number
              </button>
              <button type="button" class="btn btn-sm py-1 fw-semibold" :class="payMethodMode === 'qr' ? 'btn-primary' : 'btn-outline-secondary'" @click="payMethodMode = 'qr'">
                <i class="bi bi-qr-code-scan me-1"></i> Scan QR Ph
              </button>
            </div>

            <!-- Form: Mobile Account Number Entry -->
            <div v-if="['gcash', 'maya', 'grabpay'].includes(selectedPaymentMethod) && payMethodMode === 'number'" class="mb-3">
              <div class="mb-2">
                <label class="form-label small text-secondary">Registered {{ selectedPaymentMethod.toUpperCase() }} Number</label>
                <div class="input-group">
                  <span class="input-group-text modal-input text-secondary"><i class="bi bi-phone"></i></span>
                  <input type="text" v-model="paymentForm.accountNumber" class="form-control modal-input fw-mono" placeholder="09XX XXX XXXX" required>
                </div>
              </div>
              <div class="mb-2">
                <label class="form-label small text-secondary">Account Holder Name</label>
                <input type="text" v-model="paymentForm.accountName" class="form-control modal-input" placeholder="Juan Dela Cruz" required>
              </div>
              <div class="p-2 rounded-2" style="background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.25);">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="small text-secondary" style="font-size:0.72rem;">
                    <i class="bi bi-info-circle-fill text-info me-1"></i>Sandbox Mode: You can use any test mobile number.
                  </span>
                  <button type="button" class="btn btn-xs btn-outline-info py-0 px-2" style="font-size:0.65rem;" @click="paymentForm.accountNumber = '0917-829-4102'; paymentForm.accountName = bookingForm.guestName || 'Juan Dela Cruz'">
                    Fast Auto-Fill
                  </button>
                </div>
              </div>
            </div>

            <!-- QR Ph View -->
            <div v-else-if="['gcash', 'maya', 'grabpay'].includes(selectedPaymentMethod) && payMethodMode === 'qr'" class="qr-code-box text-center mb-3">
              <div class="d-inline-block p-2 rounded-2 bg-white mb-2">
                <!-- Realistic stylized QR SVG -->
                <svg width="150" height="150" viewBox="0 0 100 100" fill="#000">
                  <rect x="5" y="5" width="28" height="28" fill="#000" rx="3"/>
                  <rect x="9" y="9" width="20" height="20" fill="#fff" rx="2"/>
                  <rect x="13" y="13" width="12" height="12" fill="#000"/>
                  
                  <rect x="67" y="5" width="28" height="28" fill="#000" rx="3"/>
                  <rect x="71" y="9" width="20" height="20" fill="#fff" rx="2"/>
                  <rect x="75" y="13" width="12" height="12" fill="#000"/>
                  
                  <rect x="5" y="67" width="28" height="28" fill="#000" rx="3"/>
                  <rect x="9" y="71" width="20" height="20" fill="#fff" rx="2"/>
                  <rect x="13" y="75" width="12" height="12" fill="#000"/>
                  
                  <circle cx="50" cy="50" r="10" fill="#e05624"/>
                  <rect x="40" y="10" width="8" height="8" fill="#000"/>
                  <rect x="40" y="24" width="8" height="8" fill="#000"/>
                  <rect x="40" y="70" width="8" height="8" fill="#000"/>
                  <rect x="70" y="45" width="8" height="8" fill="#000"/>
                  <rect x="85" y="65" width="8" height="8" fill="#000"/>
                  <rect x="65" y="80" width="8" height="8" fill="#000"/>
                  <rect x="50" y="75" width="8" height="8" fill="#000"/>
                  <rect x="25" y="45" width="8" height="8" fill="#000"/>
                </svg>
              </div>
              <div class="fw-bold text-white small">Scan via {{ selectedPaymentMethod.toUpperCase() }} or any QR Ph App</div>
              <div class="text-secondary" style="font-size:0.72rem;">Merchant: StayFinder PH Reservations • Amount: ₱{{ Number(computedDownpaymentAmount).toLocaleString() }}</div>
            </div>

            <!-- Card View -->
            <div v-else-if="selectedPaymentMethod === 'card'" class="mb-3">
              <div class="mb-2">
                <label class="form-label small text-secondary">Cardholder Name</label>
                <input type="text" v-model="paymentForm.cardHolder" class="form-control modal-input" placeholder="Name on card" required>
              </div>
              <div class="mb-2">
                <label class="form-label small text-secondary">Card Number</label>
                <div class="input-group">
                  <span class="input-group-text modal-input text-secondary"><i class="bi bi-credit-card-2-front"></i></span>
                  <input type="text" v-model="paymentForm.cardNumber" class="form-control modal-input fw-mono" placeholder="•••• •••• •••• ••••">
                </div>
              </div>
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small text-secondary">Expiry</label>
                  <input type="text" v-model="paymentForm.cardExpiry" class="form-control modal-input text-center fw-mono" placeholder="MM/YY">
                </div>
                <div class="col-6">
                  <label class="form-label small text-secondary">CVV</label>
                  <input type="password" v-model="paymentForm.cardCvv" maxlength="4" class="form-control modal-input text-center fw-mono" placeholder="•••">
                </div>
              </div>
            </div>

            <!-- Security Assurance Notice -->
            <div class="d-flex align-items-center gap-2 mb-3 text-secondary" style="font-size:0.72rem;">
              <i class="bi bi-shield-check text-success fs-5"></i>
              <span>256-Bit SSL Encrypted Payment Gateway. Transaction is instantly verified and attached to your booking reference.</span>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary w-40 py-2 fw-semibold rounded-3" @click="bookingStep = 'review'">
                <i class="bi bi-arrow-left me-1"></i> Back
              </button>
              <button type="button" class="btn btn-success w-60 py-2 fw-bold rounded-3 shadow" :disabled="isProcessingPayment" @click="processOnlineDownPayment">
                <span v-if="isProcessingPayment" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check2-circle me-1"></i> Authorize & Pay ₱{{ Number(computedDownpaymentAmount).toLocaleString() }}
              </button>
            </div>
          </div>

          <!-- STEP 4: SUCCESS CONFIRMATION RECEIPT & VOUCHER -->
          <div v-else-if="bookingStep === 'success' && lastConfirmedBooking" class="text-center py-2">
            <div class="d-inline-flex p-3 rounded-circle mb-3" style="background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.3);">
              <i class="bi bi-patch-check-fill text-success fs-1"></i>
            </div>
            <h5 class="fw-bold text-white mb-1">Reservation Guaranteed & Confirmed!</h5>
            <p class="text-secondary small mb-3">Your online down payment has been verified. Anti-spam booking protection active.</p>

            <div class="payment-receipt-box text-start mb-4">
              <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary border-opacity-25">
                <div>
                  <span class="text-secondary small d-block" style="font-size:0.68rem;">OFFICIAL REFERENCE NUMBER</span>
                  <span class="fw-mono text-info fw-bold fs-6">{{ lastConfirmedBooking.payment_ref }}</span>
                </div>
                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>PAID &amp; RESERVED</span>
              </div>

              <div class="row g-2 small">
                <div class="col-sm-4 text-secondary">Hotel Property:</div>
                <div class="col-sm-8 text-white fw-bold">{{ lastConfirmedBooking.hotel_title }}</div>

                <div class="col-sm-4 text-secondary">Suite / Room:</div>
                <div class="col-sm-8 text-info fw-semibold">{{ lastConfirmedBooking.room_name }} ({{ lastConfirmedBooking.room_number || 'Main Suite' }})</div>

                <div class="col-sm-4 text-secondary">Guest Name:</div>
                <div class="col-sm-8 text-white">{{ lastConfirmedBooking.guest_name }}</div>

                <div class="col-sm-4 text-secondary">Check-In Schedule:</div>
                <div class="col-sm-8 text-white">{{ formatDateTime(lastConfirmedBooking.check_in_time) }}</div>

                <div class="col-sm-4 text-secondary">Payment Method:</div>
                <div class="col-sm-8">
                  <span class="badge text-white" :style="{ background: getPaymentBadgeBg(lastConfirmedBooking.payment_method) }">
                    {{ (lastConfirmedBooking.payment_method || 'GCash').toUpperCase() }}
                  </span>
                </div>

                <div class="col-sm-4 text-secondary">Down Payment Paid:</div>
                <div class="col-sm-8 text-success fw-bold">₱{{ Number(lastConfirmedBooking.downpayment_amount).toLocaleString() }} (20%)</div>

                <div class="col-sm-4 text-secondary">Balance at Front Desk:</div>
                <div class="col-sm-8 text-warning fw-bold">₱{{ Number(lastConfirmedBooking.balance_due).toLocaleString() }}</div>
              </div>
            </div>

            <div class="d-flex gap-2 justify-content-center">
              <button type="button" class="btn btn-outline-info px-3 py-2 fw-semibold rounded-3" @click="bookingModalInstance.hide(); openBookingsModal();">
                <i class="bi bi-journal-text me-1"></i> View My Bookings
              </button>
              <button type="button" class="btn btn-primary px-4 py-2 fw-bold rounded-3" data-bs-dismiss="modal">
                <i class="bi bi-geo-alt me-1"></i> Back to Map
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- MODAL 3: CONFIRMATION ACTION MODAL (FOR CANCEL) -->
  <!-- ============================================== -->
  <div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modal-content-dark">
        <div class="modal-body p-4 text-center" v-if="cancelTarget">
          <div class="d-inline-flex p-3 rounded-circle bg-danger bg-opacity-20 text-danger mb-3 border border-danger border-opacity-25">
            <i class="bi bi-exclamation-triangle-fill fs-2"></i>
          </div>
          <h5 class="text-white fw-bold mb-2">Cancel Reservation?</h5>
          <p class="text-white-50 small mb-3">
            Are you sure you want to cancel booking <strong class="text-info">#{{ cancelTarget.id }}</strong> for <strong class="text-white">{{ cancelTarget.stayTitle }}</strong> ({{ cancelTarget.roomName || 'Luxury Suite' }})?
          </p>
          <div class="p-3 rounded-3 mb-4 text-start small" style="background:#131c31; border:1px solid #1e293b;">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-secondary">Refundable Amount:</span>
              <span class="text-success fw-bold">₱{{ Number(cancelTarget.amount).toLocaleString() }}</span>
            </div>
            <div class="text-secondary" style="font-size:0.75rem;">
              <i class="bi bi-info-circle me-1"></i> The room inventory slot will immediately be released back to other travelers.
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary w-50 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">
              Keep Reservation
            </button>
            <button type="button" class="btn btn-danger w-50 py-2 fw-bold rounded-3 shadow" @click="confirmCancelBookingAction">
              <i class="bi bi-x-circle-fill me-1"></i> Yes, Cancel Booking
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- MODAL 4: USER BOOKINGS HISTORY MODAL           -->
  <!-- ============================================== -->
  <div class="modal fade" id="userBookingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content modal-content-dark">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
            <i class="bi bi-journal-check text-success"></i> My Stays & Booking History
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle border-secondary mb-0">
              <thead class="table-dark">
                <tr class="text-secondary small">
                  <th>ID</th>
                  <th>Hotel Property</th>
                  <th>Suite & Room #</th>
                  <th>Stay Package</th>
                  <th>Total Rate</th>
                  <th>Deposit &amp; Payment</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="b in userBookings" :key="b.id">
                  <td class="text-white-50 small fw-mono">#{{ b.id }}</td>
                  <td class="fw-bold text-white">{{ b.stayTitle }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                      <span v-if="b.roomNumber" class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 font-monospace me-1">{{ b.roomNumber }}</span>
                      <span class="text-info small">{{ b.roomName || 'Deluxe Room' }}</span>
                    </div>
                  </td>
                  <td><span class="badge bg-dark border border-secondary">{{ b.stayType }}</span></td>
                  <td class="text-white fw-bold">₱{{ Number(b.amount).toLocaleString() }}</td>
                  <td>
                    <div>
                      <div class="d-flex align-items-center gap-1 mb-1">
                        <span class="badge" :style="{ background: getPaymentBadgeBg(b.payment_method || 'gcash'), fontSize: '0.66rem' }">
                          {{ (b.payment_method || 'GCash').toUpperCase() }}
                        </span>
                        <span class="text-success small fw-semibold">
                          Paid: ₱{{ Number(b.downpayment_amount || Math.round(b.amount * 0.2)).toLocaleString() }}
                        </span>
                      </div>
                      <div class="text-secondary" style="font-size:0.7rem;">
                        Bal: <span class="text-warning fw-semibold">₱{{ Number(b.balance_due || Math.round(b.amount * 0.8)).toLocaleString() }}</span>
                        <span v-if="b.payment_ref" class="ms-1 fw-mono text-white-50" style="font-size:0.65rem;">({{ b.payment_ref }})</span>
                      </div>
                    </div>
                  </td>
                  <td><span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Guaranteed</span></td>
                  <td>
                    <button class="btn btn-outline-danger btn-xs py-1 px-2" @click="promptCancelBooking(b)">
                      <i class="bi bi-trash3 me-1"></i>Cancel
                    </button>
                  </td>
                </tr>
                <tr v-if="userBookings.length === 0">
                  <td colspan="8" class="text-center text-secondary py-4">
                    <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                    No active hotel reservations found yet.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- MODAL 5: ACCOUNT VERIFICATION ACTION MODAL     -->
  <!-- ============================================== -->
  <div class="modal fade" id="verifyAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modal-content-dark">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
            <i class="bi bi-patch-check-fill text-warning"></i> Verify Real User Account
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-secondary small mb-3">
            Confirm your account for <strong><?= htmlspecialchars($userEmail) ?></strong> to unlock verified guest booking privileges.
          </p>

          <!-- Demo Quick Code Badge -->
          <div class="p-3 rounded-3 mb-3 border border-primary border-opacity-30" style="background: rgba(59, 130, 246, 0.1);">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="small text-secondary d-block">Demo Verification Code:</span>
                <span class="fw-mono text-white fw-bold fs-5 letter-spacing-2">849201</span>
              </div>
              <button type="button" class="btn btn-xs btn-outline-info rounded-pill py-1 px-3" @click="verifyCodeInput = '849201'">
                <i class="bi bi-magic me-1"></i> Auto-Fill
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small text-secondary">Enter 6-Digit PIN</label>
            <input type="text" v-model="verifyCodeInput" maxlength="6" class="form-control modal-input text-center fs-4 fw-bold letter-spacing-3" placeholder="••••••">
            <div v-if="verifyError" class="text-danger small mt-1"><i class="bi bi-exclamation-triangle me-1"></i>{{ verifyError }}</div>
          </div>

          <!-- Action Buttons -->
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary py-2 fw-semibold rounded-3 shadow" @click="submitAccountVerification">
              <i class="bi bi-shield-check me-1"></i> Verify Account Now
            </button>
            <button type="button" class="btn btn-outline-success py-2 fw-semibold rounded-3" @click="instantVerifyAccount">
              <i class="bi bi-lightning-charge-fill me-1"></i> 1-Click Instant Verification Pass
            </button>
            <button type="button" class="btn btn-outline-secondary py-1 text-secondary small rounded-3" @click="resendVerificationCode">
              <i class="bi bi-arrow-clockwise me-1"></i> Resend Security Code
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- FLOATING ACTION TOAST NOTIFICATIONS            -->
  <!-- ============================================== -->
  <!-- v-if on message ensures this is absent from DOM entirely until a toast fires -->
  <div class="action-toast-wrapper" v-if="activeToast && activeToast.message">
    <div class="action-toast">
      <div class="d-flex align-items-start gap-2 mb-2">
        <i class="bi fs-5" :class="activeToast?.iconClass || 'bi-info-circle-fill text-info'"></i>
        <div class="flex-grow-1">
          <div class="fw-bold small" style="color:var(--mocha)">{{ activeToast?.title || '' }}</div>
          <div class="small" style="color:var(--text-secondary)">{{ activeToast?.message || '' }}</div>
        </div>
        <button type="button" class="btn-close btn-close-white btn-sm" @click="activeToast = null"></button>
      </div>
      <div class="d-flex justify-content-end gap-2 mt-2" v-if="activeToast?.actionButton">
        <button class="btn btn-xs btn-primary rounded-pill px-3 py-1" @click="activeToast.actionButton.handler">
          {{ activeToast?.actionButton?.label || '' }}
        </button>
      </div>
    </div>
  </div>

</div>

<script>
const { createApp, ref, computed, onMounted, nextTick, watch } = Vue;

createApp({
  setup() {
    const stays = ref([]);
    const userBookings = ref([]);
    const savedLocations = ref([]);
    const selectedStay = ref(null);
    const searchQuery = ref('');
    const selectedCategory = ref('all');
    const commuterOnly = ref(false);
    const sortBy = ref('featured');

    // Account verification state
    const accountVerified = ref(<?= json_encode($isVerified) ?>);
    const verifyCodeInput = ref('');
    const verifyError = ref('');

    // Helper to get local ISO string formatted for input[type="datetime-local"]
    const getLocalNowISO = () => {
      const now = new Date();
      const pad = (n) => String(n).padStart(2, '0');
      return `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    };
    const getTomorrowLocalISO = () => {
      const tmrw = new Date(Date.now() + 86400000);
      const pad = (n) => String(n).padStart(2, '0');
      return `${tmrw.getFullYear()}-${pad(tmrw.getMonth()+1)}-${pad(tmrw.getDate())}T${pad(tmrw.getHours())}:${pad(tmrw.getMinutes())}`;
    };

    // Leaflet Map Objects & Location State
    let map = null;
    let markersLayer = null;
    let directionsPolyline = null;
    let directionsOriginMarker = null;
    let flightMarker = null;
    let userLocationMarker = null;
    let markersMap = {};
    const currentTileLayer = ref('satellite');
    let tileLayers = {};
    const directionsActive = ref(false);
    const directionsTarget = ref(null);
    const directionsDistance = ref('0');
    const directionsDuration = ref('0');
    const isLocatingUser = ref(false);
    const userLocation = ref(null);

    // Multi-Modal GPS Navigation & Travel State
    const selectedTravelMode = ref('car'); // 'car' | 'motorcycle' | 'bicycle' | 'train' | 'airplane'
    const isLiveGpsTracking = ref(false);
    const isCalculatingRoute = ref(false);
    let watchGpsId = null;
    const originPreset = ref('gps'); // 'gps' | 'naia' | 'makati' | 'manila' | 'bgc'
    const turnByTurnSteps = ref([]);
    const showTurnSteps = ref(false);
    const isDirectionsMinimized = ref(false);
    let cachedRoadGeometry = [];

    const getModeIcon = (mode) => {
      switch (mode) {
        case 'car': return 'bi-car-front-fill';
        case 'motorcycle': return 'bi-speedometer2';
        case 'bicycle': return 'bi-bicycle';
        case 'train': return 'bi-train-front-fill';
        case 'airplane': return 'bi-airplane-fill';
        default: return 'bi-compass';
      }
    };

    const ORIGIN_PRESETS = {
      naia: { label: 'NAIA T3 Airport', coords: [14.5176, 121.0158] },
      makati: { label: 'Makati CBD (Ayala)', coords: [14.5507, 121.0280] },
      manila: { label: 'Manila City Hall', coords: [14.5895, 120.9815] },
      bgc: { label: 'BGC Taguig', coords: [14.5492, 121.0504] }
    };

    // Traffic Condition Simulator & Detection Engine
    const trafficCondition = ref('auto'); // 'auto' | 'light' | 'moderate' | 'heavy' | 'severe'
    let lastRawRouteData = null;

    const getTrafficConfig = (conditionKey) => {
      if (conditionKey === 'auto') {
        const now = new Date();
        const hour = now.getHours();
        const day = now.getDay(); // 0 = Sun, 5 = Fri, 6 = Sat

        // Payday / Friday night rush (heaviest traffic in Metro Manila)
        if (day === 5 && hour >= 16 && hour <= 21) {
          return {
            conditionKey: 'auto',
            resolvedLevel: 'severe',
            label: 'Friday Night Gridlock',
            badgeText: '🚨 Friday Gridlock (+130%)',
            tagClass: 'tag-severe',
            carMultiplier: 2.30,
            motoMultiplier: 1.38,
            trainPlatformDelay: 14,
            speedNoteCity: '~9 km/h (Gridlock Standstill)',
            speedNoteHwy: '~22 km/h (Bottlenecks)',
            desc: 'Severe Friday commuter exodus and mall traffic. Heavy bottlenecks on EDSA, C-5, and SLEX.'
          };
        }

        // Weekday Morning Rush Hour (7:00 AM - 10:00 AM)
        if (day >= 1 && day <= 5 && hour >= 7 && hour < 10) {
          return {
            conditionKey: 'auto',
            resolvedLevel: 'heavy',
            label: 'Morning Peak Rush',
            badgeText: '🔴 Morning Rush (+80%)',
            tagClass: 'tag-heavy',
            carMultiplier: 1.80,
            motoMultiplier: 1.25,
            trainPlatformDelay: 10,
            speedNoteCity: '~14 km/h (Heavy Congestion)',
            speedNoteHwy: '~32 km/h (Slow Flow)',
            desc: 'Inbound business district commute (Makati CBD, BGC, Ortigas, Manila). Heavy stop-and-go delays.'
          };
        }

        // Weekday Evening Rush Hour (5:00 PM - 8:30 PM)
        if (day >= 1 && day <= 5 && hour >= 17 && hour < 21) {
          return {
            conditionKey: 'auto',
            resolvedLevel: 'heavy',
            label: 'Evening Peak Rush',
            badgeText: '🔴 Evening Rush (+90%)',
            tagClass: 'tag-heavy',
            carMultiplier: 1.90,
            motoMultiplier: 1.28,
            trainPlatformDelay: 12,
            speedNoteCity: '~12 km/h (Heavy Congestion)',
            speedNoteHwy: '~28 km/h (Slow Flow)',
            desc: 'Outbound commuter traffic leaving office districts. Long waits on flyovers and major avenue intersections.'
          };
        }

        // Late Night / Midnight (10:00 PM - 5:30 AM)
        if (hour >= 22 || hour < 6) {
          return {
            conditionKey: 'auto',
            resolvedLevel: 'light',
            label: 'Late Night Free-Flow',
            badgeText: '🟢 Free-Flowing (+5%)',
            tagClass: 'tag-light',
            carMultiplier: 1.05,
            motoMultiplier: 1.00,
            trainPlatformDelay: 0,
            speedNoteCity: '~38 km/h (Clear Streets)',
            speedNoteHwy: '~70 km/h (Free Flow)',
            desc: 'Uncongested late-night streets. Cruising speed along expressways and surface avenues.'
          };
        }

        // Weekend Light (Sunday morning)
        if (day === 0 && hour < 13) {
          return {
            conditionKey: 'auto',
            resolvedLevel: 'light',
            label: 'Sunday Light Flow',
            badgeText: '🟢 Sunday Light (+10%)',
            tagClass: 'tag-light',
            carMultiplier: 1.10,
            motoMultiplier: 1.02,
            trainPlatformDelay: 0,
            speedNoteCity: '~34 km/h (Smooth)',
            speedNoteHwy: '~65 km/h (Free Flow)',
            desc: 'Sunday morning leisure traffic. Smooth transit across commercial and residential areas.'
          };
        }

        // Standard daytime (10:00 AM - 4:30 PM)
        return {
          conditionKey: 'auto',
          resolvedLevel: 'moderate',
          label: 'Midday Normal Flow',
          badgeText: '🟡 Moderate Flow (+35%)',
          tagClass: 'tag-moderate',
          carMultiplier: 1.35,
          motoMultiplier: 1.14,
          trainPlatformDelay: 4,
          speedNoteCity: '~24 km/h (Moderate Traffic)',
          speedNoteHwy: '~48 km/h (Moderate Flow)',
          desc: 'Typical urban flow with regular traffic light cycles and delivery/jeepney loading stops.'
        };
      }

      if (conditionKey === 'light') {
        return {
          conditionKey: 'light',
          resolvedLevel: 'light',
          label: 'Light / Off-Peak',
          badgeText: '🟢 Free Flow (+5%)',
          tagClass: 'tag-light',
          carMultiplier: 1.05,
          motoMultiplier: 1.00,
          trainPlatformDelay: 0,
          speedNoteCity: '~38 km/h (Clear Streets)',
          speedNoteHwy: '~70 km/h (Free Flow)',
          desc: 'Simulating late night or Sunday morning clear roads with minimal vehicle congestion.'
        };
      }

      if (conditionKey === 'moderate') {
        return {
          conditionKey: 'moderate',
          resolvedLevel: 'moderate',
          label: 'Moderate Traffic',
          badgeText: '🟡 Moderate Flow (+35%)',
          tagClass: 'tag-moderate',
          carMultiplier: 1.35,
          motoMultiplier: 1.14,
          trainPlatformDelay: 4,
          speedNoteCity: '~24 km/h (Moderate Traffic)',
          speedNoteHwy: '~48 km/h (Moderate Flow)',
          desc: 'Simulating regular daytime urban traffic with minor choke points and traffic light delays.'
        };
      }

      if (conditionKey === 'heavy') {
        return {
          conditionKey: 'heavy',
          resolvedLevel: 'heavy',
          label: 'Heavy Peak Rush',
          badgeText: '🔴 Rush Hour (+85%)',
          tagClass: 'tag-heavy',
          carMultiplier: 1.85,
          motoMultiplier: 1.25,
          trainPlatformDelay: 10,
          speedNoteCity: '~13 km/h (Heavy Congestion)',
          speedNoteHwy: '~30 km/h (Slow Flow)',
          desc: 'Simulating peak rush hour (7-9 AM, 5-8 PM). Significant bottlenecks and delays for 4-wheel vehicles.'
        };
      }

      // severe gridlock
      return {
        conditionKey: 'severe',
        resolvedLevel: 'severe',
        label: 'Severe Gridlock',
        badgeText: '🚨 Severe Gridlock (+130%)',
        tagClass: 'tag-severe',
        carMultiplier: 2.30,
        motoMultiplier: 1.38,
        trainPlatformDelay: 15,
        speedNoteCity: '~8-10 km/h (Standstill)',
        speedNoteHwy: '~18 km/h (Heavy Gridlock)',
        desc: 'Simulating extreme Friday night, holiday, or rainy season gridlock on Philippine roads.'
      };
    };

    const currentTrafficInfo = computed(() => {
      return getTrafficConfig(trafficCondition.value);
    });

    const travelEstimates = ref({
      car: {
        available: true,
        distanceKm: 0,
        durationMins: 0,
        etaFormatted: '0m',
        distanceFormatted: '0 km',
        badge: 'Shortest Road Network',
        speed: '~35 km/h',
        insight: 'Optimal road route computed via OSRM highway engine.'
      },
      motorcycle: {
        available: true,
        distanceKm: 0,
        durationMins: 0,
        etaFormatted: '0m',
        distanceFormatted: '0 km',
        badge: 'Fastest in Traffic',
        speed: '~45 km/h',
        insight: 'Agile lane filtering bypasses road congestion.'
      },
      bicycle: {
        available: true,
        distanceKm: 0,
        durationMins: 0,
        etaFormatted: '0m',
        distanceFormatted: '0 km',
        calories: 0,
        co2: '0',
        badge: 'Eco-Friendly',
        speed: '~16 km/h',
        insight: 'Zero carbon footprint cycling route.'
      },
      train: {
        available: false,
        distanceKm: 0,
        durationMins: 0,
        etaFormatted: 'N/A',
        distanceFormatted: '0 km',
        badge: 'No Direct Rail',
        speed: '~32 km/h transit',
        insight: 'No direct rail corridor to this destination.'
      },
      airplane: {
        available: false,
        distanceKm: 0,
        flightMins: 0,
        durationMins: 0,
        etaFormatted: 'N/A',
        distanceFormatted: '0 km',
        airportHub: 'NAIA',
        badge: 'Direct Flight',
        speed: '~650 km/h flight',
        insight: 'Commercial domestic flight with boarding buffer.'
      }
    });

    const activeModeData = computed(() => {
      return travelEstimates.value[selectedTravelMode.value] || travelEstimates.value.car;
    });

    // Modals instances
    let bookingModalInstance = null;
    let userBookingsModalInstance = null;
    let roomDetailsModalInstance = null;
    let confirmCancelModalInstance = null;
    let verifyAccountModalInstance = null;

    // Room Details Modal Target
    const roomDetailsHotel = ref(null);

    // Booking state
    const bookingTarget = ref(null);
    const bookingStep = ref('form'); // 'form', 'review', 'payment', 'success'
    const isSubmittingBooking = ref(false);
    const bookingErrors = ref({});
    const minDateTime = ref(getLocalNowISO());

    // Anti-Spam Reservation Down Payment & Online Payment Gateway State
    const selectedPaymentMethod = ref('gcash'); // 'gcash' | 'maya' | 'grabpay' | 'card'
    const isProcessingPayment = ref(false);
    const lastConfirmedBooking = ref(null);
    const payMethodMode = ref('number'); // 'number' | 'qr'
    const paymentForm = ref({
      accountNumber: '0917-829-4102',
      accountName: '<?= addslashes($userName) ?>',
      cardNumber: '4532 •••• •••• 8819',
      cardHolder: '<?= addslashes($userName) ?>',
      cardExpiry: '11/28',
      cardCvv: '•••'
    });

    const bookingForm = ref({
      guestName: '<?= addslashes($userName) ?>',
      guestPhone: '<?= addslashes($userPhone) ?>',
      selectedRoomId: '',
      bookingMode: 'hourly',
      stayDurationType: '3-Hour Wash Rate',
      nights: 1,
      hourlySlot: '3hr',
      checkInTime: getTomorrowLocalISO()
    });

    // Cancellation Target
    const cancelTarget = ref(null);

    // Reviews State
    const reviewsRoom = ref(null);
    const roomReviewsList = ref([]);
    const loadingReviews = ref(false);
    const submittingReview = ref(false);
    const reviewForm = ref({ rating: 5, comment: '' });
    let reviewsModalInstance = null;

    const getStarClass = (avg, n) => {
      const value = parseFloat(avg) || 0;
      if (value >= n) return 'bi-star-fill text-warning';
      if (value >= n - 0.5) return 'bi-star-half text-warning';
      return 'bi-star text-secondary text-opacity-50';
    };

    const openRoomReviewsModal = async (room) => {
      reviewsRoom.value = room;
      reviewForm.value = { rating: 5, comment: '' };
      roomReviewsList.value = [];
      
      if (!reviewsModalInstance) {
        reviewsModalInstance = new bootstrap.Modal(document.getElementById('roomReviewsModal'));
      }
      reviewsModalInstance.show();

      loadingReviews.value = true;
      try {
        const res = await axios.get(`api.php?action=get_room_reviews&room_id=${room.id}`);
        roomReviewsList.value = res.data.reviews || [];
        if (res.data.user_review) {
          reviewForm.value.rating = parseInt(res.data.user_review.rating);
          reviewForm.value.comment = res.data.user_review.comment;
        }
      } catch (e) {
        console.error('Error fetching reviews:', e);
      } finally {
        loadingReviews.value = false;
      }
    };

    const submitReview = async () => {
      if (!accountVerified.value) {
        showToast('Account Required', 'Please create a real account or login to leave a review.', 'bi-exclamation-triangle-fill text-warning');
        return;
      }
      if (!reviewForm.value.comment.trim()) {
        showToast('Empty Review', 'Please write a comment before submitting.', 'bi-info-circle-fill text-warning');
        return;
      }
      submittingReview.value = true;
      try {
        const payload = {
          room_id: reviewsRoom.value.id,
          rating: reviewForm.value.rating,
          comment: reviewForm.value.comment
        };
        const res = await axios.post('api.php?action=submit_room_review', payload);
        if (res.data.status === 'success') {
          showToast('Review Submitted', res.data.message || 'Thank you for your feedback!', 'bi-check-circle-fill text-success');
          // Reload reviews
          const resList = await axios.get(`api.php?action=get_room_reviews&room_id=${reviewsRoom.value.id}`);
          roomReviewsList.value = resList.data.reviews || [];
          if (resList.data.user_review) {
            reviewForm.value.rating = parseInt(resList.data.user_review.rating);
            reviewForm.value.comment = resList.data.user_review.comment;
          }
          // Also reload data to update averages
          loadData();
        } else {
          showToast('Notice', res.data.message || 'Could not submit review.', 'bi-info-circle-fill text-warning');
        }
      } catch (err) {
        showToast('Error', 'Failed to submit review.', 'bi-exclamation-triangle-fill text-danger');
      } finally {
        submittingReview.value = false;
      }
    };

    // Action Toast State
    const activeToast = ref(null);
    const showToast = (title, message, iconClass, actionBtn) => {
      activeToast.value = {
        title,
        message,
        iconClass: iconClass || 'bi-check-circle-fill text-success',
        actionButton: actionBtn || null
      };
      setTimeout(() => {
        if (activeToast.value?.title === title) {
          activeToast.value = null;
        }
      }, 6500);
    };

    // Filter & Search Controls
    const selectedDistrict = ref('all');
    const selectedBranchId = ref('all');
    const onlyAvailableRooms = ref(false);
    const districtsList = computed(() => {
      const defaultOrder = [
        'Makati CBD',
        'BGC Taguig',
        'Tagaytay',
        'Nasugbu Batangas',
        'Batangas',
        'Cebu City',
        'Clark Pampanga',
        'Iloilo City',
        'Quezon City',
        'Cubao Quezon City',
        'Novaliches QC',
        'Pasay & Manila Bay',
        'Pasay City',
        'Entertainment City',
        'Ortigas Center',
        'Historic Manila',
        'Alabang Muntinlupa'
      ];
      const existing = new Set((stays.value || []).map(s => s.city).filter(Boolean));
      const result = defaultOrder.filter(d => existing.has(d));
      existing.forEach(c => {
        if (!result.includes(c)) result.push(c);
      });
      return result;
    });

    const countHotels = computed(() => 
      (stays.value || []).filter(s => ['hotel', 'casino', 'resort', 'heritage'].includes(s.category)).length
    );
    const countMotelsAndRentals = computed(() => 
      (stays.value || []).filter(s => ['motel', 'sogo', 'rental'].includes(s.category)).length
    );
    const countAirbnbs = computed(() => 
      (stays.value || []).filter(s => s.category === 'airbnb').length
    );

    const countByCategory = (cat) => stays.value.filter(s => s.category === cat).length;
    const countByDistrict = (dist) => stays.value.filter(s => s.city === dist).length;

    // Commuter count: hotels that have a real terminal set
    const commuterCount = computed(() => 
      stays.value.filter(s => s.nearest_terminal && s.nearest_terminal !== 'Commuter Hub').length
    );

    const resetFilters = () => {
      searchQuery.value = '';
      selectedCategory.value = 'all';
      selectedDistrict.value = 'all';
      selectedBranchId.value = 'all';
      onlyAvailableRooms.value = false;
      sortBy.value = 'featured';
    };

    // Helpers for Room Status display
    const getRoomStatusBadgeClass = (status) => {
      if (status === 'Available') return 'bg-success bg-opacity-25 text-success border border-success border-opacity-35';
      if (status === 'Sold Out') return 'bg-danger bg-opacity-25 text-danger border border-danger border-opacity-35';
      if (status === 'Occupied') return 'bg-info bg-opacity-25 text-info border border-info border-opacity-35';
      if (status === 'Reserved') return 'bg-warning bg-opacity-25 text-warning border border-warning border-opacity-35';
      if (status === 'Under Maintenance') return 'bg-danger bg-opacity-25 text-danger border border-danger border-opacity-35';
      return 'bg-secondary text-light';
    };

    const getRoomStatusIcon = (status) => {
      if (status === 'Available') return 'bi-check-circle-fill text-success';
      if (status === 'Sold Out') return 'bi-slash-circle-fill text-danger';
      if (status === 'Occupied') return 'bi-person-fill-lock text-info';
      if (status === 'Reserved') return 'bi-clock-fill text-warning';
      if (status === 'Under Maintenance') return 'bi-tools text-danger';
      return 'bi-info-circle';
    };

    // Filtered & Sorted Hotels (matches Title, Branch Name, District, Address, Brand, Tags, and Room Suites)
    const filteredStays = computed(() => {
      return stays.value.filter(stay => {
        // Commuter-only filter
        if (commuterOnly.value && !(stay.nearest_terminal && stay.nearest_terminal !== 'Commuter Hub')) return false;

        // Category filter: Hotels, Motels & Rentals, or AirBnB
        if (selectedCategory.value === 'hotel') {
          if (!['hotel', 'casino', 'resort', 'heritage'].includes(stay.category)) return false;
        } else if (selectedCategory.value === 'motel') {
          if (!['motel', 'sogo', 'rental'].includes(stay.category)) return false;
        } else if (selectedCategory.value === 'airbnb') {
          if (stay.category !== 'airbnb') return false;
        }

        // Designated District filter
        if (selectedDistrict.value !== 'all' && stay.city !== selectedDistrict.value) return false;

        // Specific branch selector filter
        if (selectedBranchId.value !== 'all' && stay.id !== selectedBranchId.value) return false;

        // Available suites only filter
        if (onlyAvailableRooms.value && (stay.availableRoomsCount || 0) === 0) return false;

        // Search query
        if (searchQuery.value.trim() !== '') {
          const q = searchQuery.value.toLowerCase().trim();
          const titleMatch = (stay.title || '').toLowerCase().includes(q);
          const branchMatch = (stay.branch_name || '').toLowerCase().includes(q);
          const cityMatch = (stay.city || '').toLowerCase().includes(q);
          const addressMatch = (stay.address || '').toLowerCase().includes(q);
          const brandMatch = (stay.brand || '').toLowerCase().includes(q);
          const tagsMatch = (stay.tags || []).some(t => t.toLowerCase().includes(q));
          const roomsMatch = (stay.rooms || []).some(r => 
            (r.room_name || '').toLowerCase().includes(q) || 
            (r.room_number || '').toLowerCase().includes(q) || 
            (r.room_type || '').toLowerCase().includes(q) ||
            (r.amenities || '').toLowerCase().includes(q)
          );
          return titleMatch || branchMatch || cityMatch || addressMatch || brandMatch || tagsMatch || roomsMatch;
        }
        return true;
      }).sort((a, b) => {
        if (sortBy.value === 'nearest') return getStayDistanceKm(a) - getStayDistanceKm(b);
        if (sortBy.value === 'rating') return (b.rating || 0) - (a.rating || 0);
        if (sortBy.value === 'views') return (b.viewsCount || 0) - (a.viewsCount || 0);
        if (sortBy.value === 'bookings') return (b.totalBooked || 0) - (a.totalBooked || 0);
        if (sortBy.value === 'price-asc') return (a.price || 0) - (b.price || 0);
        if (sortBy.value === 'price-desc') return (b.price || 0) - (a.price || 0);
        return 0;
      });
    });

    // Selected Room Details — all guarded for null bookingTarget
    const selectedRoomPrice = computed(() => {
      if (!bookingTarget.value) return 0;
      if (!bookingTarget.value.rooms || !bookingTarget.value.rooms.length) {
        return Number(bookingTarget.value.price) || 0;
      }
      const rm = bookingTarget.value.rooms.find(r => r.id === bookingForm.value.selectedRoomId);
      return Number(rm ? rm.price : (bookingTarget.value.rooms[0]?.price ?? bookingTarget.value.price ?? 0)) || 0;
    });

    const selectedRoomName = computed(() => {
      if (!bookingTarget.value) return '';
      if (!bookingTarget.value.rooms || !bookingTarget.value.rooms.length) return 'Room';
      const rm = bookingTarget.value.rooms.find(r => r.id === bookingForm.value.selectedRoomId);
      return rm ? (rm.room_name || 'Room') : (bookingTarget.value.rooms[0]?.room_name || 'Room');
    });

    const selectedRoomNumber = computed(() => {
      if (!bookingTarget.value || !bookingTarget.value.rooms) return '';
      const rm = bookingTarget.value.rooms.find(r => r.id === bookingForm.value.selectedRoomId);
      return rm ? (rm.room_number || '') : (bookingTarget.value.rooms[0]?.room_number || '');
    });

    // Property category detectors for booking modal
    const isHotelTarget = computed(() => {
      if (!bookingTarget.value) return false;
      return ['hotel', 'casino', 'resort', 'heritage'].includes(bookingTarget.value.category);
    });

    const isMotelOrRentalTarget = computed(() => {
      if (!bookingTarget.value) return false;
      return ['motel', 'sogo', 'rental'].includes(bookingTarget.value.category);
    });

    // Determines if the booking target uses hourly (walk-in) rates
    const isHourlyTarget = computed(() => {
      if (!bookingTarget.value) return false;
      return isMotelOrRentalTarget.value && bookingTarget.value.hourly_rates;
    });

    // Mode Switcher between hourly and nightly
    const setBookingMode = (mode) => {
      bookingForm.value.bookingMode = mode;
      if (mode === 'hourly') {
        onHourlySlotChange();
      } else {
        const n = bookingForm.value.nights || 1;
        bookingForm.value.stayDurationType = `${n} Night${n > 1 ? 's' : ''} Stay`;
      }
    };

    const onNightsChange = () => {
      const n = bookingForm.value.nights || 1;
      bookingForm.value.stayDurationType = `${n} Night${n > 1 ? 's' : ''} Stay`;
    };

    // Compute a rate for non-explicit slots like 7hr and 8hr by interpolating
    const computedHourlyRate = (slot) => {
      if (!bookingTarget.value || !bookingTarget.value.hourly_rates) return 0;
      const rates = bookingTarget.value.hourly_rates;
      if (rates[slot]) return rates[slot];
      // For 7hr: interpolate between 6hr and 12hr
      if (slot === '7hr' && rates['6hr'] && rates['12hr']) {
        return Math.round(rates['6hr'] + (rates['12hr'] - rates['6hr']) * (1/6));
      }
      // For 8hr: interpolate between 6hr and 12hr
      if (slot === '8hr' && rates['6hr'] && rates['12hr']) {
        return Math.round(rates['6hr'] + (rates['12hr'] - rates['6hr']) * (2/6));
      }
      return rates['6hr'] || 0;
    };

    // When user changes the hourly slot selector, also sync stayDurationType
    const onHourlySlotChange = () => {
      const slotToType = {
        '2hr': '2-Hour Express Wash', '3hr': '3-Hour Wash Rate',
        '6hr': '6-Hour Mid Stay', '7hr': '7-Hour Stay',
        '8hr': '8-Hour Stay', '12hr': '12-Hour Layover', '24hr': '24-Hour Overnight'
      };
      bookingForm.value.stayDurationType = slotToType[bookingForm.value.hourlySlot] || '3-Hour Wash Rate';
    };

    const calculateTotalAmount = computed(() => {
      if (!bookingTarget.value) return 0;
      if (isMotelOrRentalTarget.value && bookingForm.value.bookingMode === 'hourly') {
        const slot = bookingForm.value.hourlySlot || '3hr';
        const rates = bookingTarget.value.hourly_rates || {};
        if (rates[slot]) return Number(rates[slot]);
        return computedHourlyRate(slot);
      }
      const price = Number(selectedRoomPrice.value) || 0;
      const nights = Math.max(1, Number(bookingForm.value.nights) || 1);
      return price * nights;
    });

    const computedDownpaymentAmount = computed(() => {
      const total = Number(calculateTotalAmount.value || 0);
      if (total <= 0) return 150;
      // 20% down payment deposit to verify guest & prevent fake/bot spam, min 150
      return Math.max(150, Math.round(total * 0.20));
    });

    const computedBalanceDue = computed(() => {
      const total = Number(calculateTotalAmount.value || 0);
      const down = computedDownpaymentAmount.value;
      return Math.max(0, total - down);
    });

    const getPaymentBadgeBg = (method) => {
      const m = (method || '').toLowerCase();
      if (m === 'gcash') return '#007DFE';
      if (m === 'maya' || m === 'paymaya') return '#289F4C';
      if (m === 'grabpay') return '#00B14F';
      if (m === 'card') return '#6366F1';
      return '#475569';
    };

    // ── PANNELLUM 360° VIEWER STATE ──────────────────────────────────
    // Two-way sync: Vue ↔ Pannellum ↔ Leaflet
    let pannellumViewer = null;
    const pannellumActive = ref(false);
    const pannellumCurrentScene = ref('');
    const pannellumAutoRotating = ref(false);
    const pannellumContainerId = 'pannellum-container';

    // Pannellum fallback scene config - Rock-Solid Walk-Through Portal Hotspots
    const PANNELLUM_SCENES = {
      bedroom: {
        title: 'Master Bedroom Suite',
        mapLabel: 'Master Bedroom',
        panorama: 'img/pano_bedroom.jpg',
        hotSpots: [
          {
            id: 'to_bathroom',
            pitch: -3,
            yaw: -124,
            type: 'scene',
            sceneId: 'bathroom',
            cssClass: 'pano-portal-hs',
            createTooltipFunc: (div) => {
              div.innerHTML = '<span class="portal-badge"><i class="bi bi-door-open-fill"></i></span><span class="portal-name">Ensuite Bathroom</span><i class="bi bi-chevron-right portal-arrow"></i>';
            },
            clickHandlerFunc: () => switchPannellumScene('bathroom')
          }
        ]
      },
      bathroom: {
        title: 'Ensuite Luxury Bathroom',
        mapLabel: 'Ensuite Bathroom',
        panorama: 'img/pano_bathroom.jpg',
        hotSpots: [
          {
            id: 'to_bedroom',
            pitch: -3,
            yaw: 118,
            type: 'scene',
            sceneId: 'bedroom',
            cssClass: 'pano-portal-hs',
            createTooltipFunc: (div) => {
              div.innerHTML = '<span class="portal-badge"><i class="bi bi-door-open-fill"></i></span><span class="portal-name">Back to Room</span><i class="bi bi-chevron-right portal-arrow"></i>';
            },
            clickHandlerFunc: () => switchPannellumScene('bedroom')
          }
        ]
      }
    };

    // Dynamically compute scenes from active stay's showroom
    const activePannellumScenes = computed(() => {
      const stay = roomDetailsHotel.value;
      if (!stay || !stay.showroom || !stay.showroom.rooms) {
        return PANNELLUM_SCENES;
      }
      const result = {};
      const rooms = stay.showroom.rooms;
      Object.entries(rooms).forEach(([rKey, rCfg]) => {
        const hotSpots = (rCfg.hotspots || []).map((spot, idx) => {
          const yaw = Math.round(((spot.x || 50) - 50) * 3.6);
          const pitch = Math.round(((spot.y || 50) - 50) * -0.9);
          const spotLabel = spot.label || ('Go to ' + spot.targetRoomId);
          return {
            id: 'hs_' + rKey + '_' + idx,
            pitch: pitch,
            yaw: yaw,
            type: 'scene',
            sceneId: spot.targetRoomId || spot.target,
            cssClass: 'pano-portal-hs',
            createTooltipFunc: (div) => {
              div.innerHTML = `<span class="portal-badge"><i class="bi bi-door-open-fill"></i></span><span class="portal-name">${spotLabel}</span><i class="bi bi-chevron-right portal-arrow"></i>`;
            }
          };
        });

        result[rKey] = {
          title: rCfg.title || rKey,
          type: 'equirectangular',
          panorama: rCfg.image_360 || 'img/pano_bedroom.jpg',
          hotSpots: hotSpots
        };
      });
      return Object.keys(result).length > 0 ? result : PANNELLUM_SCENES;
    });

    /**
     * Boot (or re-init) Pannellum on a given scene.
     * Destroys any existing instance first to avoid DOM conflicts.
     */
    const initPannellum = (startScene = null) => {
      if (typeof pannellum === 'undefined') {
        console.warn('[Pannellum] library not loaded yet');
        return;
      }
      // Destroy previous instance
      if (pannellumViewer) {
        try { pannellumViewer.destroy(); } catch(e) {}
        pannellumViewer = null;
      }

      const scenesObj = activePannellumScenes.value;
      const initialKey = startScene || roomDetailsHotel.value?.showroom?.initial_room || Object.keys(scenesObj)[0] || 'bedroom';

      pannellumActive.value = true;
      pannellumCurrentScene.value = initialKey;
      pannellumAutoRotating.value = false;

      // Build multi-scene config
      const scenes = {};
      Object.entries(scenesObj).forEach(([sceneId, cfg]) => {
        scenes[sceneId] = {
          title: cfg.title,
          type: 'equirectangular',
          panorama: cfg.panorama,
          hotSpots: cfg.hotSpots
        };
      });

      nextTick(() => {
        const el = document.getElementById(pannellumContainerId);
        if (!el) return;

        try {
          pannellumViewer = pannellum.viewer(pannellumContainerId, {
            default: {
              firstScene: initialKey,
              sceneFadeDuration: 0,
              autoLoad: true,
              autoRotate: 0,
              showControls: false,
              mouseZoom: true,
              hfov: 100
            },
            scenes
          });

          // ── PANNELLUM → VUE → LEAFLET sync ──────────────────────────
          pannellumViewer.on('scenechange', (newSceneId) => {
            pannellumCurrentScene.value = newSceneId;
            if (roomDetailsHotel.value && map) {
              const stay = roomDetailsHotel.value;
              const lat = parseFloat(stay.lat);
              const lng = parseFloat(stay.lng);
              if (!isNaN(lat) && !isNaN(lng)) {
                map.panTo([lat, lng], { animate: true, duration: 0.8 });
                const marker = markersMap[stay.id];
                if (marker) {
                  marker.openPopup();
                }
              }
            }
            showToast(
              '360° Scene Changed',
              `Now viewing: ${scenesObj[newSceneId]?.title || newSceneId}`,
              'bi-badge-3d text-primary'
            );
          });

          pannellumViewer.on('error', (err) => {
            console.warn('[Pannellum] Warning during load:', err);
          });
        } catch (e) {
          console.error('[Pannellum] init error:', e);
        }
      });
    };

    /**
     * Switch Pannellum to a specific scene.
     */
    const switchPannellumScene = (sceneId) => {
      const scenesObj = activePannellumScenes.value;
      if (!scenesObj[sceneId]) return;
      pannellumCurrentScene.value = sceneId;
      if (pannellumViewer) {
        try {
          pannellumViewer.loadScene(sceneId);
          setTimeout(() => {
            try {
              if (pannellumViewer && pannellumViewer.getScene() !== sceneId) {
                initPannellum(sceneId);
              }
            } catch(checkErr) {
              initPannellum(sceneId);
            }
          }, 150);
        } catch(e) {
          initPannellum(sceneId);
        }
      } else {
        initPannellum(sceneId);
      }
    };

    const destroyPannellum = () => {
      if (pannellumViewer) {
        try { pannellumViewer.destroy(); } catch(e) {}
        pannellumViewer = null;
      }
      pannellumActive.value = false;
      pannellumCurrentScene.value = '';
      pannellumAutoRotating.value = false;
    };

    const togglePannellumAutoRotate = () => {
      pannellumAutoRotating.value = !pannellumAutoRotating.value;
      if (pannellumViewer) {
        if (pannellumAutoRotating.value) {
          pannellumViewer.startAutoRotate(-2);
        } else {
          pannellumViewer.stopAutoRotate();
        }
      }
    };

    const zoomPannellum = (delta) => {
      if (!pannellumViewer) return;
      const fov = pannellumViewer.getHfov() || 100;
      pannellumViewer.setHfov(Math.max(45, Math.min(125, fov + delta)));
    };

    const resetPannellumView = () => {
      if (!pannellumViewer) return;
      pannellumViewer.setPitch(0);
      pannellumViewer.setYaw(0);
      pannellumViewer.setHfov(100);
    };

    const togglePannellumFullscreen = () => {
      if (!pannellumViewer) return;
      pannellumViewer.toggleFullscreen();
    };
    // ── END PANNELLUM ──────────────────────────────────────────────

    // Initialize Map
    const initMap = () => {
      if (map) return;
      const container = document.getElementById('leafletMap');
      if (!container) return;

      tileLayers = {
        satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { 
          maxZoom: 19,
          attribution: '&copy; Esri &mdash; World Imagery'
        }),
        street: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
          maxZoom: 19,
          attribution: '&copy; OpenStreetMap contributors'
        }),
        dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { 
          maxZoom: 19,
          attribution: '&copy; CartoDB'
        })
      };

      map = L.map(container, {
        center: [12.8797, 121.7740], /* Center of Philippines */
        zoom: 6,
        zoomControl: false,
        layers: [tileLayers[currentTileLayer.value] || tileLayers.satellite]
      });

      L.control.zoom({ position: 'topright' }).addTo(map);
      markersLayer = L.layerGroup().addTo(map);

      setTimeout(() => { if (map) map.invalidateSize(); }, 150);
      setTimeout(() => { if (map) map.invalidateSize(); }, 500);

      window.addEventListener('resize', () => {
        if (map) map.invalidateSize();
      });

      document.addEventListener('hidden.bs.modal', () => {
        if (map) {
          setTimeout(() => { map.invalidateSize(); }, 100);
        }
      });

      renderMapMarkers();
    };

    const switchMapLayer = (name) => {
      if (!map || !tileLayers[name]) return;
      Object.values(tileLayers).forEach(layer => {
        if (map.hasLayer(layer)) map.removeLayer(layer);
      });
      map.addLayer(tileLayers[name]);
      currentTileLayer.value = name;
    };

    const renderMapMarkers = () => {
      if (!map || !markersLayer) return;
      markersLayer.clearLayers();
      markersMap = {};

      const currentList = filteredStays.value || [];
      currentList.forEach(stay => {
        const lat = parseFloat(stay.lat);
        const lng = parseFloat(stay.lng);
        if (isNaN(lat) || isNaN(lng)) return;

        const isDest = selectedStay.value && selectedStay.value.id === stay.id;
        const customIcon = L.divIcon({
          className: 'leaflet-custom-marker',
          html: `<div class="map-price-bubble bubble-${stay.category || 'hotel'} ${isDest ? 'is-destination' : ''}"><span>₱${Number(stay.price).toLocaleString()}</span></div>`,
          iconSize: [95, 32],
          iconAnchor: [47, 16]
        });

        const isSaved = (savedLocations.value || []).includes(String(stay.id));
        
        const marker = L.marker([lat, lng], { icon: customIcon });
        const popupContent = `
          <div style="min-width: 255px; font-family: 'Plus Jakarta Sans', sans-serif;">
            <div style="font-weight: 800; font-size: 13.5px; margin-bottom: 3px; color: var(--mocha);">${stay.title}</div>
            <div style="display:flex; align-items:center; gap:6px; margin-bottom:6px; flex-wrap:wrap;">
              <span style="background: rgba(200,113,74,0.1); color: var(--terracotta); border: 1px solid rgba(200,113,74,0.3); padding: 2px 7px; border-radius: 4px; font-size: 10px; font-weight: 700;">
                <i class="bi bi-geo-alt-fill me-1"></i>${stay.branch_name || 'Main Branch'}
              </span>
              <span style="background: var(--cream-deep); color: var(--text-secondary); padding: 2px 6px; border-radius: 4px; font-size: 10px;">
                ${stay.city}
              </span>
            </div>
            <div style="font-size: 10.5px; color: var(--text-secondary); margin-bottom: 4px;">
              <i class="bi bi-pin-map text-primary me-1"></i>${stay.address}
            </div>
            <div style="font-family: monospace; font-size: 9.5px; color: var(--text-muted); margin-bottom: 8px;">
              <i class="bi bi-crosshair text-info me-1"></i>Coords: ${Number(stay.lat).toFixed(6)}, ${Number(stay.lng).toFixed(6)}
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 6px; margin-bottom: 8px;">
              <div>
                <span style="font-size: 9.5px; color: var(--text-muted); display:block;">FROM</span>
                <span style="font-size: 13.5px; color: var(--sage); font-weight: 800;">₱${Number(stay.price).toLocaleString()}</span>
              </div>
              <div style="text-align: right;">
                <span style="font-size: 10px; font-weight: 700; color: ${stay.availableRoomsCount > 0 ? '#fff' : '#fff'}; background: ${stay.availableRoomsCount > 0 ? 'var(--sage)' : '#D95F5F'}; padding: 3px 7px; border-radius: 4px; display:inline-block;">
                  ${stay.availableRoomsCount > 0 ? '🟢 ' + stay.availableRoomsCount + ' Available' : '🔴 Fully Booked'}
                </span>
              </div>
            </div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
              <button onclick="window.vueApp.openRoomDetailsFromId('${stay.id}')" style="flex:1; background: var(--terracotta); color: #fff; border:none; border-radius:6px; font-size:11px; font-weight:600; padding:6px 8px; cursor:pointer;" title="View Suite Options">
                <i class="bi bi-door-open-fill me-1"></i>Suites & Book
              </button>
              <button onclick="window.vueApp.quickRouteToId('${stay.id}')" style="background: var(--bg-card); color: var(--sage); border:1px solid var(--border); border-radius:6px; font-size:11px; font-weight:600; padding:6px 10px; cursor:pointer;" title="Route to Destination">
                <i class="bi bi-sign-turn-right-fill"></i>
              </button>
              <button onclick="window.vueApp.saveDestination('${stay.id}')" style="background: ${isSaved ? 'var(--danger)' : 'var(--bg-card)'}; color: ${isSaved ? '#fff' : 'var(--danger)'}; border:1px solid ${isSaved ? 'var(--danger)' : 'var(--border)'}; border-radius:6px; font-size:11px; font-weight:600; padding:6px 10px; cursor:pointer;" title="Toggle Favorite">
                <i class="bi ${isSaved ? 'bi-heart-fill' : 'bi-heart'}"></i>
              </button>
            </div>
          </div>
        `;
        marker.bindPopup(popupContent);
        marker.on('click', () => { 
          locateDestination(stay, false);
        });
        markersLayer.addLayer(marker);
        markersMap[stay.id] = marker;
      });
    };

    const highlightDestinationMarker = (stay) => {
      if (!stay) return;
      document.querySelectorAll('.map-price-bubble').forEach(el => {
        el.classList.remove('is-destination');
      });

      if (markersMap[stay.id]) {
        const markerEl = markersMap[stay.id].getElement();
        if (markerEl) {
          const bubble = markerEl.querySelector('.map-price-bubble');
          if (bubble) bubble.classList.add('is-destination');
        }
      }
    };

    const locateDestination = (stay, userTriggered = true) => {
      if (!stay) return;
      selectedStay.value = stay;
      incrementViews(stay);

      const lat = parseFloat(stay.lat);
      const lng = parseFloat(stay.lng);
      if (map && !isNaN(lat) && !isNaN(lng)) {
        // High-definition destination close-up zoom (level 16)
        map.flyTo([lat, lng], 16, { animate: true, duration: 1.1 });

        // Highlight marker with pulsating blue beacon
        highlightDestinationMarker(stay);

        // Open marker popup
        setTimeout(() => {
          if (markersMap[stay.id]) {
            markersMap[stay.id].openPopup();
          }
        }, 320);

        // On mobile or small screens, scroll viewport to map container
        if (window.innerWidth < 992) {
          const mapEl = document.querySelector('.map-pane-container');
          if (mapEl) {
            mapEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        }

        if (userTriggered) {
          showToast(
            'Destination Located',
            `Centered on ${stay.title} (${stay.branch_name || stay.city}) with 16x zoom view.`,
            'bi-geo-alt-fill text-info',
            { label: 'View Suites', handler: () => openRoomDetailsModal(stay) }
          );
        }
      }
    };

    const zoomToDestination = (stay) => {
      locateDestination(stay || directionsTarget.value, true);
    };

    const selectHotelCard = (stay) => {
      locateDestination(stay, false);
    };

    const locateFromModal = (hotel) => {
      if (!hotel) return;
      roomDetailsModalInstance.hide();
      setTimeout(() => {
        locateDestination(hotel, true);
      }, 300);
    };

    const onBranchChange = () => {
      if (selectedBranchId.value && selectedBranchId.value !== 'all') {
        const stay = stays.value.find(s => s.id === selectedBranchId.value);
        if (stay) {
          locateDestination(stay, true);
        }
      } else {
        fitAllPins();
      }
    };

    const locateUserPosition = () => {
      if (!navigator.geolocation) {
        showToast('GPS Unsupported', 'Geolocation is not supported by your browser.', 'bi-exclamation-triangle text-warning');
        return;
      }

      isLocatingUser.value = true;
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          isLocatingUser.value = false;
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          userLocation.value = [lat, lng];

          if (userLocationMarker && map) map.removeLayer(userLocationMarker);
          const userGpsIcon = L.divIcon({
            className: 'leaflet-custom-marker',
            html: '<div class="user-gps-marker"><div class="user-gps-pulse"></div><div class="user-gps-dot"></div></div>',
            iconSize: [26, 26],
            iconAnchor: [13, 13]
          });
          userLocationMarker = L.marker([lat, lng], { icon: userGpsIcon, zIndexOffset: 1200 }).addTo(map);
          userLocationMarker.bindPopup(`
            <div style="font-family:'Inter',sans-serif; text-align:center; padding:5px;">
              <div style="color:#38bdf8; font-weight:800; font-size:12px; margin-bottom:2px;"><i class="bi bi-crosshair me-1"></i>Your Current Location</div>
              <div style="font-size:10.5px; color:#cbd5e1;">Coords: ${lat.toFixed(4)}, ${lng.toFixed(4)}</div>
              <div style="font-size:9.5px; color:#94a3b8; margin-top:2px;">Accuracy: ±${Math.round(pos.coords.accuracy)}m</div>
            </div>
          `).openPopup();

          if (selectedStay.value) {
            const destLat = parseFloat(selectedStay.value.lat);
            const destLng = parseFloat(selectedStay.value.lng);
            if (!isNaN(destLat) && !isNaN(destLng)) {
              map.fitBounds([[lat, lng], [destLat, destLng]], { padding: [65, 65] });
            }
          } else {
            map.flyTo([lat, lng], 15, { duration: 1.0 });
          }

          showToast('GPS Location Detected', 'Your current coordinates have been mapped successfully.', 'bi-check-circle-fill text-success');
        },
        (err) => {
          isLocatingUser.value = false;
          console.warn('Geolocation notice:', err.message);
          showToast('GPS Notice', 'Unable to detect location. Using NAIA Terminal 3 Airport as reference.', 'bi-info-circle text-info');
        },
        { enableHighAccuracy: true, timeout: 8000 }
      );
    };

    const incrementViews = async (stay) => {
      try {
        const res = await axios.post('api.php?action=increment_views', { id: stay.id });
        if (res.data && res.data.views_count) {
          stay.viewsCount = res.data.views_count;
        }
      } catch (e) {
        stay.viewsCount++;
      }
    };

    const fitAllPins = () => {
      if (!map || !filteredStays.value || filteredStays.value.length === 0) return;
      const validCoords = filteredStays.value
        .map(s => [parseFloat(s.lat), parseFloat(s.lng)])
        .filter(c => !isNaN(c[0]) && !isNaN(c[1]));
      if (validCoords.length > 0) {
        map.fitBounds(L.latLngBounds(validCoords), { padding: [50, 50] });
      }
    };

    // ── MULTI-MODAL GPS ROUTING & AUTO-TRACKING ENGINE ───────────
    function getCurrentOriginCoords() {
      if (originPreset.value && originPreset.value !== 'gps' && ORIGIN_PRESETS[originPreset.value]) {
        return ORIGIN_PRESETS[originPreset.value].coords;
      }
      return userLocation.value ? userLocation.value : ORIGIN_PRESETS.naia.coords;
    }

    function getCurrentOriginLabel() {
      if (originPreset.value && originPreset.value !== 'gps' && ORIGIN_PRESETS[originPreset.value]) {
        return ORIGIN_PRESETS[originPreset.value].label;
      }
      return userLocation.value ? 'Your Live GPS' : 'NAIA T3 (GPS Default)';
    }

    // Haversine distance formula in kilometers
    function calculateDistanceKm(lat1, lon1, lat2, lon2) {
      const R = 6371; // Earth radius in km
      const dLat = (lat2 - lat1) * Math.PI / 180;
      const dLon = (lon2 - lon1) * Math.PI / 180;
      const a = 
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      return R * c;
    }

    function getStayDistanceKm(stay) {
      if (!stay) return 999999;
      const origin = getCurrentOriginCoords();
      const sLat = parseFloat(stay.lat);
      const sLng = parseFloat(stay.lng);
      if (isNaN(sLat) || isNaN(sLng) || !origin) return 999999;
      return calculateDistanceKm(origin[0], origin[1], sLat, sLng);
    }

    function getStayDistanceText(stay) {
      const d = getStayDistanceKm(stay);
      if (d >= 99999) return 'N/A';
      const estMins = Math.max(3, Math.round((d / 22) * 60)); // ~22 km/h city average
      if (d < 1) {
        return `${Math.round(d * 1000)}m (~${estMins}m drive)`;
      }
      return `${d.toFixed(1)} km (~${estMins}m drive)`;
    }

    const nearestConvenientStays = computed(() => {
      const list = [...(stays.value || [])].filter(s => {
        const lat = parseFloat(s.lat);
        const lng = parseFloat(s.lng);
        return !isNaN(lat) && !isNaN(lng);
      });
      list.sort((a, b) => getStayDistanceKm(a) - getStayDistanceKm(b));
      return list.slice(0, 8);
    });

    const toggleNearMeSort = () => {
      if (sortBy.value === 'nearest') {
        sortBy.value = 'featured';
        showToast('Sorting Reset', 'Displaying standard featured accommodation order.', 'bi-filter text-info');
      } else {
        sortBy.value = 'nearest';
        if (!userLocation.value) {
          locateUserPosition();
        } else {
          showToast('Nearest Stays Sorted', 'Accommodations are now sorted by proximity to your current location.', 'bi-geo-alt-fill text-danger');
        }
      }
    };

    const formatDuration = (mins) => {
      if (!mins || mins <= 0) return '0m';
      if (mins < 60) return `${mins}m`;
      const h = Math.floor(mins / 60);
      const m = mins % 60;
      return m > 0 ? `${h}h ${m}m` : `${h}h`;
    };

    const createFlightArc = (start, end, numPoints = 40) => {
      const points = [];
      const lat1 = start[0], lon1 = start[1];
      const lat2 = end[0], lon2 = end[1];
      const dLat = lat2 - lat1;
      const dLon = lon2 - lon1;
      const dist = Math.hypot(dLat, dLon);
      const bulge = dist * 0.16; // parabolic curve curvature

      const pLat = -dLon / (dist || 1);
      const pLon = dLat / (dist || 1);

      for (let i = 0; i <= numPoints; i++) {
        const t = i / numPoints;
        const curLat = lat1 + t * dLat;
        const curLon = lon1 + t * dLon;
        const arcOffset = Math.sin(t * Math.PI) * bulge;
        points.push([curLat + pLat * arcOffset, curLon + pLon * arcOffset]);
      }
      return points;
    };

    const computeMultiModalEstimates = (roadDistKm, rawDurationMins, straightDistKm, origin, destination) => {
      // Cache raw route parameters for instant simulation when user switches traffic conditions
      lastRawRouteData = { roadDistKm, rawDurationMins, straightDistKm, origin, destination };

      const traffic = currentTrafficInfo.value;

      // Base non-traffic driving time from OSRM or expressway/city road model
      const baseCarMins = Math.max(5, Math.round(rawDurationMins || (roadDistKm > 25 ? roadDistKm * 1.1 + 8 : roadDistKm * 2.2 + 3)));

      // 1. Car (Driving with dynamic traffic multiplier)
      const currentCarMins = Math.max(5, Math.round(baseCarMins * traffic.carMultiplier));
      const carTrafficDelayMins = Math.max(0, currentCarMins - baseCarMins);

      const minCarMins = Math.max(5, Math.round(baseCarMins * 1.05));
      const maxCarMins = Math.max(minCarMins + 5, Math.round(baseCarMins * 2.30));

      const carSpeed = roadDistKm > 25 ? traffic.speedNoteHwy : traffic.speedNoteCity;

      let carInsight = '';
      if (carTrafficDelayMins > 8) {
        carInsight = `Includes ~+${carTrafficDelayMins}m traffic delay under ${traffic.label.toLowerCase()}. Surface chokepoints and highway ramps significantly delay 4-wheel vehicles.`;
      } else {
        carInsight = `Smooth road transit with minimal congestion delay under ${traffic.label.toLowerCase()}.`;
      }

      travelEstimates.value.car = {
        available: true,
        distanceKm: roadDistKm,
        durationMins: currentCarMins,
        baseMins: baseCarMins,
        trafficDelayMins: carTrafficDelayMins,
        trafficDelayText: carTrafficDelayMins > 0 ? `+${carTrafficDelayMins}m traffic delay` : 'Clear traffic',
        rangeText: `Traffic Range: ${formatDuration(minCarMins)} (light) – ${formatDuration(maxCarMins)} (gridlock)`,
        etaFormatted: formatDuration(currentCarMins),
        distanceFormatted: `${roadDistKm} km`,
        badge: 'Shortest Road Network',
        speed: carSpeed,
        insight: carInsight
      };

      // 2. Motorcycle (Agile lane-filtering in PH urban traffic)
      const baseMotoMins = Math.max(4, Math.round(baseCarMins * 0.72));
      const currentMotoMins = Math.max(4, Math.round(baseMotoMins * traffic.motoMultiplier));
      const motoTrafficDelayMins = Math.max(0, currentMotoMins - baseMotoMins);
      const motoTimeSavedVsCar = Math.max(0, currentCarMins - currentMotoMins);

      const motoSpeed = roadDistKm > 25 ? '~55 km/h' : '~38 km/h';

      let motoInsight = '';
      if (motoTimeSavedVsCar >= 6) {
        motoInsight = `⚡ Lane-filtering bypasses car gridlock, saving ~${motoTimeSavedVsCar} mins vs driving a car in ${traffic.label.toLowerCase()}!`;
      } else {
        motoInsight = 'Agile motorcycle travel. Fast lane filtering through traffic signals. Helmet required (RA 10054).';
      }

      travelEstimates.value.motorcycle = {
        available: true,
        distanceKm: roadDistKm,
        durationMins: currentMotoMins,
        baseMins: baseMotoMins,
        trafficDelayMins: motoTrafficDelayMins,
        trafficDelayText: motoTimeSavedVsCar > 0 ? `Saves ${motoTimeSavedVsCar}m vs car` : 'Fast transit',
        rangeText: `Traffic Range: ${formatDuration(baseMotoMins)} – ${formatDuration(Math.round(baseMotoMins * 1.45))}`,
        etaFormatted: formatDuration(currentMotoMins),
        distanceFormatted: `${roadDistKm} km`,
        badge: 'Fastest in Traffic',
        speed: motoSpeed,
        insight: motoInsight
      };

      // 3. Bicycle (Eco-friendly cycling @ ~16 km/h + Calorie & CO2 tracking)
      const bikeMins = Math.max(5, Math.round((roadDistKm / 16) * 60));
      const calories = Math.round(roadDistKm * 32);
      const co2 = (roadDistKm * 0.15).toFixed(1);

      let bikeInsight = '';
      if (roadDistKm <= 4.5 && currentCarMins >= bikeMins) {
        bikeInsight = `🚲 Bicycle matches or beats driving (${bikeMins}m vs ${currentCarMins}m car) because vehicles are trapped in ${traffic.label.toLowerCase()}!`;
      } else if (roadDistKm > 45) {
        bikeInsight = `Challenging ${roadDistKm} km endurance ride. Recommended only for experienced long-distance cyclists; carry hydration.`;
      } else {
        bikeInsight = `Zero carbon footprint! Completely immune to motor vehicle traffic jams. Burns ~${calories} kcal and saves ${co2} kg CO₂.`;
      }

      travelEstimates.value.bicycle = {
        available: true,
        distanceKm: roadDistKm,
        durationMins: bikeMins,
        trafficDelayText: 'Zero traffic delay',
        rangeText: 'Steady speed: unaffected by car gridlock',
        etaFormatted: formatDuration(bikeMins),
        distanceFormatted: `${roadDistKm} km`,
        calories: calories,
        co2: co2,
        badge: roadDistKm > 45 ? 'Long-Distance Cycling' : 'Zero Emissions',
        speed: '~16 km/h',
        insight: bikeInsight
      };

      // 4. Train (LRT-1, LRT-2, MRT-3 rail transit corridor)
      const inRailCorridor = (lat, lng) => lat >= 14.48 && lat <= 14.70 && lng >= 120.94 && lng <= 121.11;
      const originNearRail = inRailCorridor(origin[0], origin[1]);
      const destNearRail = inRailCorridor(destination[0], destination[1]);

      if (originNearRail && destNearRail) {
        const trainTransitMins = Math.max(12, Math.round((roadDistKm / 32) * 60 + 12));
        const trainTotalMins = trainTransitMins + (traffic.trainPlatformDelay || 0);

        let trainInsight = '';
        if (carTrafficDelayMins >= 12) {
          trainInsight = `🚆 MRT/LRT runs on dedicated tracks — avoids all ${carTrafficDelayMins}m of road traffic gridlock! (Includes +${traffic.trainPlatformDelay}m platform wait).`;
        } else {
          trainInsight = 'Metro rail transit accessible (MRT-3 / LRT-1 / LRT-2). Immune to highway traffic delays + short last-mile transit.';
        }

        travelEstimates.value.train = {
          available: true,
          distanceKm: roadDistKm,
          durationMins: trainTotalMins,
          trafficDelayText: 'Dedicated rail track',
          rangeText: `Station wait: +${traffic.trainPlatformDelay || 2}m during ${traffic.label.toLowerCase()}`,
          etaFormatted: formatDuration(trainTotalMins),
          distanceFormatted: `${roadDistKm} km`,
          badge: 'MRT / LRT Line',
          speed: '~32 km/h rail transit',
          insight: trainInsight
        };
      } else {
        travelEstimates.value.train = {
          available: false,
          distanceKm: roadDistKm,
          durationMins: 0,
          trafficDelayText: 'No rail corridor',
          rangeText: 'Provincial destination without rail service',
          etaFormatted: 'No Rail',
          distanceFormatted: `${roadDistKm} km`,
          badge: 'No Direct Line',
          speed: 'N/A',
          insight: 'No direct train connection to this provincial destination. Regional bus, private vehicle, or motorcycle recommended.'
        };
      }

      // 5. Airplane (Domestic Aviation Hubs)
      if (straightDistKm <= 95) {
        travelEstimates.value.airplane = {
          available: false,
          distanceKm: Math.round(straightDistKm),
          flightMins: 0,
          durationMins: 0,
          trafficDelayText: 'Not applicable',
          rangeText: 'Short provincial distance (<95 km)',
          etaFormatted: 'Not Practical',
          distanceFormatted: `${Math.round(straightDistKm)} km air`,
          airportHub: 'NAIA',
          badge: 'Short Distance',
          speed: 'N/A',
          insight: `Commercial flight not practical for short road distance (${Math.round(straightDistKm)} km). Driving or motorcycle is far faster without airport security buffer.`
        };
      } else {
        const flightPureMins = Math.max(45, Math.round((straightDistKm / 650) * 60));
        const airportAccessDelay = traffic.carMultiplier >= 1.8 ? 20 : 0;
        const totalFlightTripMins = flightPureMins + 90 + airportAccessDelay;

        travelEstimates.value.airplane = {
          available: true,
          distanceKm: Math.round(straightDistKm),
          flightMins: flightPureMins,
          durationMins: totalFlightTripMins,
          trafficDelayText: airportAccessDelay > 0 ? `+${airportAccessDelay}m airport road delay` : 'Air flight',
          rangeText: `Flight: ${formatDuration(flightPureMins)} + 90m airport boarding buffer`,
          etaFormatted: formatDuration(totalFlightTripMins),
          distanceFormatted: `${Math.round(straightDistKm)} km air`,
          airportHub: 'NAIA / Clark',
          badge: 'Domestic Flight',
          speed: '~650 km/h flight',
          insight: `Domestic commercial flight corridor available (${formatDuration(flightPureMins)} flight + 90m check-in/boarding buffer). Connects via NAIA, Clark, or regional hubs.`
        };
      }

      directionsDuration.value = travelEstimates.value.car.durationMins;
    };

    const setTrafficCondition = (conditionKey) => {
      trafficCondition.value = conditionKey;
      if (lastRawRouteData) {
        computeMultiModalEstimates(
          lastRawRouteData.roadDistKm,
          lastRawRouteData.rawDurationMins,
          lastRawRouteData.straightDistKm,
          lastRawRouteData.origin,
          lastRawRouteData.destination
        );
      }
      const cfg = getTrafficConfig(conditionKey);
      showToast('Traffic Mode Updated', `Estimated for: ${cfg.label} (${cfg.badgeText})`, 'bi-stopwatch text-warning');
    };

    const drawModeRouteOnMap = (mode) => {
      if (!map) return;
      if (directionsPolyline) {
        map.removeLayer(directionsPolyline);
        directionsPolyline = null;
      }
      if (flightMarker) {
        map.removeLayer(flightMarker);
        flightMarker = null;
      }

      const origin = getCurrentOriginCoords();
      const dest = directionsTarget.value ? [parseFloat(directionsTarget.value.lat), parseFloat(directionsTarget.value.lng)] : null;
      if (!dest) return;

      if (mode === 'airplane' && travelEstimates.value.airplane.available) {
        const flightArcCoords = createFlightArc(origin, dest);
        directionsPolyline = L.polyline(flightArcCoords, {
          color: '#06b6d4',
          weight: 4,
          dashArray: '8, 8',
          opacity: 0.95
        }).addTo(map);

        const midPoint = flightArcCoords[Math.floor(flightArcCoords.length / 2)];
        flightMarker = L.marker(midPoint, {
          icon: L.divIcon({
            className: 'leaflet-plane-marker',
            html: '<i class="bi bi-airplane-fill"></i>',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
          }),
          zIndexOffset: 1100
        }).addTo(map);

        map.fitBounds(directionsPolyline.getBounds(), { padding: [60, 60] });
        return;
      }

      if (mode === 'train' && travelEstimates.value.train.available) {
        directionsPolyline = L.polyline(cachedRoadGeometry, {
          color: '#8b5cf6',
          weight: 5,
          dashArray: '6, 6',
          opacity: 0.95
        }).addTo(map);
        map.fitBounds(directionsPolyline.getBounds(), { padding: [60, 60] });
        return;
      }

      // Road modes: Car, Motorcycle, Bicycle
      let lineColor = '#2563eb'; // Car
      let lineWeight = 6;
      if (mode === 'motorcycle') {
        lineColor = '#f59e0b';
        lineWeight = 6;
      } else if (mode === 'bicycle') {
        lineColor = '#10b981';
        lineWeight = 5;
      }

      directionsPolyline = L.polyline(cachedRoadGeometry, {
        color: lineColor,
        weight: lineWeight,
        opacity: 0.95
      }).addTo(map);

      map.fitBounds(directionsPolyline.getBounds(), { padding: [60, 60] });
    };

    const switchTravelMode = (mode) => {
      selectedTravelMode.value = mode;
      drawModeRouteOnMap(mode);
      if (mode === 'airplane' && !travelEstimates.value.airplane.available) {
        showToast('Flight Notice', 'Commercial air flight not applicable for short distance (<95 km). Road navigation active.', 'bi-info-circle text-info');
      } else if (mode === 'train' && !travelEstimates.value.train.available) {
        showToast('Train Notice', 'No direct rail corridor to this destination. Road transit active.', 'bi-info-circle text-info');
      }
    };

    const switchOriginPreset = (presetKey) => {
      originPreset.value = presetKey;
      if (presetKey === 'gps' && !userLocation.value) {
        locateUserPosition();
      } else if (directionsTarget.value) {
        executeRouteCalculation(directionsTarget.value, false);
      }
    };

    const executeRouteCalculation = async (stay, autoZoom = true) => {
      directionsActive.value = true;
      directionsTarget.value = stay;
      selectedStay.value = stay;
      isCalculatingRoute.value = true;

      const lat = parseFloat(stay.lat);
      const lng = parseFloat(stay.lng);
      if (isNaN(lat) || isNaN(lng)) {
        isCalculatingRoute.value = false;
        return;
      }
      const destination = [lat, lng];
      const origin = getCurrentOriginCoords();
      const originLabel = getCurrentOriginLabel();

      // Setup/update origin marker on map
      if (directionsOriginMarker && map) map.removeLayer(directionsOriginMarker);
      const originIcon = L.divIcon({
        className: 'leaflet-custom-marker',
        html: `<div class="origin-pin-badge"><i class="bi bi-geo-alt-fill text-white"></i> ${originPreset.value === 'gps' && userLocation.value ? 'Start (You)' : 'Start'}</div>`,
        iconSize: [95, 28],
        iconAnchor: [47, 14]
      });
      directionsOriginMarker = L.marker(origin, { icon: originIcon, zIndexOffset: 950 }).addTo(map);
      directionsOriginMarker.bindPopup(`
        <div style="font-family:'Inter',sans-serif; padding:4px;">
          <div style="color:#34d399; font-weight:800; font-size:12px;"><i class="bi bi-pin-map-fill me-1"></i>Starting Point</div>
          <div style="font-size:10.5px; color:#cbd5e1;">${originLabel}</div>
        </div>
      `);

      const straightDistKm = Math.hypot((destination[0]-origin[0])*111, (destination[1]-origin[1])*105);

      // Query OSRM driving engine
      const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${origin[1]},${origin[0]};${destination[1]},${destination[0]}?overview=full&geometries=geojson&steps=true`;

      try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 6000);
        const res = await fetch(osrmUrl, { signal: controller.signal });
        clearTimeout(timeoutId);

        if (!res.ok) throw new Error(`OSRM HTTP ${res.status}`);
        const data = await res.json();

        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
          const route = data.routes[0];
          const roadDistKm = parseFloat((route.distance / 1000).toFixed(1));
          const roadMins = Math.max(5, Math.round(route.duration / 60));

          directionsDistance.value = roadDistKm;
          directionsDuration.value = roadMins;

          // Convert GeoJSON [lng, lat] to Leaflet [lat, lng]
          cachedRoadGeometry = route.geometry.coordinates.map(c => [c[1], c[0]]);

          // Parse turn-by-turn guidance steps
          if (route.legs && route.legs[0] && route.legs[0].steps) {
            turnByTurnSteps.value = route.legs[0].steps.map(st => {
              let icon = 'bi-arrow-up';
              const type = st.maneuver ? st.maneuver.type : '';
              const modifier = st.maneuver ? st.maneuver.modifier : '';

              if (type === 'depart') icon = 'bi-geo-alt-fill text-success';
              else if (type === 'arrive') icon = 'bi-flag-fill text-danger';
              else if (modifier && modifier.includes('left')) icon = 'bi-arrow-90deg-left text-warning';
              else if (modifier && modifier.includes('right')) icon = 'bi-arrow-90deg-right text-warning';
              else if (modifier && modifier.includes('straight')) icon = 'bi-arrow-up text-info';
              else if (modifier && modifier.includes('uturn')) icon = 'bi-arrow-counterclockwise text-danger';

              let instruction = '';
              if (type === 'depart') {
                instruction = st.name ? `Head out onto ${st.name}` : 'Depart starting point';
              } else if (type === 'arrive') {
                instruction = `Arrive at destination: ${stay.title}`;
              } else {
                const modText = modifier ? modifier.replace('-', ' ') : 'continue';
                instruction = st.name ? `${modText.charAt(0).toUpperCase() + modText.slice(1)} onto ${st.name}` : `${modText} on route`;
              }

              const stepDistKm = (st.distance / 1000).toFixed(1);
              const stepDistText = st.distance < 1000 ? `${Math.round(st.distance)} m` : `${stepDistKm} km`;
              const stepMins = Math.max(1, Math.round(st.duration / 60));

              return {
                icon,
                instruction,
                distText: stepDistText,
                durationText: `${stepMins} min`
              };
            });
          }

          computeMultiModalEstimates(roadDistKm, roadMins, straightDistKm, origin, destination);
        } else {
          throw new Error('OSRM route not found');
        }
      } catch (err) {
        // Fallback: Geodesic road factor estimation (x1.35)
        const fallbackDist = parseFloat((straightDistKm * 1.35).toFixed(1));
        const fallbackMins = Math.max(8, Math.round(fallbackDist * 2.8 + 5));

        directionsDistance.value = fallbackDist;
        directionsDuration.value = fallbackMins;
        cachedRoadGeometry = [origin, destination];

        turnByTurnSteps.value = [
          { icon: 'bi-geo-alt-fill text-success', instruction: `Depart from ${originLabel}`, distText: '0 m', durationText: '0 min' },
          { icon: 'bi-signpost-2 text-info', instruction: 'Follow shortest road highway route towards destination', distText: `${fallbackDist} km`, durationText: `${fallbackMins} mins` },
          { icon: 'bi-flag-fill text-danger', instruction: `Arrive at destination: ${stay.title}`, distText: `${fallbackDist} km`, durationText: 'Goal' }
        ];

        computeMultiModalEstimates(fallbackDist, fallbackMins, straightDistKm, origin, destination);
      } finally {
        isCalculatingRoute.value = false;
        drawModeRouteOnMap(selectedTravelMode.value);
        if (autoZoom && directionsPolyline && map) {
          map.fitBounds(directionsPolyline.getBounds(), { padding: [60, 60] });
        }
        highlightDestinationMarker(stay);
      }
    };

    const startDirections = (stay) => {
      if (!userLocation.value && navigator.geolocation && originPreset.value === 'gps') {
        showToast('Locating', 'Detecting your GPS location as starting point...', 'bi-geo-alt text-info');
        navigator.geolocation.getCurrentPosition(
          (pos) => {
            userLocation.value = [pos.coords.latitude, pos.coords.longitude];
            executeRouteCalculation(stay, true);
          },
          (err) => {
            showToast('GPS Notice', 'Unable to detect location. Using NAIA Terminal 3 Airport as reference.', 'bi-info-circle text-info');
            executeRouteCalculation(stay, true);
          },
          { timeout: 6000, enableHighAccuracy: true }
        );
      } else {
        executeRouteCalculation(stay, true);
      }
    };

    const routeFromModal = (hotel) => {
      if (!hotel) return;
      if (roomDetailsModalInstance) {
        roomDetailsModalInstance.hide();
      }
      destroyPannellum();
      setTimeout(() => {
        startDirections(hotel);
      }, 350);
    };

    const toggleLiveGpsTracking = () => {
      if (isLiveGpsTracking.value) {
        if (watchGpsId !== null) {
          navigator.geolocation.clearWatch(watchGpsId);
          watchGpsId = null;
        }
        isLiveGpsTracking.value = false;
        showToast('Auto-Tracking Paused', 'Live GPS position follow has been paused.', 'bi-pause-circle text-secondary');
      } else {
        if (!navigator.geolocation) {
          showToast('GPS Unsupported', 'Geolocation is not supported by your browser.', 'bi-exclamation-triangle text-warning');
          return;
        }
        originPreset.value = 'gps';
        isLiveGpsTracking.value = true;
        showToast('Auto-Tracking Active', 'Live GPS auto-tracking is actively following your movement.', 'bi-crosshair text-success');

        watchGpsId = navigator.geolocation.watchPosition(
          (pos) => {
            const newLat = pos.coords.latitude;
            const newLng = pos.coords.longitude;
            const prevLoc = userLocation.value;
            userLocation.value = [newLat, newLng];

            let movedMeters = 999;
            if (prevLoc) {
              movedMeters = Math.hypot((newLat - prevLoc[0]) * 111000, (newLng - prevLoc[1]) * 105000);
            }
            if (movedMeters > 30 && directionsActive.value && directionsTarget.value) {
              executeRouteCalculation(directionsTarget.value, false);
            }
          },
          (err) => {
            console.warn('[GPS Watch Error]', err.message);
          },
          { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 }
        );
      }
    };

    const zoomToOrigin = () => {
      const origin = getCurrentOriginCoords();
      if (map) {
        map.flyTo(origin, 16, { duration: 1.0 });
        if (directionsOriginMarker) directionsOriginMarker.openPopup();
      }
    };

    const fitRouteBounds = () => {
      if (directionsPolyline && map) {
        map.fitBounds(directionsPolyline.getBounds(), { padding: [60, 60] });
      }
    };

    const simulateNavStep = () => {
      if (directionsTarget.value) startDirections(directionsTarget.value);
    };

    const clearDirections = () => {
      if (watchGpsId !== null) {
        navigator.geolocation.clearWatch(watchGpsId);
        watchGpsId = null;
      }
      isLiveGpsTracking.value = false;

      if (directionsPolyline && map) map.removeLayer(directionsPolyline);
      if (directionsOriginMarker && map) map.removeLayer(directionsOriginMarker);
      if (flightMarker && map) map.removeLayer(flightMarker);

      directionsPolyline = null;
      directionsOriginMarker = null;
      flightMarker = null;
      directionsActive.value = false;
      directionsTarget.value = null;
      isDirectionsMinimized.value = false;
      turnByTurnSteps.value = [];
      showTurnSteps.value = false;
    };

    const getGoogleMapsUrl = () => {
      const origin = getCurrentOriginCoords();
      const dest = directionsTarget.value ? [directionsTarget.value.lat, directionsTarget.value.lng] : origin;
      return `https://www.google.com/maps/dir/?api=1&origin=${origin[0]},${origin[1]}&destination=${dest[0]},${dest[1]}&travelmode=driving`;
    };

    const getWazeUrl = () => {
      const dest = directionsTarget.value ? [directionsTarget.value.lat, directionsTarget.value.lng] : [14.5176, 121.0158];
      return `https://waze.com/ul?ll=${dest[0]},${dest[1]}&navigate=yes`;
    };

    const streetViewActive = Vue.ref(false);
    
    const getSamplePanoUrl = (roomId) => {
      // Create a deterministic numeric seed based on the room ID string
      let seed = 0;
      const strId = String(roomId);
      for (let i = 0; i < strId.length; i++) {
        seed += strId.charCodeAt(i);
      }
      
      // Array of reliable Unsplash CDN interior panoramas to completely bypass CORS
      const panos = [
        'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=2000&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1566665797739-1674de7a421a?q=80&w=2000&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1582719478250-c89404bb8a0e?q=80&w=2000&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=2000&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=2000&auto=format&fit=crop'
      ];
      
      const selectedPano = panos[seed % panos.length];
      return `https://cdn.pannellum.org/2.5/pannellum.htm#panorama=${encodeURIComponent(selectedPano)}&autoLoad=true`;
    };

    // Load Data
    const loadData = async () => {
      try {
        const [resStays, resBookings, resSaved] = await Promise.all([
          axios.get('api.php?action=get_stays'),
          axios.get('api.php?action=get_bookings'),
          axios.get('api.php?action=get_saved_destinations')
        ]);
        stays.value = resStays.data;
        userBookings.value = resBookings.data;
        savedLocations.value = Array.isArray(resSaved.data) ? resSaved.data.map(String) : [];
      } catch (e) {
        console.error('Error loading data:', e);
      }
    };

    // ── TAB A: CSS 360 PANORAMA PREVIEW HANDLERS ──────────────────────
    const panoPanX = ref(0);
    const panoZoom = ref(1.2);
    const currentPanoKey = ref('');
    const panoLoading = ref(false);
    const panoAutoRotating = ref(false);
    let isDraggingPano = false;
    let panoStartX = 0;
    let panoAnimFrame = null;
    let panoAutoDir = 1;

    const currentPanoRoom = computed(() => {
      if (!roomDetailsHotel.value || !roomDetailsHotel.value.showroom) return {};
      const rooms = roomDetailsHotel.value.showroom.rooms || {};
      return rooms[currentPanoKey.value] || Object.values(rooms)[0] || {};
    });

    const switchPanoRoom = (key) => {
      if (!roomDetailsHotel.value?.showroom?.rooms?.[key] || currentPanoKey.value === key) return;
      panoLoading.value = true;
      panoPanX.value = 0;
      setTimeout(() => {
        currentPanoKey.value = key;
        panoLoading.value = false;
      }, 300);
    };

    const startPanoDrag = (e) => {
      try { e.target.setPointerCapture?.(e.pointerId); } catch(err){}
      isDraggingPano = true;
      panoStartX = e.clientX || 0;
      if (panoAutoRotating.value) {
        panoAutoRotating.value = false;
        if (panoAnimFrame) cancelAnimationFrame(panoAnimFrame);
      }
    };

    const onPanoDrag = (e) => {
      if (!isDraggingPano) return;
      const x = e.clientX || 0;
      const diff = x - panoStartX;
      panoStartX = x;
      panoPanX.value += diff * 1.5;
      if (panoPanX.value > 450) panoPanX.value = 450;
      if (panoPanX.value < -450) panoPanX.value = -450;
    };

    const endPanoDrag = (e) => {
      isDraggingPano = false;
      try { e?.target?.releasePointerCapture?.(e.pointerId); } catch(err){}
    };

    const onPanoWheel = (e) => {
      const delta = e.deltaY < 0 ? 0.15 : -0.15;
      panoZoom.value = Math.max(1.0, Math.min(2.5, +(panoZoom.value + delta).toFixed(2)));
    };

    const zoomPanoPreview = (delta) => {
      panoZoom.value = Math.max(1.0, Math.min(2.5, +(panoZoom.value + delta).toFixed(2)));
    };

    const resetPanoView = () => {
      panoPanX.value = 0;
      panoZoom.value = 1.2;
    };

    let _wasAutoPanningBeforeHover = false;
    const togglePanoAutoPan = () => {
      panoAutoRotating.value = !panoAutoRotating.value;
      if (panoAutoRotating.value) {
        const loop = () => {
          if (!panoAutoRotating.value) return;
          panoPanX.value += 0.8 * panoAutoDir;
          if (panoPanX.value > 300) { panoAutoDir = -1; }
          else if (panoPanX.value < -300) { panoAutoDir = 1; }
          panoAnimFrame = requestAnimationFrame(loop);
        };
        panoAnimFrame = requestAnimationFrame(loop);
      } else {
        if (panoAnimFrame) cancelAnimationFrame(panoAnimFrame);
      }
    };

    const pausePanoAutoPan = () => {
      if (panoAutoRotating.value) {
        _wasAutoPanningBeforeHover = true;
        panoAutoRotating.value = false;
        if (panoAnimFrame) cancelAnimationFrame(panoAnimFrame);
      }
    };

    const resumePanoAutoPan = () => {
      if (_wasAutoPanningBeforeHover) {
        _wasAutoPanningBeforeHover = false;
        togglePanoAutoPan();
      }
    };

    const togglePreviewFullscreen = () => {
      const wrapper = document.getElementById('showroomViewerWrapper');
      if (!wrapper) return;
      if (!document.fullscreenElement) {
        wrapper.requestFullscreen?.().catch(()=>{});
      } else {
        document.exitFullscreen?.().catch(()=>{});
      }
    };

    const openRoomDetailsModal = (hotel) => {
      roomDetailsHotel.value = hotel;
      streetViewActive.value = false;
      
      // Init 360 viewer if showroom data exists
      if (hotel.showroom && hotel.showroom.rooms) {
        const initKey = hotel.showroom.initial_room || Object.keys(hotel.showroom.rooms)[0];
        currentPanoKey.value = initKey;
        panoPanX.value = 0;
        panoZoom.value = 1.2;
      }
      
      incrementViews(hotel);
      roomDetailsModalInstance.show();
    };

    // ── MODAL 1.5: DEDICATED ROOM 360° TOUR ───────────────────────────
    let room360ModalInstance = null;
    const active360Room = ref(null);
    let modalPannellumViewer = null;
    const modalScenes = ref({});
    const currentModalScene = ref('room');
    const modal360Loading = ref(false);
    const modalAutoRotate = ref(true);

    const currentModalSceneTitle = computed(() => {
      const scenes = modalScenes.value || {};
      return scenes[currentModalScene.value]?.title || 'Virtual Room';
    });

    /**
     * Build dynamic multi-scene walk-through for a specific room - Rock-Solid Portal Hotspots
     */
    const buildModalScenesForRoom = (room, hotel) => {
      const scenes = {};
      const mainPano = room.custom_360_url || room.image_360 || 'img/pano_bedroom.jpg';
      const bathPano = 'img/pano_bathroom.jpg';
      const rmTitle = room.room_name || 'Master Bedroom Suite';

      // Scene 1: Main Room / Suite
      scenes['room'] = {
        title: rmTitle,
        icon: 'bi bi-door-open-fill',
        type: 'equirectangular',
        panorama: mainPano,
        hotSpots: [
          {
            id: 'to_bath',
            pitch: -3,
            yaw: -120,
            type: 'scene',
            sceneId: 'bathroom',
            cssClass: 'pano-portal-hs',
            createTooltipFunc: (div) => {
              div.innerHTML = '<span class="portal-badge"><i class="bi bi-door-open-fill"></i></span><span class="portal-name">Ensuite Bathroom</span><i class="bi bi-chevron-right portal-arrow"></i>';
            },
            clickHandlerFunc: () => switchModalScene('bathroom')
          }
        ]
      };

      // Scene 2: Ensuite Bathroom
      scenes['bathroom'] = {
        title: 'Ensuite Bathroom',
        icon: 'bi bi-droplet-fill',
        type: 'equirectangular',
        panorama: bathPano,
        hotSpots: [
          {
            id: 'to_room',
            pitch: -2,
            yaw: 115,
            type: 'scene',
            sceneId: 'room',
            cssClass: 'pano-portal-hs',
            createTooltipFunc: (div) => {
              div.innerHTML = '<span class="portal-badge"><i class="bi bi-door-open-fill"></i></span><span class="portal-name">Back to Room</span><i class="bi bi-chevron-right portal-arrow"></i>';
            },
            clickHandlerFunc: () => switchModalScene('room')
          }
        ]
      };

      // Scene 3: Skyline Terrace / Balcony / Lounge (for suites and condostays)
      const rLower = rmTitle.toLowerCase();
      const isSuiteOrCondo = rLower.includes('suite') || rLower.includes('executive') || rLower.includes('condo') || rLower.includes('deluxe') || (hotel && (hotel.category === 'airbnb' || hotel.category === 'hotel'));
      if (isSuiteOrCondo) {
        const balconyPano = (hotel && hotel.category === 'airbnb') ? 'img/skyline_condo.jpg' : 'img/luxury_suite.jpg';
        scenes['balcony'] = {
          title: 'Skyline Terrace / Lounge',
          icon: 'bi bi-sunset-fill',
          type: 'equirectangular',
          panorama: balconyPano,
          hotSpots: [
            {
              id: 'balcony_to_room',
              pitch: -3,
              yaw: 175,
              type: 'scene',
              sceneId: 'room',
              cssClass: 'pano-portal-hs',
              createTooltipFunc: (div) => {
                div.innerHTML = '<span class="portal-badge"><i class="bi bi-door-open-fill"></i></span><span class="portal-name">Return to Suite</span><i class="bi bi-chevron-right portal-arrow"></i>';
              },
              clickHandlerFunc: () => switchModalScene('room')
            }
          ]
        };
        scenes['room'].hotSpots.push({
          id: 'room_to_balcony',
          pitch: 1,
          yaw: 65,
          type: 'scene',
          sceneId: 'balcony',
          cssClass: 'pano-portal-hs',
          createTooltipFunc: (div) => {
            div.innerHTML = '<span class="portal-badge"><i class="bi bi-sunset-fill"></i></span><span class="portal-name">Skyline Terrace</span><i class="bi bi-chevron-right portal-arrow"></i>';
          },
          clickHandlerFunc: () => switchModalScene('balcony')
        });
      }

      return scenes;
    };

    /**
     * Launch 360 viewer modal for a room
     */
    const openRoom360Modal = (room) => {
      active360Room.value = room;
      modalScenes.value = buildModalScenesForRoom(room, roomDetailsHotel.value);
      currentModalScene.value = 'room';
      modal360Loading.value = true;
      // Default to non-rotating so hotspots stay completely steady and easy to click
      modalAutoRotate.value = false;
      if (room360ModalInstance) {
        room360ModalInstance.show();
      }
    };

    /**
     * Initialize Pannellum inside modal (called when modal finishes showing)
     */
    const initModalPannellum = () => {
      if (typeof pannellum === 'undefined') {
        console.warn('[Pannellum] Library not ready');
        modal360Loading.value = false;
        return;
      }
      if (modalPannellumViewer) {
        try { modalPannellumViewer.destroy(); } catch(e){}
        modalPannellumViewer = null;
      }

      const scenesObj = modalScenes.value || {};
      const containerEl = document.getElementById('panoContainer');
      if (!containerEl) return;

      const panoScenes = {};
      Object.entries(scenesObj).forEach(([sKey, sCfg]) => {
        panoScenes[sKey] = {
          title: sCfg.title,
          type: 'equirectangular',
          panorama: sCfg.panorama,
          hotSpots: sCfg.hotSpots || []
        };
      });

      try {
        modalPannellumViewer = pannellum.viewer('panoContainer', {
          default: {
            firstScene: currentModalScene.value || 'room',
            sceneFadeDuration: 0,
            autoLoad: true,
            autoRotate: 0,
            showControls: false,
            mouseZoom: true,
            hfov: 100,
            compass: false
          },
          scenes: panoScenes
        });

        modalPannellumViewer.on('load', () => {
          modal360Loading.value = false;
        });

        modalPannellumViewer.on('scenechange', (newScene) => {
          currentModalScene.value = newScene;
          showToast(
            '360° Scene Switched',
            `Now viewing: ${scenesObj[newScene]?.title || newScene}`,
            'bi-vr text-info'
          );
        });

        modalPannellumViewer.on('error', (err) => {
          console.warn('[Pannellum Modal] Error:', err);
          modal360Loading.value = false;
        });

        // Trigger resize after brief frame settling
        setTimeout(() => {
          modalPannellumViewer?.resize();
        }, 150);
      } catch (err) {
        console.error('[Pannellum Modal] Failed to create viewer:', err);
        modal360Loading.value = false;
      }
    };

    const destroyModalPannellum = () => {
      if (modalPannellumViewer) {
        try { modalPannellumViewer.destroy(); } catch(e){}
        modalPannellumViewer = null;
      }
      modal360Loading.value = false;
      currentModalScene.value = 'room';
    };

    const switchModalScene = (sceneId) => {
      if (!modalScenes.value?.[sceneId]) return;
      currentModalScene.value = sceneId;
      modal360Loading.value = true;
      if (modalPannellumViewer) {
        try {
          if (typeof modalPannellumViewer.loadScene === 'function') {
            modalPannellumViewer.loadScene(sceneId);
            setTimeout(() => {
              try {
                if (modalPannellumViewer && modalPannellumViewer.getScene() !== sceneId) {
                  initModalPannellum();
                } else {
                  modal360Loading.value = false;
                }
              } catch(e) {
                initModalPannellum();
              }
            }, 120);
          } else {
            initModalPannellum();
          }
        } catch(e) {
          initModalPannellum();
        }
      } else {
        initModalPannellum();
      }
    };

    const toggleModalAutoRotate = () => {
      modalAutoRotate.value = !modalAutoRotate.value;
      if (modalPannellumViewer) {
        if (modalAutoRotate.value) {
          modalPannellumViewer.startAutoRotate(-2);
        } else {
          modalPannellumViewer.stopAutoRotate();
        }
      }
    };

    const zoomModalPano = (delta) => {
      if (!modalPannellumViewer) return;
      const fov = modalPannellumViewer.getHfov() || 100;
      modalPannellumViewer.setHfov(Math.max(45, Math.min(125, fov + delta)));
    };

    const resetModalPanoView = () => {
      if (!modalPannellumViewer) return;
      modalPannellumViewer.setPitch(0);
      modalPannellumViewer.setYaw(0);
      modalPannellumViewer.setHfov(100);
    };

    const toggleModalFullscreen = () => {
      const modalContent = document.querySelector('#room360Modal .modal-content');
      if (!document.fullscreenElement) {
        modalContent?.requestFullscreen?.().catch(()=>{
          modalPannellumViewer?.toggleFullscreen();
        });
      } else {
        document.exitFullscreen?.().catch(()=>{});
      }
    };

    const bookFrom360Modal = () => {
      const stay = roomDetailsHotel.value;
      const room = active360Room.value;
      if (room360ModalInstance) room360ModalInstance.hide();
      if (roomDetailsModalInstance) roomDetailsModalInstance.hide();
      if (stay && room) {
        openBookingModalFor(stay, room.id);
      }
    };

    const bookSpecificRoom = (room) => {
      if (!roomDetailsHotel.value) return;
      roomDetailsModalInstance.hide();
      openBookingModalFor(roomDetailsHotel.value, room.id);
    };

    // Booking Handlers
    const openBookingModalFor = (stay, specificRoomId) => {
      bookingTarget.value = stay;
      bookingStep.value = 'form';
      bookingErrors.value = {};

      const isHotel = ['hotel', 'casino', 'resort', 'heritage'].includes(stay.category);
      if (isHotel || stay.category === 'airbnb') {
        bookingForm.value.bookingMode = 'nightly';
        bookingForm.value.nights = 1;
        bookingForm.value.stayDurationType = '1 Night Stay';
      } else {
        // Motels & short rentals: support hourly & nightly, default to hourly
        bookingForm.value.bookingMode = 'hourly';
        bookingForm.value.hourlySlot = '3hr';
        bookingForm.value.nights = 1;
        bookingForm.value.stayDurationType = '3-Hour Wash Rate';
      }

      const availableRoom = (stay.rooms || []).find(r => r.status === 'Available' && (!r.booking_limit || r.total_booked < r.booking_limit)) || (stay.rooms || [])[0];
      const defaultRoomId = specificRoomId || (availableRoom ? availableRoom.id : '');
      bookingForm.value.selectedRoomId = defaultRoomId;
      bookingModalInstance.show();
    };

    const onRoomSelectChange = () => {
      // triggers computed price update
    };

    // Validation before review
    const proceedToReview = () => {
      bookingErrors.value = {};
      const name = bookingForm.value.guestName?.trim() || '';
      const phone = bookingForm.value.guestPhone?.trim() || '';
      const checkIn = bookingForm.value.checkInTime;

      // Check room availability & limit
      const selRoom = (bookingTarget.value?.rooms || []).find(r => r.id === bookingForm.value.selectedRoomId);
      if (selRoom) {
        const lim = selRoom.booking_limit || 10;
        if (selRoom.total_booked >= lim || selRoom.status === 'Sold Out') {
          Swal.fire({
            icon: 'error',
            title: 'Room Fully Booked',
            text: `Sorry, '${selRoom.room_name}' has reached its maximum booking quota (${selRoom.total_booked}/${lim} reserved). Please select an available room suite.`,
            background: '#1e293b',
            color: '#fff',
            confirmButtonColor: '#3b82f6'
          });
          return;
        }
      }

      if (name.length < 2) {
        bookingErrors.value.guestName = 'Name must be at least 2 letters';
      }
      if (phone.length < 7) {
        bookingErrors.value.guestPhone = 'Valid phone number required';
      }
      if (!checkIn) {
        bookingErrors.value.checkInTime = 'Please select check-in date/time';
      } else {
        const checkInDate = new Date(checkIn);
        if (checkInDate.getTime() < Date.now() - 3600000) {
          bookingErrors.value.checkInTime = 'Check-in cannot be in the past';
        }
      }

      if (Object.keys(bookingErrors.value).length > 0) {
        return;
      }
      bookingStep.value = 'review';
    };

    // Proceed from review step to online payment gateway
    const proceedToPaymentGateway = () => {
      if (!paymentForm.value.accountName && bookingForm.value.guestName) {
        paymentForm.value.accountName = bookingForm.value.guestName;
      }
      if (!paymentForm.value.cardHolder && bookingForm.value.guestName) {
        paymentForm.value.cardHolder = bookingForm.value.guestName;
      }
      if (bookingForm.value.guestPhone && (!paymentForm.value.accountNumber || paymentForm.value.accountNumber === '0917-829-4102')) {
        paymentForm.value.accountNumber = bookingForm.value.guestPhone;
      }
      bookingStep.value = 'payment';
    };

    // Authorize & Process Online Down Payment (GCash, Maya, GrabPay, Card)
    const processOnlineDownPayment = async () => {
      isProcessingPayment.value = true;
      const prefixMap = { gcash: 'GC', maya: 'MY', grabpay: 'GP', card: 'CC' };
      const prefix = prefixMap[selectedPaymentMethod.value] || 'PAY';
      const payRef = `${prefix}-${Math.floor(100000 + Math.random() * 900000)}`;

      const isHourly = isMotelOrRentalTarget.value && bookingForm.value.bookingMode === 'hourly';
      const totalAmt = Number(calculateTotalAmount.value) || 0;
      const downAmt = Number(computedDownpaymentAmount.value) || Math.round(totalAmt * 0.2);
      const balAmt = Math.max(0, totalAmt - downAmt);

      const payload = {
        hotel_id: bookingTarget.value.id,
        stay_id: bookingTarget.value.id,
        room_id: bookingForm.value.selectedRoomId,
        hotel_title: bookingTarget.value.title,
        room_name: selectedRoomName.value,
        room_number: selectedRoomNumber.value,
        guest_name: bookingForm.value.guestName,
        guest_phone: bookingForm.value.guestPhone,
        stay_type: isHourly ? bookingForm.value.stayDurationType : `${bookingForm.value.nights} Night${bookingForm.value.nights > 1 ? 's' : ''} Stay`,
        check_in_time: bookingForm.value.checkInTime,
        nights: isHourly ? 1 : bookingForm.value.nights,
        amount: totalAmt,
        downpayment_amount: downAmt,
        balance_due: balAmt,
        payment_method: selectedPaymentMethod.value,
        payment_status: 'Paid',
        payment_ref: payRef
      };

      try {
        const res = await axios.post('api.php?action=create_booking', payload);
        if (res.data && res.data.status === 'error') {
          Swal.fire({
            icon: 'error',
            title: 'Unable to Reserve',
            text: res.data.message || 'Booking limit has been reached for this room type.',
            background: '#1e293b',
            color: '#fff',
            confirmButtonColor: '#3b82f6'
          });
          return;
        }

        lastConfirmedBooking.value = {
          ...payload,
          id: res.data.booking_id || res.data.id || Math.floor(1000 + Math.random() * 9000)
        };

        bookingStep.value = 'success';
        await loadData();

        showToast(
          'Down Payment Confirmed!',
          `₱${downAmt.toLocaleString()} received via ${selectedPaymentMethod.value.toUpperCase()}. Ref: ${payRef}`,
          'bi-check-circle-fill text-success',
          { label: 'View Bookings', handler: () => openBookingsModal() }
        );
      } catch (err) {
        console.error('Error creating booking with down payment:', err);
        Swal.fire({
          icon: 'error',
          title: 'Payment Error',
          text: 'Payment authorization could not be completed. Please try again.',
          background: '#1e293b',
          color: '#fff'
        });
      } finally {
        isProcessingPayment.value = false;
      }
    };

    const finalizeBooking = () => {
      proceedToPaymentGateway();
    };

    const saveDestination = async (hotelId) => {
      const idStr = String(hotelId);
      const isCurrentlySaved = savedLocations.value.includes(idStr);
      
      if (isCurrentlySaved) {
        const result = await Swal.fire({
          title: 'Remove from Favorites?',
          text: "Are you sure you want to remove this destination from your library?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, remove it!'
        });
        
        if (result.isConfirmed) {
          try {
            const res = await axios.post('api.php?action=remove_destination', { hotel_id: hotelId });
            if (res.data.status === 'success') {
              savedLocations.value = savedLocations.value.filter(id => id !== idStr);
              showToast('Removed', 'Destination removed from your library.', 'bi-info-circle-fill text-info');
              renderMapMarkers();
            }
          } catch (e) {
            showToast('Error', 'Failed to remove destination.', 'bi-exclamation-triangle-fill text-danger');
          }
        }
      } else {
        try {
          const res = await axios.post('api.php?action=save_destination', { hotel_id: hotelId });
          if (res.data.status === 'success') {
            savedLocations.value.push(idStr);
            showToast('Destination Saved', 'Saved to your Destination Library.', 'bi-heart-fill text-danger');
            renderMapMarkers();
          } else {
            showToast('Notice', res.data.message || 'Could not save destination.', 'bi-info-circle-fill text-warning');
          }
        } catch (err) {
          showToast('Error', 'An error occurred while saving.', 'bi-exclamation-triangle-fill text-danger');
        }
      }
    };

    // Cancellation with Action Modal
    const promptCancelBooking = (booking) => {
      cancelTarget.value = booking;
      userBookingsModalInstance.hide();
      confirmCancelModalInstance.show();
    };

    const confirmCancelBookingAction = async () => {
      if (!cancelTarget.value) return;
      const idToCancel = cancelTarget.value.id;
      const hotelName = cancelTarget.value.stayTitle;

      try {
        await axios.post('api.php?action=cancel_booking', { id: idToCancel });
        confirmCancelModalInstance.hide();
        await loadData();

        showToast(
          'Reservation Cancelled',
          `Reservation #${idToCancel} for ${hotelName} has been cancelled.`,
          'bi-info-circle-fill text-warning'
        );
      } catch (err) {
        console.error('Error cancelling booking:', err);
      }
    };

    // Account Verification Action Handlers
    const openVerifyModal = () => {
      verifyError.value = '';
      verifyCodeInput.value = '';
      verifyAccountModalInstance.show();
    };

    const submitAccountVerification = async () => {
      if (!verifyCodeInput.value || verifyCodeInput.value.length < 6) {
        verifyError.value = 'Please enter a valid 6-digit PIN code.';
        return;
      }
      try {
        const res = await axios.post('api.php?action=verify_account', { code: verifyCodeInput.value });
        if (res.data.status === 'success') {
          accountVerified.value = true;
          verifyAccountModalInstance.hide();
          showToast('Account Verified!', 'Your account has been officially verified with verified traveler status.', 'bi-patch-check-fill text-success');
        } else {
          verifyError.value = res.data.message || 'Invalid code.';
        }
      } catch (err) {
        verifyError.value = 'Verification failed. Try instant pass.';
      }
    };

    const instantVerifyAccount = async () => {
      try {
        const res = await axios.post('api.php?action=verify_account', { instant: 1 });
        if (res.data.status === 'success') {
          accountVerified.value = true;
          verifyAccountModalInstance.hide();
          showToast('Account Verified!', 'Instant 1-Click Verification applied successfully.', 'bi-lightning-charge-fill text-success');
        }
      } catch (err) {
        accountVerified.value = true;
        verifyAccountModalInstance.hide();
      }
    };

    const resendVerificationCode = async () => {
      try {
        const res = await axios.post('api.php?action=send_verification_code');
        showToast('Code Resent', `A new 6-digit code has been dispatched: [${res.data.code}]`, 'bi-shield-check text-info');
      } catch (err) {
        showToast('Code Generated', 'Demo code ready: [849201]', 'bi-shield-check text-info');
      }
    };

    const closeSidebar = () => {
      const sidebarEl = document.getElementById('exploreSidebar');
      if (sidebarEl) {
        const bsOffcanvas = bootstrap.Offcanvas.getInstance(sidebarEl);
        if (bsOffcanvas) bsOffcanvas.hide();
      }
    };

    const openBookingsModal = () => userBookingsModalInstance.show();

    const formatDateTime = (dtStr) => {
      if (!dtStr) return '';
      const d = new Date(dtStr);
      return d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
    };

    onMounted(async () => {
      const bModalEl = document.getElementById('bookingModal');
      const ubModalEl = document.getElementById('userBookingsModal');
      const rdModalEl = document.getElementById('roomDetailsModal');
      const r360ModalEl = document.getElementById('room360Modal');
      const ccModalEl = document.getElementById('confirmCancelModal');
      const vaModalEl = document.getElementById('verifyAccountModal');

      if (bModalEl) bookingModalInstance = new bootstrap.Modal(bModalEl);
      if (ubModalEl) userBookingsModalInstance = new bootstrap.Modal(ubModalEl);
      if (rdModalEl) roomDetailsModalInstance = new bootstrap.Modal(rdModalEl);
      if (r360ModalEl) room360ModalInstance = new bootstrap.Modal(r360ModalEl);
      if (ccModalEl) confirmCancelModalInstance = new bootstrap.Modal(ccModalEl);
      if (vaModalEl) verifyAccountModalInstance = new bootstrap.Modal(vaModalEl);

      // ── 360° JAVASCRIPT EVENT LISTENERS ──────────────────────────
      // 1. Room 360 Modal Lifecycle Event Listeners
      if (r360ModalEl) {
        r360ModalEl.addEventListener('shown.bs.modal', () => {
          initModalPannellum();
        });
        r360ModalEl.addEventListener('hidden.bs.modal', () => {
          destroyModalPannellum();
        });
      }

      // 2. Hotel Details Modal Lifecycle Event Listeners
      if (rdModalEl) {
        rdModalEl.addEventListener('shown.bs.modal', () => {
          if (pannellumActive.value && pannellumViewer) {
            pannellumViewer.resize();
          }
        });
        rdModalEl.addEventListener('hidden.bs.modal', () => {
          destroyPannellum();
          if (panoAutoRotating.value) {
            panoAutoRotating.value = false;
            if (panoAnimFrame) cancelAnimationFrame(panoAnimFrame);
          }
        });
      }

      // 3. Wheel Zoom & Hover Pause Event Listener on Pano Canvas
      const panoContainerEl = document.getElementById('panoContainer');
      if (panoContainerEl) {
        panoContainerEl.addEventListener('wheel', (e) => {
          if (modalPannellumViewer) {
            e.preventDefault();
            const delta = e.deltaY < 0 ? -8 : 8;
            zoomModalPano(delta);
          }
        }, { passive: false });

        // Pause rotation on hover so hotspots stay perfectly still and never evade mouse
        panoContainerEl.addEventListener('mouseenter', () => {
          if (modalPannellumViewer && modalAutoRotate.value) {
            try { modalPannellumViewer.stopAutoRotate(); } catch(e){}
          }
        });
        panoContainerEl.addEventListener('mouseleave', () => {
          if (modalPannellumViewer && modalAutoRotate.value) {
            try { modalPannellumViewer.startAutoRotate(-2); } catch(e){}
          }
        });
      }

      // 4. Window Resize & Orientation Change Listeners
      const handlePanoResize = () => {
        if (modalPannellumViewer) {
          modalPannellumViewer.resize();
        }
        if (pannellumViewer) {
          pannellumViewer.resize();
        }
      };
      window.addEventListener('resize', handlePanoResize);
      window.addEventListener('orientationchange', handlePanoResize);

      // 5. Global Keyboard Navigation Listeners for 360 Viewers
      const handleGlobal360KeyDown = (e) => {
        // Do not intercept if user is typing into an input/textarea/select
        const activeTag = document.activeElement ? document.activeElement.tagName : '';
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(activeTag)) return;

        const isModal360Open = r360ModalEl && r360ModalEl.classList.contains('show');
        const isDetailsOpen = rdModalEl && rdModalEl.classList.contains('show');

        // Modal 360 View Controls
        if (isModal360Open && modalPannellumViewer) {
          switch(e.code) {
            case 'ArrowLeft':
            case 'KeyA':
              e.preventDefault();
              modalPannellumViewer.setYaw(modalPannellumViewer.getYaw() - 10);
              break;
            case 'ArrowRight':
            case 'KeyD':
              e.preventDefault();
              modalPannellumViewer.setYaw(modalPannellumViewer.getYaw() + 10);
              break;
            case 'ArrowUp':
            case 'KeyW':
              e.preventDefault();
              modalPannellumViewer.setPitch(modalPannellumViewer.getPitch() + 8);
              break;
            case 'ArrowDown':
            case 'KeyS':
              e.preventDefault();
              modalPannellumViewer.setPitch(modalPannellumViewer.getPitch() - 8);
              break;
            case 'Equal':
            case 'NumpadAdd':
              e.preventDefault();
              zoomModalPano(-10);
              break;
            case 'Minus':
            case 'NumpadSubtract':
              e.preventDefault();
              zoomModalPano(10);
              break;
            case 'Space':
              e.preventDefault();
              toggleModalAutoRotate();
              break;
            case 'KeyF':
              e.preventDefault();
              toggleModalFullscreen();
              break;
            case 'KeyH':
              e.preventDefault();
              resetModalPanoView();
              break;
          }
        } else if (isDetailsOpen) {
          // Tab B: Full Pannellum in Details Modal
          if (pannellumActive.value && pannellumViewer) {
            switch(e.code) {
              case 'ArrowLeft':
              case 'KeyA':
                e.preventDefault();
                pannellumViewer.setYaw(pannellumViewer.getYaw() - 10);
                break;
              case 'ArrowRight':
              case 'KeyD':
                e.preventDefault();
                pannellumViewer.setYaw(pannellumViewer.getYaw() + 10);
                break;
              case 'ArrowUp':
              case 'KeyW':
                e.preventDefault();
                pannellumViewer.setPitch(pannellumViewer.getPitch() + 8);
                break;
              case 'ArrowDown':
              case 'KeyS':
                e.preventDefault();
                pannellumViewer.setPitch(pannellumViewer.getPitch() - 8);
                break;
              case 'Equal':
              case 'NumpadAdd':
                e.preventDefault();
                zoomPannellum(-10);
                break;
              case 'Minus':
              case 'NumpadSubtract':
                e.preventDefault();
                zoomPannellum(10);
                break;
              case 'Space':
                e.preventDefault();
                togglePannellumAutoRotate();
                break;
              case 'KeyF':
                e.preventDefault();
                togglePannellumFullscreen();
                break;
              case 'KeyH':
                e.preventDefault();
                resetPannellumView();
                break;
            }
          } else if (!pannellumActive.value) {
            // Tab A: Preview Pan Controls
            switch(e.code) {
              case 'ArrowLeft':
              case 'KeyA':
                e.preventDefault();
                panoPanX.value = Math.max(-450, panoPanX.value - 30);
                break;
              case 'ArrowRight':
              case 'KeyD':
                e.preventDefault();
                panoPanX.value = Math.min(450, panoPanX.value + 30);
                break;
              case 'Equal':
              case 'NumpadAdd':
                e.preventDefault();
                zoomPanoPreview(0.2);
                break;
              case 'Minus':
              case 'NumpadSubtract':
                e.preventDefault();
                zoomPanoPreview(-0.2);
                break;
              case 'Space':
                e.preventDefault();
                togglePanoAutoPan();
                break;
              case 'KeyH':
                e.preventDefault();
                resetPanoView();
                break;
            }
          }
        }
      };
      window.addEventListener('keydown', handleGlobal360KeyDown);

      // Expose to window for popup clicks
      window.vueApp = {
        openRoomDetailsFromId: (id) => {
          const h = stays.value.find(s => s.id === id);
          if (h) openRoomDetailsModal(h);
        },
        quickRouteToId: (id) => {
          const h = stays.value.find(s => s.id === id);
          if (h) startDirections(h);
        },
        quickBookId: (id) => {
          const h = stays.value.find(s => s.id === id);
          if (h) openBookingModalFor(h, null);
        },
        locateDestinationFromId: (id) => {
          const h = stays.value.find(s => s.id === id);
          if (h) locateDestination(h, true);
        },
        saveDestination: (id) => {
          saveDestination(id);
        },
        get savedLocations() {
          return savedLocations.value || [];
        }
      };

      nextTick(() => {
        initMap();
      });

      await loadData();

      // URL Parameters Handling
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has('verified') && urlParams.get('verified') === '1') {
        accountVerified.value = true;
        showToast('Account Verified!', 'Welcome to StayFinder PH. You can now make reservations.', 'bi-patch-check-fill text-success');
      }

      if (urlParams.has('category')) {
        selectedCategory.value = urlParams.get('category');
      }

      setTimeout(() => {
        if (urlParams.has('locate')) {
          const locateId = urlParams.get('locate');
          const target = stays.value.find(s => s.id === locateId);
          if (target) {
            locateDestination(target, true);
          }
        } else if (urlParams.has('tour')) {
          const tourId = urlParams.get('tour');
          const target = stays.value.find(s => s.id === tourId);
          if (target) {
            openRoomDetailsModal(target);
            setTimeout(() => {
              initPannellum();
            }, 300);
          }
        } else if (!urlParams.has('category') && !urlParams.has('view')) {
          fitAllPins();
        }
        
        if (urlParams.has('view')) {
          if (urlParams.get('view') === 'bookings') {
            openBookingsModal();
          }
        }
      }, 500);
    });

    const filteredStayIds = computed(() => (filteredStays.value || []).map(s => s.id).join(','));

    watch(filteredStayIds, () => {
      renderMapMarkers();
      if (selectedStay.value) {
        highlightDestinationMarker(selectedStay.value);
      } else {
        setTimeout(() => { fitAllPins(); }, 120);
      }
    });

    watch(selectedBranchId, (newId) => {
      if (newId && newId !== 'all') {
        const target = stays.value.find(s => s.id === newId);
        if (target) locateDestination(target, true);
      } else {
        fitAllPins();
      }
    });

    return {
      stays, userBookings, selectedStay, searchQuery, selectedCategory,
      sortBy, filteredStays, countByCategory, resetFilters,
      selectedDistrict, selectedBranchId, onlyAvailableRooms, districtsList, countByDistrict,
      getRoomStatusBadgeClass, getRoomStatusIcon,
      accountVerified, verifyCodeInput, verifyError, openVerifyModal,
      submitAccountVerification, instantVerifyAccount, resendVerificationCode,
      currentTileLayer, switchMapLayer, directionsActive, directionsTarget,
      directionsDistance, directionsDuration, startDirections, clearDirections, simulateNavStep,
      selectHotelCard, fitAllPins, locateDestination, zoomToDestination, highlightDestinationMarker,
      locateFromModal, routeFromModal, onBranchChange, locateUserPosition, zoomToOrigin, fitRouteBounds,
      isLocatingUser, userLocation, closeSidebar, saveDestination, savedLocations,
      streetViewActive, getSamplePanoUrl,
      // Multi-Modal GPS Navigation & Travel State
      selectedTravelMode, switchTravelMode, travelEstimates, activeModeData,
      isLiveGpsTracking, toggleLiveGpsTracking, isCalculatingRoute,
      originPreset, switchOriginPreset, getCurrentOriginLabel,
      trafficCondition, currentTrafficInfo, setTrafficCondition,
      turnByTurnSteps, showTurnSteps, getGoogleMapsUrl, getWazeUrl,
      isDirectionsMinimized, getModeIcon,
      roomDetailsHotel, openRoomDetailsModal, bookSpecificRoom,
      // 360 Preview (Tab A)
      panoPanX, panoZoom, currentPanoKey, panoLoading, currentPanoRoom, panoAutoRotating,
      switchPanoRoom, startPanoDrag, onPanoDrag, endPanoDrag, onPanoWheel,
      togglePanoAutoPan, pausePanoAutoPan, resumePanoAutoPan, zoomPanoPreview, resetPanoView, togglePreviewFullscreen,
      // Full Pannellum (Tab B)
      PANNELLUM_SCENES, activePannellumScenes, pannellumActive, pannellumCurrentScene, pannellumAutoRotating,
      initPannellum, switchPannellumScene, destroyPannellum,
      togglePannellumAutoRotate, zoomPannellum, resetPannellumView, togglePannellumFullscreen,
      // Room 360 Modal (Modal 1.5)
      active360Room, openRoom360Modal, modalScenes, currentModalScene, currentModalSceneTitle,
      modal360Loading, modalAutoRotate, switchModalScene, toggleModalAutoRotate,
      zoomModalPano, resetModalPanoView, toggleModalFullscreen, bookFrom360Modal,
      // Booking & Reviews
      bookingTarget, bookingStep, bookingForm, bookingErrors, minDateTime,
      selectedRoomPrice, selectedRoomName, selectedRoomNumber, calculateTotalAmount, onRoomSelectChange,
      openBookingModalFor, proceedToReview, finalizeBooking, isSubmittingBooking,
      cancelTarget, promptCancelBooking, confirmCancelBookingAction,
      openBookingsModal, formatDateTime, activeToast,
      reviewsRoom, roomReviewsList, loadingReviews, submittingReview, reviewForm, getStarClass, openRoomReviewsModal, submitReview,
      // Commuter & Hourly Rate features
      commuterOnly, commuterCount, isHourlyTarget, computedHourlyRate, onHourlySlotChange,
      // Anti-Spam Downpayment & Online Payment Gateway
      selectedPaymentMethod, isProcessingPayment, lastConfirmedBooking, payMethodMode, paymentForm,
      computedDownpaymentAmount, computedBalanceDue, getPaymentBadgeBg, proceedToPaymentGateway, processOnlineDownPayment,
      // Nearest & Convenient Location Recommendations
      calculateDistanceKm, getStayDistanceKm, getStayDistanceText, nearestConvenientStays, toggleNearMeSort
    };
  }
}).mount('#app');
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (!themeToggleBtn) return;
    const icon = themeToggleBtn.querySelector('i');
    const label = document.getElementById('themeToggleLabel');
    
    // Load preference (default to dark theme or saved preference)
    const savedTheme = localStorage.getItem('stayfinder_theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateIcon(savedTheme);

    themeToggleBtn.addEventListener('click', () => {
      let currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
      let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('stayfinder_theme', newTheme);
      updateIcon(newTheme);
    });

    function updateIcon(theme) {
      if (theme === 'dark') {
        if (icon) icon.className = 'bi bi-sun-fill text-warning';
        if (label) label.textContent = 'Dark';
      } else {
        if (icon) icon.className = 'bi bi-moon-stars text-primary';
        if (label) label.textContent = 'Light';
      }
    }

    // Cross-page / cross-tab theme synchronization listener
    window.addEventListener('storage', (e) => {
      if (e.key === 'stayfinder_theme' && e.newValue) {
        document.documentElement.setAttribute('data-theme', e.newValue);
        updateIcon(e.newValue);
      }
    });
  });
</script>
</body>
</html>