<?php
require_once __DIR__ . '/../db.php';

echo "=== Seeding Receptionist Accounts for ALL Hotel Branches ===\n";

if (!$pdo) {
    die("Database connection error!\n");
}

$hotels = $pdo->query("SELECT id, title, city, category FROM hotels ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($hotels) . " hotel branches in database.\n";

$passHash = password_hash('reception123', PASSWORD_DEFAULT);
$createdCount = 0;
$updatedCount = 0;

$accountList = [];

foreach ($hotels as $h) {
    $hotelId = $h['id'];
    $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $hotelId));
    // Clean, readable email
    $email = "desk." . $slug . "@stayfinder.ph";
    $fullName = $h['title'] . " Front Desk";
    $phone = !empty($h['phone']) ? $h['phone'] : '+63 2 8555 ' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);

    // Check if account with this email or hotel_id exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR (role = 'receptionist' AND hotel_id = ?)");
    $check->execute([$email, $hotelId]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $upd = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password_hash = ?, role = 'receptionist', hotel_id = ?, is_verified = 1 WHERE id = ?");
        $upd->execute([$fullName, $email, $passHash, $hotelId, $existing['id']]);
        $updatedCount++;
    } else {
        $ins = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, hotel_id, is_verified, verification_code, phone) VALUES (?, ?, ?, 'receptionist', ?, 1, '888888', ?)");
        $ins->execute([$fullName, $email, $passHash, $hotelId, $phone]);
        $createdCount++;
    }

    $accountList[] = [
        'hotel_id' => $hotelId,
        'title' => $h['title'],
        'city' => $h['city'] ?? 'Metro Manila',
        'category' => $h['category'] ?? 'hotel',
        'email' => $email,
        'password' => 'reception123'
    ];
}

echo "Seeded accounts summary:\n";
echo "- Created: $createdCount\n";
echo "- Updated: $updatedCount\n";
echo "- Total branches with front-desk accounts: " . count($accountList) . "\n";

// Save a JSON list of all branch credentials for quick reference and UI quick-select
file_put_contents(dirname(__DIR__) . '/data/reception_accounts.json', json_encode($accountList, JSON_PRETTY_PRINT));
echo "Saved branch directory to data/reception_accounts.json\n";
