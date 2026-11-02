<?php
require_once __DIR__ . '/../db.php';

echo "=== Setting Up Hotel Reception & Retailer Database Schema ===\n";

if (!$pdo) {
    die("Database connection failed!\n");
}

// 1. Create uploads directory for 360 panoramas
$uploadsDir = dirname(__DIR__) . '/uploads/panoramas';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
    echo "[OK] Created directory: $uploadsDir\n";
} else {
    echo "[INFO] Uploads directory already exists: $uploadsDir\n";
}

// 2. Add custom_360_url to hotel_rooms if not present
try {
    $cols = $pdo->query("SHOW COLUMNS FROM hotel_rooms LIKE 'custom_360_url'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE hotel_rooms ADD COLUMN custom_360_url VARCHAR(500) DEFAULT NULL AFTER image");
        echo "[OK] Added custom_360_url column to hotel_rooms table\n";
    } else {
        echo "[INFO] custom_360_url already exists in hotel_rooms\n";
    }
} catch (Exception $e) {
    echo "[WARN] custom_360_url check: " . $e->getMessage() . "\n";
}

// 3. Add hotel_id to users if not present
try {
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'hotel_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN hotel_id VARCHAR(64) DEFAULT NULL AFTER role");
        echo "[OK] Added hotel_id column to users table\n";
    } else {
        echo "[INFO] hotel_id already exists in users\n";
    }
} catch (Exception $e) {
    echo "[WARN] hotel_id check: " . $e->getMessage() . "\n";
}

// 4. Add is_read_by_hotel to bookings if not present
try {
    $cols = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'is_read_by_hotel'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN is_read_by_hotel TINYINT(1) DEFAULT 0 AFTER status");
        echo "[OK] Added is_read_by_hotel column to bookings table\n";
    } else {
        echo "[INFO] is_read_by_hotel already exists in bookings\n";
    }
} catch (Exception $e) {
    echo "[WARN] is_read_by_hotel check: " . $e->getMessage() . "\n";
}

// 5. Seed default Reception accounts for key partner hotel branches
$demoReceptionists = [
    [
        'email' => 'reception.shangri-boracay@stayfinder.ph',
        'full_name' => 'Shangri-La Boracay Reception Desk',
        'hotel_id' => 'resort-shang-boracay',
        'phone' => '+63 36 288 4988'
    ],
    [
        'email' => 'reception.sogo-alabang@stayfinder.ph',
        'full_name' => 'Hotel Sogo Alabang Front Desk',
        'hotel_id' => 'sogo-alabang-south',
        'phone' => '+63 2 8807 4300'
    ],
    [
        'email' => 'reception.okada@stayfinder.ph',
        'full_name' => 'Okada Manila Reception & Concierge',
        'hotel_id' => 'hotel-okada',
        'phone' => '+63 2 8888 0777'
    ],
    [
        'email' => 'reception.peninsula@stayfinder.ph',
        'full_name' => 'The Peninsula Manila Front Desk',
        'hotel_id' => 'hotel-peninsula',
        'phone' => '+63 2 8887 2888'
    ],
    [
        'email' => 'reception.solaire@stayfinder.ph',
        'full_name' => 'Solaire Entertainment City Reception',
        'hotel_id' => 'hotel-solaire',
        'phone' => '+63 2 8888 8888'
    ],
    [
        'email' => 'reception.discovery-boracay@stayfinder.ph',
        'full_name' => 'Discovery Shores Boracay Front Desk',
        'hotel_id' => 'resort-discovery-boracay',
        'phone' => '+63 36 288 4500'
    ]
];

$passHash = password_hash('reception123', PASSWORD_DEFAULT);

foreach ($demoReceptionists as $rec) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$rec['email']]);
    $existing = $stmt->fetch();
    if ($existing) {
        $upd = $pdo->prepare("UPDATE users SET full_name = ?, role = 'receptionist', hotel_id = ?, is_verified = 1 WHERE id = ?");
        $upd->execute([$rec['full_name'], $rec['hotel_id'], $existing['id']]);
        echo "[UPDATED] Reception user: {$rec['email']} -> {$rec['hotel_id']}\n";
    } else {
        $ins = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, hotel_id, is_verified, verification_code, phone) VALUES (?, ?, ?, 'receptionist', ?, 1, '888888', ?)");
        $ins->execute([$rec['full_name'], $rec['email'], $passHash, $rec['hotel_id'], $rec['phone']]);
        echo "[CREATED] Reception user: {$rec['email']} -> {$rec['hotel_id']}\n";
    }
}

echo "=== Setup Complete ===\n";
