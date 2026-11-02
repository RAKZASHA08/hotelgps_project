<?php
// scripts/migrate_booking_limit.php
require_once __DIR__ . '/../db.php';

if (!$pdo) {
    die("Database connection offline.\n");
}

echo "=== MIGRATING HOTEL_ROOMS BOOKING_LIMIT & FIXING OFFICIAL COUNTS ===\n";

try {
    // 1. Check if booking_limit column exists in hotel_rooms
    $colCheck = $pdo->query("SHOW COLUMNS FROM hotel_rooms LIKE 'booking_limit'");
    if ($colCheck->rowCount() === 0) {
        echo "Adding 'booking_limit' column to hotel_rooms...\n";
        $pdo->exec("ALTER TABLE hotel_rooms ADD COLUMN booking_limit INT(11) NOT NULL DEFAULT 10 AFTER total_booked");
        echo "Column 'booking_limit' successfully added.\n";
    } else {
        echo "'booking_limit' column already exists.\n";
    }

    // 2. Assign tailored booking limits per room kind/type
    echo "Updating booking_limit per room type...\n";
    
    // Presidential / Penthouse / Luxury Villa / Diplomatic / Historic Suite
    $pdo->exec("UPDATE hotel_rooms SET booking_limit = 2 WHERE 
        LOWER(room_type) LIKE '%presidential%' 
        OR LOWER(room_type) LIKE '%penthouse%' 
        OR LOWER(room_type) LIKE '%villa%' 
        OR LOWER(room_type) LIKE '%diplomatic%' 
        OR LOWER(room_type) LIKE '%historic%'
        OR LOWER(room_name) LIKE '%presidential%'
        OR LOWER(room_name) LIKE '%penthouse%'
        OR LOWER(room_name) LIKE '%villa%'
    ");

    // Suites / Executive / Regency / Club / Family / Panorama / Bayfront
    $pdo->exec("UPDATE hotel_rooms SET booking_limit = 4 WHERE 
        (LOWER(room_type) LIKE '%suite%' 
        OR LOWER(room_type) LIKE '%executive%' 
        OR LOWER(room_type) LIKE '%club%'
        OR LOWER(room_type) LIKE '%family%'
        OR LOWER(room_name) LIKE '%suite%')
        AND booking_limit != 2
    ");

    // Deluxe / Premier / Superior / Resort
    $pdo->exec("UPDATE hotel_rooms SET booking_limit = 8 WHERE 
        (LOWER(room_type) LIKE '%deluxe%' 
        OR LOWER(room_type) LIKE '%premier%' 
        OR LOWER(room_type) LIKE '%superior%'
        OR LOWER(room_type) LIKE '%resort%'
        OR LOWER(room_name) LIKE '%deluxe%'
        OR LOWER(room_name) LIKE '%premier%')
        AND booking_limit NOT IN (2, 4)
    ");

    // Short Stay / Motels (Astrotel, Eurotel, Victoria Court, Sogo)
    $pdo->exec("UPDATE hotel_rooms SET booking_limit = 20 WHERE 
        LOWER(room_type) LIKE '%short%' 
        OR LOWER(duration) LIKE '%hour%' 
        OR rate_type = 'hourly'
        OR hotel_id LIKE 'motel-%'
    ");

    // Standard / Economy / default to 10
    $pdo->exec("UPDATE hotel_rooms SET booking_limit = 10 WHERE booking_limit NOT IN (2, 4, 8, 20)");

    echo "Booking limits updated.\n";

    // 3. Fix official count of total_booked in hotel_rooms and hotels
    echo "Fixing official confirmed booking counts in hotel_rooms...\n";
    // First reset to 0
    $pdo->exec("UPDATE hotel_rooms SET total_booked = 0");
    // Then set to exact COUNT of CONFIRMED bookings in bookings table
    $pdo->exec("
        UPDATE hotel_rooms r
        INNER JOIN (
            SELECT room_id, COUNT(*) as cnt 
            FROM bookings 
            WHERE status = 'CONFIRMED' AND room_id IS NOT NULL AND room_id != ''
            GROUP BY room_id
        ) b ON r.id = b.room_id
        SET r.total_booked = b.cnt
    ");

    echo "Fixing official confirmed booking counts in hotels...\n";
    $pdo->exec("UPDATE hotels SET total_booked = 0");
    $pdo->exec("
        UPDATE hotels h
        INNER JOIN (
            SELECT hotel_id, COUNT(*) as cnt 
            FROM bookings 
            WHERE status = 'CONFIRMED' AND hotel_id IS NOT NULL AND hotel_id != ''
            GROUP BY hotel_id
        ) b ON h.id = b.hotel_id
        SET h.total_booked = b.cnt
    ");

    // 4. Update room status if total_booked >= booking_limit
    $pdo->exec("UPDATE hotel_rooms SET status = 'Sold Out' WHERE total_booked >= booking_limit AND status = 'Available'");
    $pdo->exec("UPDATE hotel_rooms SET status = 'Available' WHERE total_booked < booking_limit AND status = 'Sold Out'");

    echo "=== SUMMARY OF ROOM LIMITS & COUNTS ===\n";
    $res = $pdo->query("SELECT booking_limit, COUNT(*) as room_count, SUM(total_booked) as total_booked_in_category FROM hotel_rooms GROUP BY booking_limit ORDER BY booking_limit ASC");
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        echo "Limit: {$row['booking_limit']} | Rooms: {$row['room_count']} | Current Booked: {$row['total_booked_in_category']}\n";
    }

    echo "\n=== SAMPLE ROOMS WITH OFFICIAL COUNTS & LIMITS ===\n";
    $sample = $pdo->query("SELECT id, hotel_id, room_name, room_type, total_booked, booking_limit, status FROM hotel_rooms ORDER BY total_booked DESC, booking_limit ASC LIMIT 10");
    while ($row = $sample->fetch(PDO::FETCH_ASSOC)) {
        echo "[{$row['id']}] {$row['room_name']} ({$row['room_type']}): Booked: {$row['total_booked']}/{$row['booking_limit']} | Status: {$row['status']}\n";
    }

    echo "\nMigration completed successfully!\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
