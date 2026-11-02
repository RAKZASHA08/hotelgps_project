<?php
// scripts/add_taal_vista.php - Seed script for Taal Vista Hotel in Tagaytay
require_once __DIR__ . '/../db.php';

if (!$pdo) {
    die("Database connection offline.\n");
}

echo "=== Seeding Taal Vista Hotel in Tagaytay ===\n";

try {
    // 1. Insert or update Taal Vista Hotel in `hotels` table
    $hotelStmt = $pdo->prepare("
        INSERT INTO hotels (
            id, title, brand, category, category_display, address, city, rating,
            rating_count, views_count, total_booked, price_from, duration_label,
            lat, lng, image, tags, status_text, status_badge, branch_name,
            nearest_terminal, transit_type, walk_time, hourly_rates
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?
        ) ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            brand = VALUES(brand),
            category = VALUES(category),
            category_display = VALUES(category_display),
            address = VALUES(address),
            city = VALUES(city),
            rating = VALUES(rating),
            rating_count = VALUES(rating_count),
            views_count = VALUES(views_count),
            total_booked = VALUES(total_booked),
            price_from = VALUES(price_from),
            duration_label = VALUES(duration_label),
            lat = VALUES(lat),
            lng = VALUES(lng),
            image = VALUES(image),
            tags = VALUES(tags),
            status_text = VALUES(status_text),
            status_badge = VALUES(status_badge),
            branch_name = VALUES(branch_name),
            nearest_terminal = VALUES(nearest_terminal),
            transit_type = VALUES(transit_type),
            walk_time = VALUES(walk_time),
            hourly_rates = VALUES(hourly_rates)
    ");

    $hotelData = [
        'hotel-taal-vista',
        'Taal Vista Hotel',
        'SM Hotels and Conventions Corp.',
        'hotel',
        'Iconic Heritage Mountain Resort',
        'Kilometer 60, Aguinaldo Highway, Kaybagal South, Tagaytay City, 4120 Cavite',
        'Tagaytay',
        4.88,
        320,
        740,
        35,
        6800.00,
        'night',
        14.094580,
        120.936650,
        'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',
        'Taal Volcano View, Tagaytay Ridge, Rain The Spa, Veranda Heritage Dining, Mountain Breeze, Heated Pool',
        'Rooms Available (4 Suites)',
        'status-walkin',
        'Tagaytay Ridge Flagship Branch',
        'Tagaytay Olivarez Transport Hub / Sky Ranch Terminal',
        'Tagaytay - Nasugbu Highway / P2P Bus & UV Express',
        '3-5 mins ride / walk',
        json_encode([
            '2hr' => 3800,
            '3hr' => 4800,
            '6hr' => 6800,
            '12hr' => 9500,
            '24hr' => 13500
        ])
    ];

    $hotelStmt->execute($hotelData);
    echo "✓ Inserted / updated hotel: Taal Vista Hotel\n";

    // 2. Insert or update rooms in `hotel_rooms`
    $rooms = [
        [
            'id' => 'rm-taal-vista-101',
            'hotel_id' => 'hotel-taal-vista',
            'room_number' => 'Room 101',
            'room_name' => 'Superior King Lake Wing',
            'room_type' => 'Superior Room',
            'price' => 6800.00,
            'capacity' => '2 Adults',
            'bed_type' => '1 King Bed',
            'size_sqm' => 32,
            'amenities' => 'Taal Volcano & Garden View, High-Speed Wi-Fi, Private Balcony, Hot Rain Shower, Mini Bar, Cable TV',
            'status' => 'Available',
            'total_booked' => 14,
            'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80',
            'rate_type' => 'nightly',
            'duration' => 'Overnight'
        ],
        [
            'id' => 'rm-taal-vista-102',
            'hotel_id' => 'hotel-taal-vista',
            'room_number' => 'Room 202',
            'room_name' => 'Deluxe Lake View Suite',
            'room_type' => 'Deluxe Room',
            'price' => 8900.00,
            'capacity' => '2 Adults, 1 Child',
            'bed_type' => '1 King Bed or 2 Double Beds',
            'size_sqm' => 40,
            'amenities' => 'Direct Taal Lake Panoramic Vista, Private Sunset Balcony, Deep Soaking Tub, Nespresso Machine, Work Desk',
            'status' => 'Available',
            'total_booked' => 11,
            'image' => 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=600&q=80',
            'rate_type' => 'nightly',
            'duration' => 'Overnight'
        ],
        [
            'id' => 'rm-taal-vista-201',
            'hotel_id' => 'hotel-taal-vista',
            'room_number' => 'Suite 301',
            'room_name' => 'Premier Ridge Executive Suite',
            'room_type' => 'Executive Suite',
            'price' => 14500.00,
            'capacity' => '3 Adults',
            'bed_type' => '1 King Bed',
            'size_sqm' => 68,
            'amenities' => 'Ridge & Caldera Panorama, Separate Living Salon, Executive Club Privileges, Marble Bath with Jacuzzi, 24/7 Room Service',
            'status' => 'Available',
            'total_booked' => 7,
            'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80',
            'rate_type' => 'nightly',
            'duration' => 'Overnight'
        ],
        [
            'id' => 'rm-taal-vista-301',
            'hotel_id' => 'hotel-taal-vista',
            'room_number' => 'Penthouse 401',
            'room_name' => 'Tagaytay Heritage Presidential Villa Suite',
            'room_type' => 'Presidential Suite',
            'price' => 28500.00,
            'capacity' => '4 Adults, 2 Children',
            'bed_type' => '2 King Bedrooms',
            'size_sqm' => 125,
            'amenities' => '180-Degree Unobstructed Taal Lake Balcony, Private Dining Room for 8, Whirlpool Jacuzzi, Butler Service, Kitchenette',
            'status' => 'Available',
            'total_booked' => 3,
            'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80',
            'rate_type' => 'nightly',
            'duration' => 'Overnight'
        ]
    ];

    $roomStmt = $pdo->prepare("
        INSERT INTO hotel_rooms (
            id, hotel_id, room_number, room_name, room_type, price,
            capacity, bed_type, size_sqm, amenities, status, total_booked,
            image, rate_type, duration
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?
        ) ON DUPLICATE KEY UPDATE
            room_number = VALUES(room_number),
            room_name = VALUES(room_name),
            room_type = VALUES(room_type),
            price = VALUES(price),
            capacity = VALUES(capacity),
            bed_type = VALUES(bed_type),
            size_sqm = VALUES(size_sqm),
            amenities = VALUES(amenities),
            status = VALUES(status),
            total_booked = VALUES(total_booked),
            image = VALUES(image),
            rate_type = VALUES(rate_type),
            duration = VALUES(duration)
    ");

    foreach ($rooms as $r) {
        $roomStmt->execute([
            $r['id'],
            $r['hotel_id'],
            $r['room_number'],
            $r['room_name'],
            $r['room_type'],
            $r['price'],
            $r['capacity'],
            $r['bed_type'],
            $r['size_sqm'],
            $r['amenities'],
            $r['status'],
            $r['total_booked'],
            $r['image'],
            $r['rate_type'],
            $r['duration']
        ]);
        echo "✓ Seeded room: {$r['room_number']} - {$r['room_name']}\n";
    }

    // 3. Insert reviews into `room_reviews`
    $revStmt = $pdo->prepare("
        INSERT INTO room_reviews (room_id, user_id, user_name, rating, comment)
        SELECT ?, ?, ?, ?, ?
        WHERE NOT EXISTS (
            SELECT 1 FROM room_reviews WHERE room_id = ? AND user_id = ?
        )
    ");

    $reviews = [
        [
            'rm-taal-vista-101', 'guest_taal_01', 'Maria Santos', 5,
            'Spectacular morning view of Taal Volcano right from our balcony! The cool Tagaytay breeze made our stay so relaxing. Breakfast buffet at the Veranda was top notch.',
            'rm-taal-vista-101', 'guest_taal_01'
        ],
        [
            'rm-taal-vista-102', 'guest_taal_02', 'Carl Andrei Mendoza', 5,
            'Historic charm meets modern comfort. Best place to stay in Tagaytay for scenic relaxation and peaceful caldera panoramas.',
            'rm-taal-vista-102', 'guest_taal_02'
        ]
    ];

    foreach ($reviews as $rev) {
        $revStmt->execute($rev);
    }
    echo "✓ Seeded sample traveler reviews.\n";

    echo "=== MySQL Seeding Completed Successfully! ===\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
