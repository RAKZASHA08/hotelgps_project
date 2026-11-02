<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}
require_once __DIR__ . '/app/bootstrap.php';

$isLoggedIn = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') !== 'guest') && (strpos((string)$_SESSION['user_id'], 'guest_') !== 0);
$userName = $_SESSION['user_name'] ?? 'Traveler';
$userEmail = $_SESSION['user_email'] ?? 'guest@comfortvue.ph';

if (!$isLoggedIn) {
    header("Location: auth.php");
    exit;
}

$bookings = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? OR guest_email = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id'], $userEmail]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {}
}

// Fallback to session mock bookings if database returned empty
if (empty($bookings)) {
    $rawBookings = $_SESSION['mock_bookings'] ?? $_SESSION['local_bookings'] ?? [];
    if (empty($rawBookings)) {
        $bookingsFile = __DIR__ . '/data/user_bookings.json';
        if (file_exists($bookingsFile)) {
            $rawBookings = json_decode(file_get_contents($bookingsFile), true) ?: [];
        }
    }
    foreach ($rawBookings as $b) {
        $bookings[] = [
            'booking_id'    => $b['id'] ?? $b['booking_id'] ?? 'BK-1001',
            'hotel_id'      => $b['stayId'] ?? $b['hotel_id'] ?? 'hotel-conrad',
            'hotel_title'   => $b['stayTitle'] ?? $b['hotel_title'] ?? 'Metro Manila Stay',
            'room_name'     => $b['roomName'] ?? $b['room_name'] ?? 'Deluxe Room',
            'room_number'   => $b['roomNumber'] ?? $b['room_number'] ?? '101',
            'guest_name'    => $b['guestName'] ?? $b['guest_name'] ?? $userName,
            'check_in_time' => $b['checkInTime'] ?? $b['check_in_time'] ?? date('Y-m-d H:i:s'),
            'stay_type'     => $b['stayType'] ?? $b['stay_type'] ?? 'Overnight Stay',
            'nights'        => $b['nights'] ?? 1,
            'amount'        => (float)($b['amount'] ?? 1500),
            'status'        => $b['status'] ?? 'CONFIRMED'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recent Bookings - ComfortVue PH</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body style="background: var(--cream);">
  
  <header class="app-header bg-white border-bottom shadow-sm sticky-top" style="border-color: var(--border)!important;">
    <div class="container px-4 d-flex align-items-center justify-content-between py-3">
      <a href="dashboard.php" class="d-flex align-items-center gap-2 text-decoration-none">
        <div class="brand-logo-badge" style="width:36px; height:36px; border-radius:10px;"><i class="bi bi-compass"></i></div>
        <span class="fs-5 fw-bold text-mocha tracking-tight">ComfortVue PH</span>
      </a>
      <div class="d-flex align-items-center gap-2">
        <a href="home.php" class="btn btn-primary rounded-pill px-3 fw-bold"><i class="bi bi-map-fill me-1"></i> Interactive Map</a>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-3 fw-bold">Dashboard</a>
        <a href="profile.php" class="btn btn-outline-secondary rounded-pill px-3 fw-bold"><i class="bi bi-person-gear me-1"></i> Profile</a>
      </div>
    </div>
  </header>

  <div class="container py-5 bookings-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h2 class="fw-extrabold text-mocha mb-1"><i class="bi bi-journal-check text-sage me-2"></i>Recent Bookings Log</h2>
        <p class="text-secondary small mb-0">Review your past and active reservations across Metro Manila</p>
      </div>
      <span class="badge bg-sage rounded-pill px-3 py-2 fs-6"><?= count($bookings) ?> Reservation<?= count($bookings) === 1 ? '' : 's' ?></span>
    </div>

    <?php if (empty($bookings)): ?>
      <div class="text-center py-5 bg-white rounded-4 shadow-sm border p-4" style="border-color: var(--border)!important;">
        <i class="bi bi-journal-x text-muted" style="font-size: 4rem;"></i>
        <h4 class="mt-3 text-mocha fw-bold">No recent bookings found.</h4>
        <p class="text-secondary">Start exploring the map to book your first express room or hotel suite!</p>
        <a href="home.php" class="btn btn-primary rounded-pill mt-2 px-4 shadow">
          <i class="bi bi-map-fill me-1"></i> Explore Interactive Map
        </a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($bookings as $b): ?>
          <?php
            $bookingCode = $b['booking_id'] ?? $b['id'] ?? 'BK-1001';
            $hotelTitle = $b['hotel_title'] ?? $b['stayTitle'] ?? 'Metro Manila Stay';
            $roomName = $b['room_name'] ?? $b['roomName'] ?? 'Deluxe Room';
            $roomNumber = $b['room_number'] ?? $b['roomNumber'] ?? '';
            $checkIn = !empty($b['check_in_time']) ? date('M d, Y h:i A', strtotime($b['check_in_time'])) : date('M d, Y h:i A');
            $stayType = $b['stay_type'] ?? $b['stayType'] ?? 'Overnight Stay';
            $nights = (int)($b['nights'] ?? 1);
            $amount = (float)($b['amount'] ?? 0);
            $status = $b['status'] ?? 'CONFIRMED';
            $hotelId = $b['hotel_id'] ?? $b['stayId'] ?? '';
          ?>
          <div class="col-12 col-md-6">
            <div class="card shadow-sm border rounded-4 h-100" style="background: var(--bg-card); border-color: var(--border)!important;">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                  <div>
                    <span class="badge bg-secondary bg-opacity-25 text-secondary font-monospace small mb-1"><?= htmlspecialchars($bookingCode) ?></span>
                    <h5 class="card-title fw-bold text-mocha mb-0"><?= htmlspecialchars($hotelTitle) ?></h5>
                  </div>
                  <span class="badge <?= $status === 'CONFIRMED' ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill px-3 py-1">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </div>
                
                <p class="mb-1 small text-secondary">
                  <i class="bi bi-door-closed text-primary me-2"></i><strong><?= htmlspecialchars($roomName) ?></strong> <?= !empty($roomNumber) ? '(Room ' . htmlspecialchars($roomNumber) . ')' : '' ?>
                </p>
                <p class="mb-1 small text-secondary">
                  <i class="bi bi-calendar-check text-success me-2"></i>Check-in: <strong><?= $checkIn ?></strong>
                </p>
                <p class="mb-3 small text-secondary">
                  <i class="bi bi-clock-history text-warning me-2"></i>Duration: <strong><?= $nights ?> Night(s) / <?= htmlspecialchars($stayType) ?></strong>
                </p>
                
                <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-color: var(--border)!important;">
                  <div>
                    <span class="small text-muted d-block" style="font-size:0.75rem;">Total Amount</span>
                    <span class="fw-extrabold text-terracotta fs-5">₱<?= number_format($amount, 2) ?></span>
                  </div>
                  <a href="home.php?locate=<?= urlencode($hotelId) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                    <i class="bi bi-pin-map-fill me-1"></i> View on Map
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
