<?php
// add_new_branches.php
require_once __DIR__ . '/../app/bootstrap.php';

try {
    $hotels = [

        // METRO MANILA AIRBNBS
        ['airbnb-gramercy', 'Gramercy Skyline Studio', 'Gramercy Residences', 'airbnb', 'High-Rise Condo Staycation', 'Kalayaan Avenue, Century City, Makati CBD', 'Makati CBD', 4.78, 3500.00, 14.5658, 121.0287, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80', '71st Floor Bar, Infinity Pool, Century Mall Access', 'Century City Hub'],
        ['airbnb-venice', 'The Venice Romantic Suite', 'Venice Luxury Residences', 'airbnb', 'European-Themed Staycation', 'Venezia Drive, McKinley Hill, Taguig', 'BGC Taguig', 4.70, 4200.00, 14.5332, 121.0543, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80', 'Grand Canal View, Gondola Rides, Fast Wi-Fi', 'McKinley Hill Branch'],
        ['airbnb-azure', 'Azure Urban Beach Resort Stay', 'Azure Residences', 'airbnb', 'Man-Made Beach Staycation', 'SLEX West Service Road, Bicutan, Parañaque', 'Entertainment City', 4.65, 2800.00, 14.4881, 121.0435, 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&q=80', 'Wave Pool, White Sand Beach, Paris Beach Club', 'Bicutan Branch']
    ];

    $stmtHotel = $pdo->prepare("INSERT IGNORE INTO hotels (id, title, brand, category, category_display, address, city, rating, price_from, lat, lng, image, tags, branch_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmtRoom = $pdo->prepare("INSERT IGNORE INTO hotel_rooms (id, hotel_id, room_number, room_name, room_type, price, capacity, bed_type, size_sqm, amenities, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available', ?)");

    $images = [
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80',
        'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?auto=format&fit=crop&w=600&q=80',
        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80',
        'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=600&q=80',
        'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=600&q=80'
    ];

    foreach ($hotels as $h) {
        $stmtHotel->execute($h);

        // Add 3 rooms for each new hotel
        for ($i = 1; $i <= 3; $i++) {
            $roomId = 'rm-' . $h[0] . '-' . $i;
            $rNum = 'Room ' . $i . '0' . rand(1, 9);
            $rName = ($i == 1) ? 'Standard Unit' : (($i == 2) ? 'Deluxe Suite' : 'Premium / Executive Suite');
            $rType = ($i == 1) ? 'Standard' : (($i == 2) ? 'Deluxe' : 'Premium');
            $price = $h[8] * (1 + ($i * 0.3)); // increment price
            $cap = '2 Adults';
            $bed = ($i == 1) ? '1 Queen Bed' : '1 King Bed';
            $sqm = 30 + ($i * 15);
            $amenities = 'Fast Wi-Fi, Smart TV, Air Conditioning, Hot & Cold Shower, Scenic Views';
            $img = $images[array_rand($images)];

            $stmtRoom->execute([$roomId, $h[0], $rNum, $rName, $rType, $price, $cap, $bed, $sqm, $amenities, $img]);
        }
    }

    echo "New regions and hotel branches seeded successfully!";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
