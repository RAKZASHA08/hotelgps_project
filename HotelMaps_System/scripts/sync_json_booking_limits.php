<?php
// scripts/sync_json_booking_limits.php
require_once __DIR__ . '/../db.php';

$jsonPath = __DIR__ . '/../data/stays_inventory.json';
if (!file_exists($jsonPath)) {
    die("JSON file not found: $jsonPath\n");
}

$hotels = json_decode(file_get_contents($jsonPath), true);
if (!is_array($hotels)) {
    die("Invalid JSON content.\n");
}

// Fetch all rooms from MySQL with booking_limit and official total_booked
$roomStmt = $pdo->query("SELECT * FROM hotel_rooms");
$dbRooms = [];
while ($row = $roomStmt->fetch(PDO::FETCH_ASSOC)) {
    $dbRooms[$row['id']] = $row;
}

// Fetch confirmed booking counts per room
$bkStmt = $pdo->query("SELECT room_id, COUNT(*) as cnt FROM bookings WHERE status = 'CONFIRMED' GROUP BY room_id");
$confirmedBookings = [];
while ($row = $bkStmt->fetch(PDO::FETCH_ASSOC)) {
    $confirmedBookings[$row['room_id']] = (int) $row['cnt'];
}

// Fetch confirmed booking counts per hotel
$hbStmt = $pdo->query("SELECT hotel_id, COUNT(*) as cnt FROM bookings WHERE status = 'CONFIRMED' GROUP BY hotel_id");
$confirmedHotelBookings = [];
while ($row = $hbStmt->fetch(PDO::FETCH_ASSOC)) {
    $confirmedHotelBookings[$row['hotel_id']] = (int) $row['cnt'];
}

$updatedHotels = 0;
$updatedRooms = 0;

foreach ($hotels as &$hotel) {
    $hid = $hotel['id'];
    $hotel['totalBooked'] = $confirmedHotelBookings[$hid] ?? 0;

    if (!empty($hotel['rooms']) && is_array($hotel['rooms'])) {
        foreach ($hotel['rooms'] as &$room) {
            $rid = $room['id'];
            $dbRoom = $dbRooms[$rid] ?? null;

            $limit = $dbRoom ? (int) $dbRoom['booking_limit'] : 10;
            $booked = $confirmedBookings[$rid] ?? 0;
            $avail = max(0, $limit - $booked);

            $room['booking_limit'] = $limit;
            $room['total_booked'] = $booked;
            $room['available_slots'] = $avail;

            // If booked reaches limit, update status to Sold Out if Available
            if ($booked >= $limit && ($room['status'] ?? 'Available') === 'Available') {
                $room['status'] = 'Sold Out';
            } elseif ($booked < $limit && ($room['status'] ?? '') === 'Sold Out') {
                $room['status'] = 'Available';
            }

            $updatedRooms++;
        }
    }
    $updatedHotels++;
}

file_put_contents($jsonPath, json_encode($hotels, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "Successfully updated $updatedHotels hotels and $updatedRooms rooms in stays_inventory.json with official counts and booking limits.\n";
