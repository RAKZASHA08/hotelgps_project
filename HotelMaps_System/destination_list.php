<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}
require_once __DIR__ . '/app/bootstrap.php';

$isLoggedIn = !empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') !== 'guest') && (strpos((string)$_SESSION['user_id'], 'guest_') !== 0);
$userId = $_SESSION['user_id'] ?? null;

if (!$isLoggedIn) {
    header("Location: auth.php");
    exit;
}

// Auto-create saved_locations table if it doesn't exist and DB is online
if ($pdo) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `saved_locations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` VARCHAR(64) NOT NULL,
            `hotel_id` VARCHAR(64) NOT NULL,
            `saved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_hotel (user_id, hotel_id)
        )");
    } catch (\Exception $e) {}
}

$savedDestinations = [];
if ($pdo && $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT s.saved_at, h.* 
            FROM saved_locations s
            JOIN hotels h ON s.hotel_id = h.id
            WHERE s.user_id = ?
            ORDER BY s.saved_at DESC
        ");
        $stmt->execute([$userId]);
        $savedDestinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {}
}

// Fallback to session saved destinations or inventory JSON if DB returned empty
if (empty($savedDestinations)) {
    $savedIds = $_SESSION['saved_locations'] ?? [];
    if (!empty($savedIds)) {
        $inventoryFile = __DIR__ . '/data/stays_inventory.json';
        if (file_exists($inventoryFile)) {
            $allStays = json_decode(file_get_contents($inventoryFile), true) ?: [];
            foreach ($allStays as $st) {
                if (in_array((string)$st['id'], array_map('strval', $savedIds))) {
                    $savedDestinations[] = [
                        'id'          => $st['id'],
                        'title'       => $st['title'],
                        'city'        => $st['city'] ?? 'Metro Manila',
                        'image'       => $st['image'],
                        'price_from'  => $st['price_from'] ?? $st['price'] ?? 1000,
                        'branch_name' => $st['branch_name'] ?? 'Main Branch'
                    ];
                }
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
  <title>Destination Library - ComfortVue PH</title>
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

  <div class="container py-5 destination-library-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h2 class="fw-extrabold text-mocha mb-1"><i class="bi bi-pin-map-fill text-info me-2"></i>Destination Library</h2>
        <p class="text-secondary small mb-0">Saved hotels, express motels, and staycations in your personal shortlist</p>
      </div>
      <span class="badge bg-info rounded-pill px-3 py-2 fs-6"><?= count($savedDestinations) ?> Saved</span>
    </div>

    <?php if (empty($savedDestinations)): ?>
      <div class="text-center py-5 bg-white rounded-4 shadow-sm border p-4" style="border-color: var(--border)!important;">
        <i class="bi bi-heartbreak text-muted" style="font-size: 4rem;"></i>
        <h4 class="mt-3 text-mocha fw-bold">Your destination library is empty.</h4>
        <p class="text-secondary">Click the heart icon on any hotel or motel map pin to bookmark it for quick access.</p>
        <a href="home.php" class="btn btn-primary rounded-pill mt-2 px-4 shadow">
          <i class="bi bi-map-fill me-1"></i> Explore Interactive Map
        </a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($savedDestinations as $dest): ?>
          <div class="col-12 col-md-4">
            <div class="card shadow-sm border rounded-4 overflow-hidden h-100" style="background: var(--bg-card); border-color: var(--border)!important; transition: transform 0.2s;">
              <img src="<?= htmlspecialchars($dest['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($dest['title']) ?>" style="height: 190px; object-fit: cover;">
              <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                  <h5 class="fw-bold text-mocha mb-1"><?= htmlspecialchars($dest['title']) ?></h5>
                  <p class="small text-secondary mb-3">
                    <i class="bi bi-geo-alt-fill text-terracotta me-1"></i><?= htmlspecialchars($dest['city']) ?>
                    <?php if (!empty($dest['branch_name'])): ?>
                      • <span class="text-muted"><?= htmlspecialchars($dest['branch_name']) ?></span>
                    <?php endif; ?>
                  </p>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-color: var(--border)!important;">
                  <div>
                    <span class="small text-muted d-block" style="font-size:0.75rem;">FROM</span>
                    <span class="fw-extrabold text-sage fs-5">₱<?= number_format((float)$dest['price_from']) ?></span>
                  </div>
                  <div class="d-flex gap-2">
                    <a href="home.php?locate=<?= urlencode($dest['id']) ?>" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                      <i class="bi bi-compass me-1"></i> Map
                    </a>
                    <a href="home.php?tour=<?= urlencode($dest['id']) ?>" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold" title="Launch 360° Virtual Tour">
                      <i class="bi bi-vr me-1"></i> 360°
                    </a>
                  </div>
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
