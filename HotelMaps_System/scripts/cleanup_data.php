<?php
require_once __DIR__ . '/../db.php';
if (!$pdo) {
    die("Database connection failed.\n");
}

echo "=== START CLEANUP: METRO MANILA ONLY & REMOVE DEMO ACCOUNTS ===\n";

$nonMetroManilaCities = [
    'Baguio City',
    'Naga City Bicol',
    'Cagayan de Oro',
    'Cebu City',
    'Davao City',
    'San Fernando Pampanga',
    'Cebu & Mactan',
    'Boracay Island',
    'El Nido Palawan',
    'Samal Island (Davao)',
    'Puerto Princesa'
];

$inClause = implode(',', array_fill(0, count($nonMetroManilaCities), '?'));

// 1. Find hotel IDs to remove
$stmt = $pdo->prepare("SELECT id, title, city FROM hotels WHERE city IN ($inClause)");
$stmt->execute($nonMetroManilaCities);
$hotelsToRemove = $stmt->fetchAll();
$hotelIds = array_column($hotelsToRemove, 'id');

echo "Found " . count($hotelIds) . " hotels outside Metro Manila to remove:\n";
foreach ($hotelsToRemove as $h) {
    echo "  - [{$h['id']}] {$h['title']} in {$h['city']}\n";
}

if (!empty($hotelIds)) {
    $hIn = implode(',', array_fill(0, count($hotelIds), '?'));

    // Find rooms to remove reviews
    $rmStmt = $pdo->prepare("SELECT id FROM hotel_rooms WHERE hotel_id IN ($hIn)");
    $rmStmt->execute($hotelIds);
    $roomIds = $rmStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($roomIds)) {
        $rIn = implode(',', array_fill(0, count($roomIds), '?'));
        $delRev = $pdo->prepare("DELETE FROM room_reviews WHERE room_id IN ($rIn)");
        $delRev->execute($roomIds);
        echo "Deleted room reviews for removed rooms.\n";
    }

    // Delete hotel_rooms
    $delRooms = $pdo->prepare("DELETE FROM hotel_rooms WHERE hotel_id IN ($hIn)");
    $delRooms->execute($hotelIds);
    echo "Deleted hotel_rooms for non-Metro Manila hotels.\n";

    // Delete saved_locations
    $delSaved = $pdo->prepare("DELETE FROM saved_locations WHERE hotel_id IN ($hIn)");
    $delSaved->execute($hotelIds);
    echo "Deleted saved_locations for non-Metro Manila hotels.\n";

    // Delete bookings for these hotels
    $delBk = $pdo->prepare("DELETE FROM bookings WHERE hotel_id IN ($hIn)");
    $delBk->execute($hotelIds);
    echo "Deleted bookings for non-Metro Manila hotels.\n";

    // Delete hotels
    $delHotels = $pdo->prepare("DELETE FROM hotels WHERE id IN ($hIn)");
    $delHotels->execute($hotelIds);
    echo "Deleted hotels outside Metro Manila.\n";
}

// 2. Remove existing non-admin user accounts
echo "\nRemoving existing user accounts (keeping admin@comfortvue.ph)...\n";
$delUsers = $pdo->query("DELETE FROM users WHERE role != 'admin' OR email != 'admin@comfortvue.ph'");
echo "Deleted non-admin user rows.\n";

// Clear dummy bookings
$delDummyBk = $pdo->query("DELETE FROM bookings WHERE user_id IS NOT NULL AND user_id != '99'");
echo "Cleared existing user bookings.\n";

// Clear saved locations
$pdo->query("TRUNCATE TABLE saved_locations");
echo "Reset saved_locations.\n";

// Reset data/user_bookings.json
$userBookingsPath = __DIR__ . '/../data/user_bookings.json';
file_put_contents($userBookingsPath, json_encode([], JSON_PRETTY_PRINT));
echo "Cleared data/user_bookings.json.\n";

// 3. Clean data/stays_inventory.json to only contain Metro Manila
$invPath = __DIR__ . '/../data/stays_inventory.json';
if (file_exists($invPath)) {
    $raw = json_decode(file_get_contents($invPath), true) ?: [];
    $filtered = array_values(array_filter($raw, function($s) use ($nonMetroManilaCities) {
        $city = $s['city'] ?? '';
        return !in_array($city, $nonMetroManilaCities);
    }));
    file_put_contents($invPath, json_encode($filtered, JSON_PRETTY_PRINT));
    echo "Updated data/stays_inventory.json: kept " . count($filtered) . " of " . count($raw) . " items.\n";
}

echo "\n=== CLEANUP COMPLETED SUCCESSFULLY ===\n";
