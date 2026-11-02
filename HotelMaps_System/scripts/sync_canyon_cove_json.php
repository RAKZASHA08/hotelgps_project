<?php
// scripts/sync_canyon_cove_json.php
require_once __DIR__ . '/../db.php';

$jsonPath = __DIR__ . '/../data/stays_inventory.json';
$raw = json_decode(file_get_contents($jsonPath), true) ?: [];

$hotelStmt = $pdo->prepare("SELECT * FROM hotels WHERE id = 'resort-canyon-cove'");
$hotelStmt->execute();
$h = $hotelStmt->fetch();

if (!$h) {
    die("Hotel not found in DB!\n");
}

$roomsStmt = $pdo->prepare("SELECT * FROM hotel_rooms WHERE hotel_id = 'resort-canyon-cove' ORDER BY price ASC");
$roomsStmt->execute();
$dbRooms = $roomsStmt->fetchAll();

$rooms = [];
foreach ($dbRooms as $rm) {
    $rNameLower = strtolower($rm['room_name'] ?? '');
    if (str_contains($rNameLower, 'villa') || str_contains($rNameLower, 'suite')) {
        $r360 = 'img/luxury_suite.jpg';
    } elseif (str_contains($rNameLower, 'beachfront') || str_contains($rNameLower, 'oceanview')) {
        $r360 = 'img/skyline_condo.jpg';
    } else {
        $r360 = 'img/pano_bedroom.jpg';
    }

    $rooms[] = [
        'id' => $rm['id'],
        'room_number' => $rm['room_number'],
        'room_name' => $rm['room_name'],
        'room_type' => $rm['room_type'],
        'price' => (float)$rm['price'],
        'rate_type' => 'nightly',
        'duration' => 'Overnight',
        'capacity' => $rm['capacity'],
        'bed_type' => $rm['bed_type'],
        'size_sqm' => (int)$rm['size_sqm'],
        'amenities' => $rm['amenities'],
        'status' => $rm['status'],
        'total_booked' => (int)$rm['total_booked'],
        'review_count' => 1,
        'avg_rating' => 5.0,
        'image' => $rm['image'],
        'image_360' => $r360
    ];
}

$tags = array_map('trim', explode(',', $h['tags']));
$hourly = json_decode($h['hourly_rates'], true) ?: [];

$canyonStay = [
    'id' => $h['id'],
    'title' => $h['title'],
    'branch_name' => $h['branch_name'],
    'brand' => $h['brand'],
    'category' => $h['category'],
    'categoryDisplay' => $h['category_display'],
    'address' => $h['address'],
    'city' => $h['city'],
    'rating' => (float)$h['rating'],
    'ratingCount' => (int)$h['rating_count'],
    'viewsCount' => (int)$h['views_count'],
    'totalBooked' => (int)$h['total_booked'],
    'price' => (float)$h['price_from'],
    'price_from' => (float)$h['price_from'],
    'averageRate' => round(array_sum(array_column($rooms, 'price')) / max(count($rooms), 1), 2),
    'availableRoomsCount' => count($rooms),
    'durationLabel' => $h['duration_label'],
    'rateTypeLabel' => 'OVERNIGHT RATE',
    'lat' => (float)$h['lat'],
    'lng' => (float)$h['lng'],
    'image' => $h['image'],
    'tags' => $tags,
    'statusText' => $h['status_text'],
    'statusBadgeClass' => $h['status_badge'],
    'instant' => true,
    'nearest_terminal' => $h['nearest_terminal'],
    'transit_type' => $h['transit_type'],
    'walk_time' => $h['walk_time'],
    'hourly_rates' => $hourly,
    'rooms' => $rooms,
    'showroom' => [
        'initial_room' => 'suite',
        'rooms' => [
            'suite' => [
                'title' => 'Executive Oceanview Suite',
                'image_360' => 'img/luxury_suite.jpg',
                'hotspots' => [
                    ['x' => 74, 'y' => 52, 'label' => 'Marble Spa Bathroom', 'target' => 'bathroom'],
                    ['x' => 26, 'y' => 54, 'label' => 'Ocean Sunset Veranda', 'target' => 'balcony']
                ]
            ],
            'bathroom' => [
                'title' => 'Marble Spa Bathroom',
                'image_360' => 'img/pano_bathroom.jpg',
                'hotspots' => [
                    ['x' => 20, 'y' => 55, 'label' => 'Return to Suite', 'target' => 'suite']
                ]
            ],
            'balcony' => [
                'title' => 'Ocean Sunset Veranda',
                'image_360' => 'img/skyline_condo.jpg',
                'hotspots' => [
                    ['x' => 70, 'y' => 52, 'label' => 'Back into Suite', 'target' => 'suite']
                ]
            ]
        ]
    ]
];

$existingIds = array_column($raw, 'id');
$idx = array_search('resort-canyon-cove', $existingIds);

if ($idx !== false) {
    $raw[$idx] = $canyonStay;
} else {
    $raw[] = $canyonStay;
}

file_put_contents($jsonPath, json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "✓ Sync complete! stays_inventory.json now has " . count($raw) . " accommodations (Canyon Cove Hotel & Spa added).\n";
