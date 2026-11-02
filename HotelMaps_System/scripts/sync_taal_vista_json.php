<?php
// scripts/sync_taal_vista_json.php - Sync Taal Vista Hotel into data/stays_inventory.json
$jsonPath = __DIR__ . '/../data/stays_inventory.json';

if (!file_exists($jsonPath)) {
    die("Inventory file not found: $jsonPath\n");
}

$stays = json_decode(file_get_contents($jsonPath), true) ?: [];

$taalVistaStay = [
    'id' => 'hotel-taal-vista',
    'title' => 'Taal Vista Hotel',
    'branch_name' => 'Tagaytay Ridge Flagship Branch',
    'brand' => 'SM Hotels and Conventions Corp.',
    'category' => 'hotel',
    'categoryDisplay' => 'Iconic Heritage Mountain Resort',
    'address' => 'Kilometer 60, Aguinaldo Highway, Kaybagal South, Tagaytay City, 4120 Cavite',
    'city' => 'Tagaytay',
    'rating' => 4.88,
    'ratingCount' => 320,
    'viewsCount' => 740,
    'totalBooked' => 35,
    'price' => 6800,
    'price_from' => 6800,
    'averageRate' => 14675,
    'availableRoomsCount' => 4,
    'durationLabel' => 'night',
    'rateTypeLabel' => 'OVERNIGHT RATE',
    'lat' => 14.094580,
    'lng' => 120.936650,
    'image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',
    'tags' => [
        'Taal Volcano View',
        'Tagaytay Ridge',
        'Rain The Spa',
        'Veranda Heritage Dining',
        'Mountain Breeze',
        'Heated Pool'
    ],
    'statusText' => 'Rooms Available (4 Suites)',
    'statusBadgeClass' => 'status-walkin',
    'instant' => true,
    'nearest_terminal' => 'Tagaytay Olivarez Transport Hub / Sky Ranch Terminal',
    'transit_type' => 'Tagaytay - Nasugbu Highway / P2P Bus & UV Express',
    'walk_time' => '3-5 mins ride / walk',
    'hourly_rates' => [
        '2hr' => 3800,
        '3hr' => 4800,
        '6hr' => 6800,
        '12hr' => 9500,
        '24hr' => 13500
    ],
    'rooms' => [
        [
            'id' => 'rm-taal-vista-101',
            'room_number' => 'Room 101',
            'room_name' => 'Superior King Lake Wing',
            'room_type' => 'Superior Room',
            'price' => 6800,
            'rate_type' => 'nightly',
            'duration' => 'Overnight',
            'capacity' => '2 Adults',
            'bed_type' => '1 King Bed',
            'size_sqm' => 32,
            'amenities' => 'Taal Volcano & Garden View, High-Speed Wi-Fi, Private Balcony, Hot Rain Shower, Mini Bar, Cable TV',
            'status' => 'Available',
            'total_booked' => 14,
            'review_count' => 24,
            'avg_rating' => 4.8,
            'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80',
            'image_360' => 'img/pano_bedroom.jpg'
        ],
        [
            'id' => 'rm-taal-vista-102',
            'room_number' => 'Room 202',
            'room_name' => 'Deluxe Lake View Suite',
            'room_type' => 'Deluxe Room',
            'price' => 8900,
            'rate_type' => 'nightly',
            'duration' => 'Overnight',
            'capacity' => '2 Adults, 1 Child',
            'bed_type' => '1 King Bed or 2 Double Beds',
            'size_sqm' => 40,
            'amenities' => 'Direct Taal Lake Panoramic Vista, Private Sunset Balcony, Deep Soaking Tub, Nespresso Machine, Work Desk',
            'status' => 'Available',
            'total_booked' => 11,
            'review_count' => 18,
            'avg_rating' => 4.9,
            'image' => 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=600&q=80',
            'image_360' => 'img/pano_bedroom.jpg'
        ],
        [
            'id' => 'rm-taal-vista-201',
            'room_number' => 'Suite 301',
            'room_name' => 'Premier Ridge Executive Suite',
            'room_type' => 'Executive Suite',
            'price' => 14500,
            'rate_type' => 'nightly',
            'duration' => 'Overnight',
            'capacity' => '3 Adults',
            'bed_type' => '1 King Bed',
            'size_sqm' => 68,
            'amenities' => 'Ridge & Caldera Panorama, Separate Living Salon, Executive Club Privileges, Marble Bath with Jacuzzi, 24/7 Room Service',
            'status' => 'Available',
            'total_booked' => 7,
            'review_count' => 12,
            'avg_rating' => 5.0,
            'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80',
            'image_360' => 'img/luxury_suite.jpg'
        ],
        [
            'id' => 'rm-taal-vista-301',
            'room_number' => 'Penthouse 401',
            'room_name' => 'Tagaytay Heritage Presidential Villa Suite',
            'room_type' => 'Presidential Suite',
            'price' => 28500,
            'rate_type' => 'nightly',
            'duration' => 'Overnight',
            'capacity' => '4 Adults, 2 Children',
            'bed_type' => '2 King Bedrooms',
            'size_sqm' => 125,
            'amenities' => '180-Degree Unobstructed Taal Lake Balcony, Private Dining Room for 8, Whirlpool Jacuzzi, Butler Service, Kitchenette',
            'status' => 'Available',
            'total_booked' => 3,
            'review_count' => 8,
            'avg_rating' => 5.0,
            'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80',
            'image_360' => 'img/luxury_suite.jpg'
        ]
    ],
    'showroom' => [
        'initial_room' => 'suite',
        'rooms' => [
            'suite' => [
                'title' => 'Executive Ridge Suite',
                'image_360' => 'img/luxury_suite.jpg',
                'hotspots' => [
                    ['x' => 74, 'y' => 52, 'label' => 'Marble Spa Bathroom', 'target' => 'bathroom'],
                    ['x' => 26, 'y' => 54, 'label' => 'Ridge View Lounge & Terrace', 'target' => 'lounge']
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
                'title' => 'Ridge View Lounge & Terrace',
                'image_360' => 'img/skyline_condo.jpg',
                'hotspots' => [
                    ['x' => 70, 'y' => 52, 'label' => 'Back into Suite', 'target' => 'suite']
                ]
            ]
        ]
    ]
];

// Check if already in array
$found = false;
foreach ($stays as $idx => $s) {
    if ($s['id'] === 'hotel-taal-vista') {
        $stays[$idx] = $taalVistaStay;
        $found = true;
        break;
    }
}
if (!$found) {
    $stays[] = $taalVistaStay;
}

file_put_contents($jsonPath, json_encode($stays, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "✓ Sync complete! stays_inventory.json now has " . count($stays) . " accommodations.\n";
