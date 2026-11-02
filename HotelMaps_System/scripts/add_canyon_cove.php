<?php
// scripts/add_canyon_cove.php - Seed Canyon Cove Hotel & Spa Batangas
require_once __DIR__ . '/../db.php';

if (!$pdo) {
    die("Database connection offline.\n");
}

echo "=== Seeding Canyon Cove Hotel & Spa in Nasugbu, Batangas ===\n";

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
        'resort-canyon-cove',
        'Canyon Cove Hotel & Spa Batangas',
        'Canyon Hotels & Resorts',
        'resort',
        'Beachfront Resort & Spa',
        'Far East Road, Piloto Wawa, Nasugbu, Batangas 4231',
        'Nasugbu Batangas',
        4.68,
        290,
        710,
        28,
        4500.00,
        'night',
        14.053600,
        120.627800,
        'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
        'Canyon Cove Hotel & Spa, Canyon Cove, Nasugbu Batangas, White Sand Beach, Freeform Pool, Water Sports, Oceanview Spa',
        'Rooms Available (4 Suites)',
        'status-walkin',
        'Nasugbu Beachfront Branch',
        'Nasugbu Central Bus Terminal',
        'PITX / Buendia - Nasugbu Provincial Busway / Shuttle',
        '5-8 mins ride from Nasugbu Hub',
        json_encode([
            '2hr' => 2500,
            '3hr' => 3200,
            '6hr' => 4500,
            '12hr' => 6200,
            '24hr' => 8500
        ])
    ];

    $hotelStmt->execute($hotelData);
    echo "✓ Seeded hotel: Canyon Cove Hotel & Spa Batangas\n";

    // 2. Insert or update rooms in `hotel_rooms`
    $rooms = [
        [
            'id' => 'rm-canyon-101',
            'hotel_id' => 'resort-canyon-cove',
            'room_number' => 'Room 101',
            'room_name' => 'Superior Garden View Room',
            'room_type' => 'Superior Room',
            'price' => 4500.00,
            'capacity' => '2 Adults, 1 Child',
            'bed_type' => '1 King Bed or 2 Single Beds',
            'size_sqm' => 35,
            'amenities' => 'Lush Garden Balcony, Hot Rain Shower, Air Conditioning, Cable TV, Free Pool Access, Beach Towels',
            'status' => 'Available',
            'total_booked' => 14,
            'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id' => 'rm-canyon-202',
            'hotel_id' => 'resort-canyon-cove',
            'room_number' => 'Room 202',
            'room_name' => 'Deluxe Beachfront Oceanview Room',
            'room_type' => 'Deluxe Room',
            'price' => 5800.00,
            'capacity' => '2 Adults, 1 Child',
            'bed_type' => '1 King Bed',
            'size_sqm' => 40,
            'amenities' => 'Direct South China Sea Sunset View, Private Beach Balcony, Soaking Tub, Mini Bar, Wi-Fi',
            'status' => 'Available',
            'total_booked' => 9,
            'image' => 'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id' => 'rm-canyon-301',
            'hotel_id' => 'resort-canyon-cove',
            'room_number' => 'Suite 301',
            'room_name' => 'Executive Oceanview Suite',
            'room_type' => 'Executive Suite',
            'price' => 8900.00,
            'capacity' => '3 Adults',
            'bed_type' => '1 King Bed + Sofa Bed',
            'size_sqm' => 58,
            'amenities' => 'Spacious Living Lounge, Wrap-around Ocean Balcony, Bathtub with View, Coffee Machine, Complimentary Breakfast',
            'status' => 'Available',
            'total_booked' => 4,
            'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80'
        ],
        [
            'id' => 'rm-canyon-401',
            'hotel_id' => 'resort-canyon-cove',
            'room_number' => 'Villa 401',
            'room_name' => 'Family Beachfront Villa Suite',
            'room_type' => 'Family Villa Suite',
            'price' => 14500.00,
            'capacity' => '5 Adults, 2 Children',
            'bed_type' => '2 King Beds + 2 Twin Beds',
            'size_sqm' => 110,
            'amenities' => 'Direct White Sand Beach Walkway, Private Plunge Tub, Kitchenette, Dining Area for 6, Outdoor Veranda',
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
            'rm-canyon-101', 'guest_canyon_01', 'Mark Villanueva', 5,
            'Massive swimming pool right next to the white sand beach! The sunset views in Nasugbu from the balcony were stunning. Great family weekend destination.',
            'rm-canyon-101', 'guest_canyon_01'
        ],
        [
            'rm-canyon-202', 'guest_canyon_02', 'Angela Soriano', 5,
            'Loved the beachfront room. We could walk right out to the shore in seconds. Water sports like banana boat and jetski were easily arranged.',
            'rm-canyon-202', 'guest_canyon_02'
        ]
    ];

    foreach ($reviews as $rev) {
        $revStmt->execute($rev);
    }
    echo "✓ Seeded verified traveler reviews.\n";

    echo "=== Canyon Cove Hotel & Spa seeded into MySQL successfully! ===\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
