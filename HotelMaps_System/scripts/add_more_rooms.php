<?php
require_once __DIR__ . '/../app/bootstrap.php';

if (!$pdo) {
    die("Database connection failed.");
}

$hotelsStmt = $pdo->query("SELECT id, price_from FROM hotels");
$hotels = $hotelsStmt->fetchAll(PDO::FETCH_ASSOC);

$roomTypes = [
    [
        'suffix' => 'std',
        'type_name' => 'Standard Room',
        'type' => 'Standard',
        'multiplier' => 1.0,
        'capacity' => '2 Adults',
        'bed' => '1 Double Bed',
        'size' => 25,
        'amenities' => 'Aircon, Fast Wi-Fi, Hot Shower, Cable TV, Work Desk',
        'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'suffix' => 'fam',
        'type_name' => 'Family Suite',
        'type' => 'Suite',
        'multiplier' => 1.8,
        'capacity' => '4 Adults, 2 Children',
        'bed' => '2 Queen Beds',
        'size' => 50,
        'amenities' => 'Living Area, Kitchenette, Bathtub, Smart TV, Free Breakfast',
        'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'suffix' => 'pen',
        'type_name' => 'Luxury Penthouse',
        'type' => 'Penthouse',
        'multiplier' => 3.5,
        'capacity' => '6 Adults',
        'bed' => '3 King Beds',
        'size' => 120,
        'amenities' => 'Panoramic View, Private Jacuzzi, Butler Service, Dining Room, Lounge',
        'image' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'suffix' => 'bal',
        'type_name' => 'Balcony Premier Room',
        'type' => 'Premier',
        'multiplier' => 1.3,
        'capacity' => '2 Adults',
        'bed' => '1 King Bed',
        'size' => 35,
        'amenities' => 'Private Balcony, Espresso Machine, Rain Shower, City View',
        'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80'
    ]
];

$insertedCount = 0;

$stmt = $pdo->prepare("INSERT INTO hotel_rooms (id, hotel_id, room_number, room_name, room_type, price, capacity, bed_type, size_sqm, amenities, status, total_booked, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available', 0, ?)");

foreach ($hotels as $hotel) {
    $hotelId = $hotel['id'];
    $basePrice = (float) $hotel['price_from'];

    // For each hotel, check how many rooms it currently has. If it has less than 8, add the new ones.
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM hotel_rooms WHERE hotel_id = ?");
    $countStmt->execute([$hotelId]);
    $currentCount = $countStmt->fetchColumn();

    if ($currentCount >= 8) {
        continue; // Skip if already populated heavily
    }

    $floor = rand(2, 8); // Random floor 2 to 8
    $roomNum = 1;

    foreach ($roomTypes as $rt) {
        $roomId = "rm-" . $hotelId . "-" . $rt['suffix'] . "-" . rand(1000, 9999);

        $price = round($basePrice * $rt['multiplier']);
        $roomNumberStr = "Room " . $floor . sprintf('%02d', $roomNum);

        try {
            $stmt->execute([
                $roomId,
                $hotelId,
                $roomNumberStr,
                $rt['type_name'],
                $rt['type'],
                $price,
                $rt['capacity'],
                $rt['bed'],
                $rt['size'],
                $rt['amenities'],
                $rt['image']
            ]);
            $insertedCount++;
            $roomNum++;
        } catch (Exception $e) {
            // Ignore duplicate keys if they accidentally happen
        }
    }
}

echo "Successfully added $insertedCount new room choices across hotels.";
