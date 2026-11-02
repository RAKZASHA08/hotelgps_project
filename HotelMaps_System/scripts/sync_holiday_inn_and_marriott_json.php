<?php
// scripts/sync_holiday_inn_and_marriott_json.php
require_once __DIR__ . '/../db.php';

$jsonPath = __DIR__ . '/../data/stays_inventory.json';
$raw = json_decode(file_get_contents($jsonPath), true) ?: [];

// Fetch all hotels and their rooms from DB
$hotelsStmt = $pdo->query("SELECT * FROM hotels WHERE id IN (
    'hotel-holiday-inn-makati',
    'hotel-holiday-inn-galleria',
    'hotel-holiday-inn-express-newport',
    'hotel-holiday-inn-cebu',
    'hotel-holiday-inn-batangas',
    'hotel-manila-marriott',
    'hotel-clark-marriott',
    'hotel-courtyard-marriott-iloilo',
    'hotel-fairfield-marriott-cebu',
    'hotel-sheraton-manila-marriott'
)");
$hotels = $hotelsStmt->fetchAll();

$roomsStmt = $pdo->query("SELECT * FROM hotel_rooms WHERE hotel_id IN (
    'hotel-holiday-inn-makati',
    'hotel-holiday-inn-galleria',
    'hotel-holiday-inn-express-newport',
    'hotel-holiday-inn-cebu',
    'hotel-holiday-inn-batangas',
    'hotel-manila-marriott',
    'hotel-clark-marriott',
    'hotel-courtyard-marriott-iloilo',
    'hotel-fairfield-marriott-cebu',
    'hotel-sheraton-manila-marriott'
) ORDER BY price ASC");
$allRooms = $roomsStmt->fetchAll();

$roomsByHotel = [];
foreach ($allRooms as $rm) {
    $hid = $rm['hotel_id'];
    if (!isset($roomsByHotel[$hid])) $roomsByHotel[$hid] = [];

    $rNameLower = strtolower($rm['room_name'] ?? '');
    if (str_contains($rNameLower, 'suite') || str_contains($rNameLower, 'executive') || str_contains($rNameLower, 'presidential') || str_contains($rNameLower, 'chairman')) {
        $r360 = 'img/luxury_suite.jpg';
    } elseif (str_contains($rNameLower, 'view') || str_contains($rNameLower, 'corner')) {
        $r360 = 'img/skyline_condo.jpg';
    } else {
        $r360 = 'img/pano_bedroom.jpg';
    }

    $roomsByHotel[$hid][] = [
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

$existingIds = array_column($raw, 'id');

foreach ($hotels as $h) {
    $hid = $h['id'];
    $hotelRooms = $roomsByHotel[$hid] ?? [];
    $tags = array_map('trim', explode(',', $h['tags']));
    $hourly = json_decode($h['hourly_rates'], true) ?: [];

    $item = [
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
        'averageRate' => round(array_sum(array_column($hotelRooms, 'price')) / max(count($hotelRooms), 1), 2),
        'availableRoomsCount' => count($hotelRooms),
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
        'rooms' => $hotelRooms,
        'showroom' => [
            'initial_room' => 'suite',
            'rooms' => [
                'suite' => [
                    'title' => 'Executive Suite',
                    'image_360' => 'img/luxury_suite.jpg',
                    'hotspots' => [
                        ['x' => 74, 'y' => 52, 'label' => 'Marble Spa Bathroom', 'target' => 'bathroom'],
                        ['x' => 26, 'y' => 54, 'label' => 'Club Lounge & Skyline View', 'target' => 'lounge']
                    ]
                ],
                'bathroom' => [
                    'title' => 'Marble Spa Bathroom',
                    'image_360' => 'img/pano_bathroom.jpg',
                    'hotspots' => [
                        ['x' => 20, 'y' => 55, 'label' => 'Return to Suite', 'target' => 'suite']
                    ]
                ],
                'lounge' => [
                    'title' => 'Club Lounge & Skyline View',
                    'image_360' => 'img/skyline_condo.jpg',
                    'hotspots' => [
                        ['x' => 70, 'y' => 52, 'label' => 'Back into Suite', 'target' => 'suite']
                    ]
                ]
            ]
        ]
    ];

    $idx = array_search($hid, $existingIds);
    if ($idx !== false) {
        $raw[$idx] = $item;
    } else {
        $raw[] = $item;
        $existingIds[] = $hid;
    }
}

file_put_contents($jsonPath, json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "✓ Sync complete! stays_inventory.json now has " . count($raw) . " accommodations.\n";
