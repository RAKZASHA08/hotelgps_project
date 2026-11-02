<?php
// scripts/add_pico_sands.php - Seed Pico Sands Hotel at Hamilo Coast in Nasugbu, Batangas
require_once __DIR__ . '/../db.php';

if (!$pdo) {
    die("Database connection offline.\n");
}

echo "=== Seeding Pico Sands Hotel at Hamilo Coast, Nasugbu, Batangas ===\n";

try {
    // 1. Insert or update in `hotels`
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
        'resort-pico-sands',
        'Pico Sands Hotel',
        'SM Hotels and Conventions Corp.',
        'resort',
        'Coastal Beach & Lagoon Resort',
        'Pico de Loro Cove, Hamilo Coast, Barangay Papaya, Nasugbu, Batangas 4231',
        'Nasugbu Batangas',
        4.87,
        340,
        810,
        31,
        6500.00,
        'night',
        14.179700,
        120.627200,
        'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&q=80',
        'Pico Sands Hotel, Hamilo Coast, Pico de Loro Cove, Nasugbu Batangas, Private Beach Club, Lagoon View, Rain Spa',
        'Rooms Available (4 Suites)',
        'status-walkin',
        'Hamilo Coast Nasugbu Branch',
        'Nasugbu Town Bus Terminal / Hamilo Coast Shuttle Service',
        'CAVITEX / Ternate-Nasugbu Highway & Resort Shuttle',
        '15 mins shuttle from Nasugbu Hub',
        json_encode([
            '2hr' => 3500,
            '3hr' => 4500,
            '6hr' => 6500,
            '12hr' => 8900,
            '24hr' => 12000
        ])
    ];

    $hotelStmt->execute($hotelData);
    echo "✓ Seeded hotel: Pico Sands Hotel at Hamilo Coast\n";

    // 2. Insert or update rooms in `hotel_rooms`
    $rooms = [
        [
            'id' => 'rm-pico-101',
            'hotel_id' => 'resort-pico-sands',
            'room_number' => 'Room 101',
            'room_name' => 'Superior Lagoon View Room',
            'room_type' => 'Superior Room',
            'price' => 6500.00,
            'capacity' => '2 Adults, 1 Child',
            'bed_type' => '1 King Bed or 2 Double Beds',
            'size_sqm' => 37,
            'amenities' => 'Private Balcony facing Man-Made Lagoon, Rain Shower, Wi-Fi, LED Cable TV, Mini Bar, Beach Club Shuttle',
            'status' => 'Available',
            'total_booked' => 15,
            'image' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id' => 'rm-pico-202',
            'hotel_id' => 'resort-pico-sands',
            'room_number' => 'Room 202',
            'room_name' => 'Premier Mountain & Lagoon Room',
            'room_type' => 'Deluxe Room',
            'price' => 7800.00,
            'capacity' => '2 Adults, 1 Child',
            'bed_type' => '1 King Bed',
            'size_sqm' => 42,
            'amenities' => 'Scenic Pico de Loro Mountain Panorama, Balcony, Deep Soaking Tub, Tea & Coffee Set, Free Beach Towels',
            'status' => 'Available',
            'total_booked' => 10,
            'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id' => 'rm-pico-301',
            'hotel_id' => 'resort-pico-sands',
            'room_number' => 'Suite 301',
            'room_name' => 'Pico Junior Beachfront Suite',
            'room_type' => 'Junior Suite',
            'price' => 12500.00,
            'capacity' => '3 Adults',
            'bed_type' => '1 King Bed + Daybed',
            'size_sqm' => 62,
            'amenities' => 'Separate Living Lounge, Panoramic Lagoon & Mountain Terrace, Country Club Full Access, Robes, Espresso Bar',
            'status' => 'Available',
            'total_booked' => 5,
            'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id' => 'rm-pico-401',
            'hotel_id' => 'resort-pico-sands',
            'room_number' => 'Penthouse 401',
            'room_name' => 'Hamilo Presidential Penthouse Loft',
            'room_type' => 'Presidential Suite',
            'price' => 22000.00,
            'capacity' => '4 Adults, 2 Children',
            'bed_type' => '2 King Bedrooms',
            'size_sqm' => 120,
            'amenities' => 'Duplex High-Ceiling Penthouse, Wrap-around Balcony with Cove Sunset View, Jacuzzi, Dining Area, Butler Concierge',
            'status' => 'Available',
            'total_booked' => 1,
            'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80'
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
            ?, 'nightly', 'Overnight'
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
            $r['image']
        ]);
        echo "   - Suite: {$r['room_number']} {$r['room_name']}\n";
    }

    // 3. Insert traveler reviews
    $revStmt = $pdo->prepare("
        INSERT INTO room_reviews (room_id, user_id, user_name, rating, comment)
        SELECT ?, ?, ?, ?, ?
        WHERE NOT EXISTS (
            SELECT 1 FROM room_reviews WHERE room_id = ? AND user_id = ?
        )
    ");

    $reviews = [
        [
            'rm-pico-101', 'guest_pico_01', 'Jessica Cruz', 5,
            'The lagoon view from the balcony was so peaceful! Access to the Pico de Loro beach club and water sports made it the ultimate Batangas weekend getaway.',
            'rm-pico-101', 'guest_pico_01'
        ],
        [
            'rm-pico-202', 'guest_pico_02', 'Ramon Bautista', 5,
            'Smooth scenic drive from Manila through the Kaybiang Tunnel. Pristine cove, clear waters, and very spacious rooms. Highly recommended!',
            'rm-pico-202', 'guest_pico_02'
        ]
    ];

    foreach ($reviews as $rev) {
        $revStmt->execute($rev);
    }
    echo "✓ Seeded verified traveler reviews.\n";

    echo "=== Pico Sands Hotel seeded into MySQL successfully! ===\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
