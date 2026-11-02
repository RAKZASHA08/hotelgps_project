<?php
// Session already started by AuthMiddleware (via controller)
// DB connection injected by controller via app/Config/Database
// Check administrative privileges
$isAdmin = (($_SESSION['role'] ?? '') === 'admin') || (($_SESSION['user_email'] ?? '') === 'admin@comfortvue.ph');
$isLoggedIn = !empty($_SESSION['user_id']);
$currentUserEmail = $_SESSION['user_email'] ?? '';
$currentUserName = $_SESSION['user_name'] ?? 'Guest';
$currentUserRole = $_SESSION['role'] ?? 'guest';

// 1. If not logged in at all, redirect to separate admin login portal
if (!$isLoggedIn) {
    header("Location: admin_login.php");
    exit;
}

// 2. If logged in as a normal/end user without admin privileges, display 403 Forbidden
if (!$isAdmin) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>403 Forbidden - Access Restricted</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
      <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
      <link rel="stylesheet" href="css/styles.css">
    </head>
    <body class="landing-body d-flex flex-column align-items-center justify-content-center py-5" style="min-height: 100vh; background: radial-gradient(circle at 50% 10%, #3b0712 0%, #070b14 70%);">
      <div class="auth-wrapper px-3 text-center" style="max-width: 520px; width: 100%;">
        
        <div class="d-inline-flex p-3 rounded-circle mb-3 border border-danger border-opacity-40 shadow-lg" style="background: rgba(239, 68, 68, 0.15);">
          <i class="bi bi-shield-slash-fill text-danger fs-1"></i>
        </div>

        <h2 class="fw-extrabold text-white mb-1">403 Access Forbidden</h2>
        <p class="text-secondary small mb-3">Segregated Administration Console</p>

        <div class="auth-card text-start shadow-lg border border-danger border-opacity-30 p-4">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-40 px-2 py-1">
              <i class="bi bi-lock-fill me-1"></i> RESTRICTED ACCESS
            </span>
            <span class="small text-secondary">Normal Users Not Permitted</span>
          </div>

          <p class="text-light mb-3" style="font-size: 0.92rem; line-height: 1.6;">
            This administrative area is isolated from the main web portal and strictly reserved for authorized hotel managers to control branch inventory and room statuses.
          </p>

          <div class="p-3 rounded-3 mb-4 border border-secondary border-opacity-30" style="background: rgba(255, 255, 255, 0.04);">
            <div class="text-secondary small">Currently signed in as:</div>
            <div class="fw-bold text-white small font-monospace mt-1">
              <?= htmlspecialchars($currentUserName) ?> (<?= htmlspecialchars($currentUserEmail) ?>)
            </div>
            <div class="mt-1">
              <span class="badge bg-secondary text-light" style="font-size: 0.68rem;">Role: <?= htmlspecialchars($currentUserRole) ?></span>
            </div>
          </div>

          <div class="d-flex flex-column gap-2">
            <a href="home.php" class="btn btn-primary py-2 fw-semibold rounded-3 shadow">
              <i class="bi bi-arrow-left me-1"></i> Return to Traveler Web Portal
            </a>
            <a href="logout.php?redirect=admin" class="btn btn-outline-warning py-2 fw-semibold rounded-3">
              <i class="bi bi-shield-lock-fill me-1"></i> Authenticate as Administrator
            </a>
          </div>
        </div>

        <div class="mt-4">
          <span class="text-secondary small">ComfortVue HotelMaps Security System</span>
        </div>

      </div>
    </body>
    </html>
    <?php
    exit;
}

// 3. User is verified Administrator
$adminName = $_SESSION['user_name'] ?? 'System Administrator';
$adminEmail = $_SESSION['user_email'] ?? 'admin@comfortvue.ph';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal - Hotel Branches & Room Status Management</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap">
  <link rel="stylesheet" href="css/styles.css">

  <!-- JS: Vue 3, Bootstrap 5, Axios -->
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="landing-body" style="background: radial-gradient(circle at 50% 0%, #17223b 0%, #070b14 70%);">
<div id="adminApp" v-cloak>

  <!-- ============================================== -->
  <!-- AUTHENTICATED ADMIN CONSOLE                    -->
  <!-- ============================================== -->
  
  <!-- Authenticated Admin Console Content -->
  <div class="admin-content">
  <!-- Admin Header -->
  <header class="app-header">
    <div class="container-fluid px-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <a href="admin.php" class="d-flex align-items-center gap-3 text-decoration-none">
          <div class="brand-logo-badge" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="bi bi-shield-lock-fill"></i></div>
          <div>
            <div class="d-flex align-items-center gap-2">
              <span class="fs-5 fw-bold tracking-tight text-white">ComfortVue Admin</span>
              <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.65rem;">
                <i class="bi bi-gear-fill me-1"></i>PORTAL MODE
              </span>
            </div>
            <div class="text-secondary" style="font-size: 0.72rem;">Hotel Branches & Room Status Master Console</div>
          </div>
        </a>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex align-items-center flex-wrap gap-2">
        <a href="home.php" class="btn-nav-pill text-decoration-none">
          <i class="bi bi-map-fill text-info"></i> View Live Client Map
        </a>
        <button class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold shadow-sm" @click="openAddRoomModal">
          <i class="bi bi-plus-circle me-1"></i> Add Room Suite
        </button>
        <div class="dropdown">
          <button class="btn-nav-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle text-warning"></i> <?= htmlspecialchars($adminName) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border border-secondary border-opacity-50">
            <li>
              <div class="px-3 py-2">
                <span class="fw-bold text-white d-block"><?= htmlspecialchars($adminName) ?></span>
                <span class="small text-secondary"><?= htmlspecialchars($adminEmail) ?></span>
                <span class="badge bg-warning text-dark d-inline-block mt-1" style="font-size:0.65rem;">Administrator</span>
              </div>
            </li>
            <li><hr class="dropdown-divider border-secondary border-opacity-25"></li>
            <li><a class="dropdown-item text-info" href="home.php"><i class="bi bi-compass me-2"></i>Client View</a></li>
            <li><a class="dropdown-item text-danger" href="logout.php?redirect=admin"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
          </ul>
        </div>
      </div>
    </div>
  </header>

  <!-- Control Panel Summary Strip -->
  <section class="exec-stats-bar">
    <div class="container-fluid px-3 px-md-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="text-secondary small fw-bold text-uppercase letter-spacing-1 d-none d-md-inline" style="font-size:0.7rem;">
            <i class="bi bi-sliders text-warning me-1"></i> SYSTEM STATUS:
          </span>

          <div class="exec-stat-pill">
            <i class="bi bi-building text-info"></i>
            <div>
              <span class="text-secondary" style="font-size:0.65rem; display:block; line-height:1;">HOTEL BRANCHES</span>
              <span class="fw-bold text-white fs-6">{{ branches.length }} Branches</span>
            </div>
          </div>

          <div class="exec-stat-pill">
            <i class="bi bi-door-open-fill text-primary"></i>
            <div>
              <span class="text-secondary" style="font-size:0.65rem; display:block; line-height:1;">TOTAL ROOM SUITES</span>
              <span class="fw-bold text-white fs-6">{{ allRoomsCount }} Suites</span>
            </div>
          </div>

          <div class="exec-stat-pill">
            <i class="bi bi-check-circle-fill text-success"></i>
            <div>
              <span class="text-secondary" style="font-size:0.65rem; display:block; line-height:1;">AVAILABLE</span>
              <span class="fw-bold text-success fs-6">{{ statusCounts.Available || 0 }}</span>
            </div>
          </div>

          <div class="exec-stat-pill">
            <i class="bi bi-person-fill-lock text-info"></i>
            <div>
              <span class="text-secondary" style="font-size:0.65rem; display:block; line-height:1;">OCCUPIED</span>
              <span class="fw-bold text-info fs-6">{{ statusCounts.Occupied || 0 }}</span>
            </div>
          </div>

          <div class="exec-stat-pill">
            <i class="bi bi-clock-fill text-warning"></i>
            <div>
              <span class="text-secondary" style="font-size:0.65rem; display:block; line-height:1;">RESERVED</span>
              <span class="fw-bold text-warning fs-6">{{ statusCounts.Reserved || 0 }}</span>
            </div>
          </div>

          <div class="exec-stat-pill">
            <i class="bi bi-tools text-danger"></i>
            <div>
              <span class="text-secondary" style="font-size:0.65rem; display:block; line-height:1;">MAINTENANCE</span>
              <span class="fw-bold text-danger fs-6">{{ statusCounts['Under Maintenance'] || 0 }}</span>
            </div>
          </div>
        </div>

        <button class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-3" @click="fetchBranches">
          <i class="bi bi-arrow-repeat me-1" :class="{ 'spin-icon': isLoading }"></i> Refresh Inventory
        </button>
      </div>
    </div>
  </section>

  <!-- Main Admin Workspace Container -->
  <main class="container-fluid px-3 px-md-4 py-4">

    <!-- Branch Filter & Search Header -->
    <div class="card bg-dark border border-secondary border-opacity-50 p-3 mb-4 rounded-4 shadow">
      <div class="row g-3 align-items-center">
        <!-- Branch Selector -->
        <div class="col-12 col-md-5">
          <label class="form-label small text-secondary fw-semibold">
            <i class="bi bi-funnel-fill text-warning me-1"></i> Filter by Hotel Branch
          </label>
          <select v-model="selectedBranchId" class="form-select modal-input">
            <option value="all">★ All Hotel Branches ({{ branches.length }})</option>
            <option v-for="b in branches" :key="b.id" :value="b.id">
              {{ b.title }} - {{ b.branch_name }} ({{ b.city }})
            </option>
          </select>
        </div>

        <!-- Room Status Filter -->
        <div class="col-12 col-md-4">
          <label class="form-label small text-secondary fw-semibold">
            <i class="bi bi-toggle-on text-success me-1"></i> Filter by Current Room Status
          </label>
          <select v-model="statusFilter" class="form-select modal-input">
            <option value="all">All Room Statuses</option>
            <option value="Available">🟢 Available Only</option>
            <option value="Occupied">🔵 Occupied</option>
            <option value="Reserved">🟡 Reserved</option>
            <option value="Under Maintenance">🔴 Under Maintenance</option>
          </select>
        </div>

        <!-- Search Input -->
        <div class="col-12 col-md-3">
          <label class="form-label small text-secondary fw-semibold">
            <i class="bi bi-search text-info me-1"></i> Search Suite Title
          </label>
          <input type="text" v-model="roomSearchQuery" class="form-control modal-input" placeholder="Search room name...">
        </div>
      </div>
    </div>

    <!-- Active Branch Details Card (If specific branch selected) -->
    <div v-if="activeBranch" class="card bg-dark border border-primary border-opacity-40 p-3 mb-4 rounded-4 shadow">
      <div class="row align-items-center g-3">
        <div class="col-md-2 text-center text-md-start">
          <img :src="activeBranch.image" :alt="activeBranch.title" class="rounded-3 shadow" style="width: 100%; max-width: 130px; height: 85px; object-fit: cover;">
        </div>
        <div class="col-md-7">
          <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <h5 class="text-white fw-bold mb-0">{{ activeBranch.title }}</h5>
            <span class="badge bg-primary">{{ activeBranch.branch_name }}</span>
            <span class="badge border border-secondary text-secondary">{{ activeBranch.city }}</span>
          </div>
          <p class="text-secondary small mb-1">
            <i class="bi bi-geo-alt text-danger me-1"></i>{{ activeBranch.address }}
          </p>
          <div class="d-flex align-items-center gap-3 text-secondary small flex-wrap" style="font-size:0.75rem;">
            <span><i class="bi bi-pin-map-fill text-info me-1"></i>Map Lat: {{ activeBranch.lat }} | Lng: {{ activeBranch.lng }}</span>
            <span>•</span>
            <span><i class="bi bi-cash text-success me-1"></i>Starting: ₱{{ Number(activeBranch.price_from).toLocaleString() }}/night</span>
            <span>•</span>
            <span><i class="bi bi-door-closed text-warning me-1"></i>Suites: {{ activeBranch.rooms?.length || 0 }}</span>
          </div>
        </div>
        <div class="col-md-3 text-md-end">
          <button class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 me-2" @click="openEditBranchModal(activeBranch)">
            <i class="bi bi-pencil-square me-1"></i> Edit Branch Coordinates
          </button>
          <button class="btn btn-sm btn-success rounded-pill px-3 py-1" @click="openAddRoomModalWithBranch(activeBranch.id)">
            <i class="bi bi-plus-lg me-1"></i> Add Room
          </button>
        </div>
      </div>
    </div>

    <!-- Rooms Inventory Table with CURRENT STATUS Controls -->
    <div class="card bg-dark border border-secondary border-opacity-50 rounded-4 overflow-hidden shadow mb-4">
      <div class="card-header bg-dark border-bottom border-secondary border-opacity-50 d-flex justify-content-between align-items-center py-3 px-4">
        <div>
          <h5 class="text-white fw-bold mb-0">
            <i class="bi bi-door-open-fill text-warning me-2"></i>Rooms & Suites Inventory
          </h5>
          <span class="text-secondary small">Manage room details, specs, and change real-time status</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-secondary px-3 py-2 fs-6">{{ displayedRooms.length }} Suites Showing</span>
          <button class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold shadow-sm" @click="openAddRoomModal">
            <i class="bi bi-plus-lg me-1"></i> Add Room Suite
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle border-secondary mb-0">
          <thead class="table-dark text-secondary small">
            <tr>
              <th style="width: 75px;">Image</th>
              <th style="width: 110px;">Room #</th>
              <th>Room Suite Name</th>
              <th>Branch Location</th>
              <th>Category & Specs</th>
              <th>Nightly Price</th>
              <th style="min-width: 170px;">Current Status (Action)</th>
              <th style="width: 140px; text-align: center;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="room in displayedRooms" :key="room.id">
              <!-- Thumbnail -->
              <td>
                <img :src="room.image" :alt="room.room_name" class="rounded-3" style="width: 65px; height: 50px; object-fit: cover;">
              </td>

              <!-- Room Number -->
              <td>
                <span v-if="room.room_number" class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 font-monospace px-2 py-1">
                  <i class="bi bi-door-closed-fill me-1"></i>{{ room.room_number }}
                </span>
                <span v-else class="text-secondary small fst-italic">Unassigned</span>
              </td>

              <!-- Title -->
              <td>
                <div class="fw-bold text-white">{{ room.room_name }}</div>
                <div class="d-flex align-items-center gap-2 mt-1">
                  <span class="text-secondary small" style="font-size:0.75rem;">
                    ID: {{ room.id }} • <strong class="text-info">{{ room.total_booked }}</strong> / {{ room.booking_limit || 10 }} Booked
                  </span>
                  <span v-if="room.total_booked >= (room.booking_limit || 10)" class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50" style="font-size:0.68rem;">
                    Limit Reached (0 left)
                  </span>
                  <span v-else class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50" style="font-size:0.68rem;">
                    {{ (room.booking_limit || 10) - room.total_booked }} slots left
                  </span>
                </div>
              </td>

              <!-- Branch Location -->
              <td>
                <span class="badge bg-dark border border-secondary text-light">{{ getBranchName(room.hotel_id) }}</span>
                <div class="text-secondary small" style="font-size:0.72rem;">{{ getBranchCity(room.hotel_id) }}</div>
              </td>

              <!-- Specs -->
              <td>
                <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 me-1">{{ room.room_type }}</span>
                <span class="text-secondary small d-block mt-1" style="font-size:0.75rem;">
                  {{ room.size_sqm }} sqm • {{ room.bed_type }} • {{ room.capacity }}
                </span>
              </td>

              <!-- Price -->
              <td>
                <span class="fw-bold text-success fs-6">₱{{ Number(room.price).toLocaleString() }}</span>
                <span class="text-secondary small d-block" style="font-size:0.72rem;">per night</span>
              </td>

              <!-- CURRENT STATUS CONTROLS (DIRECT ACTION) -->
              <td>
                <div class="d-flex align-items-center gap-2">
                  <select :value="room.status" @change="updateRoomStatus(room, $event.target.value)" class="form-select form-select-sm modal-input fw-semibold" :class="getStatusSelectClass(room.status)" style="width: 155px; font-size: 0.82rem;">
                    <option value="Available">🟢 Available</option>
                    <option value="Sold Out">⛔ Sold Out</option>
                    <option value="Occupied">🔵 Occupied</option>
                    <option value="Reserved">🟡 Reserved</option>
                    <option value="Under Maintenance">🔴 Under Maintenance</option>
                  </select>
                </div>
              </td>

              <!-- Actions -->
              <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                  <button class="btn btn-outline-info btn-xs py-1 px-2" @click="openEditRoomModal(room)" title="Edit Details">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-xs py-1 px-2" @click="confirmDeleteRoom(room)" title="Remove Room">
                    <i class="bi bi-trash3-fill"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="displayedRooms.length === 0">
              <td colspan="7" class="text-center text-secondary py-5">
                <i class="bi bi-door-closed display-4 d-block mb-2"></i>
                No room suites found matching the filter criteria.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
  </div><!-- /.admin-content -->

  <!-- ============================================== -->
  <!-- MODAL 1: ADD ROOM SUITE TO BRANCH              -->
  <!-- ============================================== -->
  <div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content modal-content-dark">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle text-primary"></i> Add New Room Suite to Branch
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitAddRoom">
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small text-secondary">Target Hotel Branch</label>
                <select v-model="newRoomForm.hotel_id" class="form-select modal-input" required>
                  <option v-for="b in branches" :key="b.id" :value="b.id">
                    {{ b.title }} - {{ b.branch_name }}
                  </option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label small text-secondary">Suite Category / Type</label>
                <select v-model="newRoomForm.room_type" class="form-select modal-input">
                  <option value="Deluxe Room">Deluxe Room</option>
                  <option value="Executive Suite">Executive Suite</option>
                  <option value="Club Room">Club Room</option>
                  <option value="Presidential Suite">Presidential Suite</option>
                  <option value="Luxury Villa">Luxury Villa</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-7">
                <label class="form-label small text-secondary">Room Title / Suite Name</label>
                <input type="text" v-model="newRoomForm.room_name" class="form-control modal-input" placeholder="e.g. Deluxe Room or Executive Suite" required>
              </div>
              <div class="col-md-5">
                <label class="form-label small text-secondary">Room # / Unit ID</label>
                <input type="text" v-model="newRoomForm.room_number" class="form-control modal-input font-monospace" placeholder="e.g. Room 204 or Suite 301" required>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label small text-secondary">Price per Night (PHP)</label>
                <input type="number" v-model.number="newRoomForm.price" class="form-control modal-input" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small text-secondary">Floor Size (sqm)</label>
                <input type="number" v-model.number="newRoomForm.size_sqm" class="form-control modal-input" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small text-secondary">Bed Configuration</label>
                <input type="text" v-model="newRoomForm.bed_type" class="form-control modal-input" placeholder="e.g. 1 King Bed" required>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label small text-secondary">Guest Capacity</label>
                <input type="text" v-model="newRoomForm.capacity" class="form-control modal-input" placeholder="e.g. 2 Adults, 1 Child" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small text-secondary">Booking Limit / Quota</label>
                <input type="number" v-model.number="newRoomForm.booking_limit" min="1" max="100" class="form-control modal-input" placeholder="e.g. 10" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small text-secondary">Initial Room Status</label>
                <select v-model="newRoomForm.status" class="form-select modal-input">
                  <option value="Available">🟢 Available</option>
                  <option value="Sold Out">⛔ Sold Out</option>
                  <option value="Occupied">🔵 Occupied</option>
                  <option value="Reserved">🟡 Reserved</option>
                  <option value="Under Maintenance">🔴 Under Maintenance</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small text-secondary">Room Image URL</label>
              <input type="url" v-model="newRoomForm.image" class="form-control modal-input" placeholder="https://images.unsplash.com/..." required>
            </div>

            <div class="mb-4">
              <label class="form-label small text-secondary">Included Amenities</label>
              <textarea v-model="newRoomForm.amenities" class="form-control modal-input" rows="2" placeholder="City Skyline View, Marble Bath, High-Speed Wi-Fi, Butler Service"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary px-4 fw-semibold">
                <i class="bi bi-plus-circle me-1"></i> Add Room Suite
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- MODAL 2: EDIT ROOM SUITE DETAILS               -->
  <!-- ============================================== -->
  <div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content modal-content-dark">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
            <i class="bi bi-pencil-square text-info"></i> Edit Room Suite Details
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" v-if="editingRoom">
          <form @submit.prevent="submitEditRoom">
            <div class="row g-3 mb-3">
              <div class="col-md-7">
                <label class="form-label small text-secondary">Room Title / Suite Name</label>
                <input type="text" v-model="editingRoom.room_name" class="form-control modal-input" required>
              </div>
              <div class="col-md-5">
                <label class="form-label small text-secondary">Room # / Unit ID</label>
                <input type="text" v-model="editingRoom.room_number" class="form-control modal-input font-monospace" placeholder="e.g. Room 204" required>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small text-secondary">Category / Type</label>
                <input type="text" v-model="editingRoom.room_type" class="form-control modal-input" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small text-secondary">Current Status</label>
                <select v-model="editingRoom.status" class="form-select modal-input">
                  <option value="Available">🟢 Available</option>
                  <option value="Sold Out">⛔ Sold Out</option>
                  <option value="Occupied">🔵 Occupied</option>
                  <option value="Reserved">🟡 Reserved</option>
                  <option value="Under Maintenance">🔴 Under Maintenance</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label small text-secondary">Price per Night (PHP)</label>
                <input type="number" v-model.number="editingRoom.price" class="form-control modal-input" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small text-secondary">Floor Size (sqm)</label>
                <input type="number" v-model.number="editingRoom.size_sqm" class="form-control modal-input" required>
              </div>
              <div class="col-md-4">
                <label class="form-label small text-secondary">Bed Configuration</label>
                <input type="text" v-model="editingRoom.bed_type" class="form-control modal-input" required>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small text-secondary">Guest Capacity</label>
                <input type="text" v-model="editingRoom.capacity" class="form-control modal-input" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small text-secondary">Booking Limit / Capacity Quota</label>
                <input type="number" v-model.number="editingRoom.booking_limit" min="1" max="100" class="form-control modal-input" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small text-secondary">Room Image URL</label>
              <input type="url" v-model="editingRoom.image" class="form-control modal-input" required>
            </div>

            <div class="mb-4">
              <label class="form-label small text-secondary">Amenities</label>
              <textarea v-model="editingRoom.amenities" class="form-control modal-input" rows="2"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-info px-4 fw-semibold text-white">
                <i class="bi bi-save me-1"></i> Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- MODAL 3: EDIT BRANCH COORDINATES & DETAILS     -->
  <!-- ============================================== -->
  <div class="modal fade" id="editBranchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content modal-content-dark">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
            <i class="bi bi-geo-alt-fill text-danger"></i> Edit Hotel Branch & Coordinates
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" v-if="editingBranch">
          <form @submit.prevent="submitEditBranch">
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small text-secondary">Hotel Title</label>
                <input type="text" v-model="editingBranch.title" class="form-control modal-input" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small text-secondary">Branch Name</label>
                <input type="text" v-model="editingBranch.branch_name" class="form-control modal-input" required>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small text-secondary">Hotel Brand</label>
                <input type="text" v-model="editingBranch.brand" class="form-control modal-input" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small text-secondary">City / District Area</label>
                <input type="text" v-model="editingBranch.city" class="form-control modal-input" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small text-secondary">Full Street Address</label>
              <input type="text" v-model="editingBranch.address" class="form-control modal-input" required>
            </div>

            <!-- Coordinate Matching -->
            <div class="p-3 rounded-3 mb-3 border border-secondary border-opacity-50" style="background:#131c31;">
              <span class="small text-info fw-bold d-block mb-2">
                <i class="bi bi-pin-map-fill me-1"></i> Exact Map Coordinate Pin
              </span>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small text-secondary">Latitude</label>
                  <input type="number" step="0.000001" v-model.number="editingBranch.lat" class="form-control modal-input" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small text-secondary">Longitude</label>
                  <input type="number" step="0.000001" v-model.number="editingBranch.lng" class="form-control modal-input" required>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label small text-secondary">Starting Nightly Price (PHP)</label>
              <input type="number" v-model.number="editingBranch.price_from" class="form-control modal-input" required>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary px-4 fw-semibold">
                <i class="bi bi-check-lg me-1"></i> Update Branch Coordinates
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- MODAL 4: CONFIRM DELETE ACTION MODAL           -->
  <!-- ============================================== -->
  <div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modal-content-dark">
        <div class="modal-body p-4 text-center" v-if="roomToDelete">
          <div class="d-inline-flex p-3 rounded-circle bg-danger bg-opacity-20 text-danger mb-3 border border-danger border-opacity-25">
            <i class="bi bi-trash3-fill fs-2"></i>
          </div>
          <h5 class="text-white fw-bold mb-2">Delete Room Suite?</h5>
          <p class="text-white-50 small mb-3">
            Are you sure you want to remove <strong class="text-white">{{ roomToDelete.room_name }}</strong> from this hotel branch? This room category will no longer be available for reservations.
          </p>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary w-50 py-2 fw-semibold" data-bs-dismiss="modal">Keep Room</button>
            <button type="button" class="btn btn-danger w-50 py-2 fw-bold" @click="executeDeleteRoom">Yes, Delete Room</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- FLOATING ACTION TOAST -->
  <div class="action-toast-wrapper" v-if="adminToast">
    <div class="action-toast border-warning">
      <div class="d-flex align-items-start gap-2">
        <i class="bi fs-5 text-warning" :class="adminToast.icon || 'bi-check-circle-fill'"></i>
        <div class="flex-grow-1">
          <div class="fw-bold text-white small">{{ adminToast.title }}</div>
          <div class="text-white-50 small">{{ adminToast.message }}</div>
        </div>
        <button type="button" class="btn-close btn-close-white btn-sm" @click="adminToast = null"></button>
      </div>
    </div>
  </div>

</div>

<script>
const { createApp, ref, computed, onMounted } = Vue;

createApp({
  setup() {
    const branches = ref([]);
    const selectedBranchId = ref('all');
    const statusFilter = ref('all');
    const roomSearchQuery = ref('');
    const isLoading = ref(false);

    // Modal targets
    const editingRoom = ref(null);
    const editingBranch = ref(null);
    const roomToDelete = ref(null);
    const adminToast = ref(null);

    let addRoomModalInstance = null;
    let editRoomModalInstance = null;
    let editBranchModalInstance = null;
    let deleteRoomModalInstance = null;

    const newRoomForm = ref({
      hotel_id: '',
      room_name: '',
      room_number: '',
      room_type: 'Deluxe Room',
      price: 12000,
      size_sqm: 45,
      bed_type: '1 King Bed',
      capacity: '2 Adults',
      booking_limit: 10,
      amenities: 'City Skyline View, Marble Bath, High-Speed Wi-Fi, Butler Service',
      status: 'Available',
      image: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'
    });

    const showAdminToast = (title, message, icon) => {
      adminToast.value = { title, message, icon };
      setTimeout(() => { adminToast.value = null; }, 5000);
    };

    // Load branches and rooms
    const fetchBranches = async () => {
      isLoading.value = true;
      try {
        const res = await axios.get('api.php?action=admin_get_branches');
        if (res.data.status === 'success') {
          branches.value = res.data.branches;
          if (branches.value.length > 0 && !newRoomForm.value.hotel_id) {
            newRoomForm.value.hotel_id = branches.value[0].id;
          }
        }
      } catch (err) {
        console.error('Error fetching admin branches:', err);
      } finally {
        isLoading.value = false;
      }
    };

    const activeBranch = computed(() => {
      if (selectedBranchId.value === 'all') return null;
      return branches.value.find(b => b.id === selectedBranchId.value) || null;
    });

    // Helper: all rooms across branches
    const allRoomsList = computed(() => {
      const list = [];
      branches.value.forEach(b => {
        (b.rooms || []).forEach(rm => {
          list.push({ ...rm, hotel_title: b.title, branch_name: b.branch_name, city: b.city });
        });
      });
      return list;
    });

    const allRoomsCount = computed(() => allRoomsList.value.length);

    // Status counts
    const statusCounts = computed(() => {
      const counts = { Available: 0, Occupied: 0, Reserved: 0, 'Under Maintenance': 0 };
      allRoomsList.value.forEach(rm => {
        const st = rm.status || 'Available';
        counts[st] = (counts[st] || 0) + 1;
      });
      return counts;
    });

    // Displayed rooms based on filters
    const displayedRooms = computed(() => {
      let rooms = [];
      if (selectedBranchId.value === 'all') {
        rooms = allRoomsList.value;
      } else {
        const br = branches.value.find(b => b.id === selectedBranchId.value);
        rooms = br ? (br.rooms || []) : [];
      }

      return rooms.filter(rm => {
        if (statusFilter.value !== 'all' && rm.status !== statusFilter.value) return false;
        if (roomSearchQuery.value.trim() !== '') {
          const q = roomSearchQuery.value.toLowerCase();
          return (
            (rm.room_name || '').toLowerCase().includes(q) ||
            (rm.room_number || '').toLowerCase().includes(q) ||
            (rm.room_type || '').toLowerCase().includes(q)
          );
        }
        return true;
      });
    });

    const getBranchName = (hotelId) => {
      const b = branches.value.find(br => br.id === hotelId);
      return b ? b.branch_name : 'Branch';
    };

    const getBranchCity = (hotelId) => {
      const b = branches.value.find(br => br.id === hotelId);
      return b ? `${b.title} (${b.city})` : '';
    };

    const getStatusSelectClass = (status) => {
      if (status === 'Available') return 'border-success text-success';
      if (status === 'Sold Out') return 'border-danger text-danger bg-danger bg-opacity-10';
      if (status === 'Occupied') return 'border-info text-info';
      if (status === 'Reserved') return 'border-warning text-warning';
      if (status === 'Under Maintenance') return 'border-danger text-danger';
      return '';
    };

    // Update Room Status via AJAX
    const updateRoomStatus = async (room, newStatus) => {
      try {
        const res = await axios.post('api.php?action=admin_update_room_status', {
          room_id: room.id,
          status: newStatus
        });
        if (res.data.status === 'success') {
          room.status = newStatus;
          showAdminToast('Status Updated', `Room "${room.room_name}" is now marked as "${newStatus}".`, 'bi-check-circle-fill text-success');
        }
      } catch (err) {
        console.error('Error updating room status:', err);
        showAdminToast('Error', 'Failed to update room status.', 'bi-exclamation-triangle-fill text-danger');
      }
    };

    // Add room
    const openAddRoomModal = () => {
      if (selectedBranchId.value !== 'all') {
        newRoomForm.value.hotel_id = selectedBranchId.value;
      }
      addRoomModalInstance.show();
    };

    const openAddRoomModalWithBranch = (branchId) => {
      newRoomForm.value.hotel_id = branchId;
      addRoomModalInstance.show();
    };

    const submitAddRoom = async () => {
      try {
        const res = await axios.post('api.php?action=admin_add_room', newRoomForm.value);
        if (res.data.status === 'success') {
          addRoomModalInstance.hide();
          await fetchBranches();
          showAdminToast('Room Suite Added', res.data.message, 'bi-check-circle-fill text-success');
        }
      } catch (err) {
        console.error('Error adding room:', err);
      }
    };

    // Edit room
    const openEditRoomModal = (room) => {
      editingRoom.value = { ...room };
      editRoomModalInstance.show();
    };

    const submitEditRoom = async () => {
      try {
        const res = await axios.post('api.php?action=admin_update_room_details', editingRoom.value);
        if (res.data.status === 'success') {
          editRoomModalInstance.hide();
          await fetchBranches();
          showAdminToast('Room Details Updated', res.data.message, 'bi-check-circle-fill text-info');
        }
      } catch (err) {
        console.error('Error saving room:', err);
      }
    };

    // Delete room
    const confirmDeleteRoom = (room) => {
      roomToDelete.value = room;
      deleteRoomModalInstance.show();
    };

    const executeDeleteRoom = async () => {
      if (!roomToDelete.value) return;
      try {
        await axios.post('api.php?action=admin_delete_room', { room_id: roomToDelete.value.id });
        deleteRoomModalInstance.hide();
        await fetchBranches();
        showAdminToast('Room Removed', 'Room suite removed from branch inventory.', 'bi-trash-fill text-danger');
      } catch (err) {
        console.error('Error deleting room:', err);
      }
    };

    // Edit branch
    const openEditBranchModal = (branch) => {
      editingBranch.value = { ...branch };
      editBranchModalInstance.show();
    };

    const submitEditBranch = async () => {
      try {
        const res = await axios.post('api.php?action=admin_update_branch', editingBranch.value);
        if (res.data.status === 'success') {
          editBranchModalInstance.hide();
          await fetchBranches();
          showAdminToast('Branch Updated', 'Coordinates and branch details updated.', 'bi-geo-alt-fill text-success');
        }
      } catch (err) {
        console.error('Error updating branch:', err);
      }
    };

    onMounted(async () => {
      addRoomModalInstance = new bootstrap.Modal(document.getElementById('addRoomModal'));
      editRoomModalInstance = new bootstrap.Modal(document.getElementById('editRoomModal'));
      editBranchModalInstance = new bootstrap.Modal(document.getElementById('editBranchModal'));
      deleteRoomModalInstance = new bootstrap.Modal(document.getElementById('deleteRoomModal'));

      await fetchBranches();
    });

    return {
      branches, selectedBranchId, activeBranch, statusFilter, roomSearchQuery,
      allRoomsCount, statusCounts, displayedRooms, isLoading,
      getBranchName, getBranchCity, getStatusSelectClass,
      updateRoomStatus,
      newRoomForm, openAddRoomModal, openAddRoomModalWithBranch, submitAddRoom,
      editingRoom, openEditRoomModal, submitEditRoom,
      roomToDelete, confirmDeleteRoom, executeDeleteRoom,
      editingBranch, openEditBranchModal, submitEditBranch,
      adminToast, fetchBranches
    };
  }
}).mount('#adminApp');
</script>
</body>
</html>
