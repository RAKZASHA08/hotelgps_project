<?php
require_once __DIR__ . '/../db.php';
$stmt = $pdo->query("SELECT id, hotel_id, room_name, image FROM hotel_rooms WHERE hotel_id = 'hotel-peninsula'");
foreach ($stmt->fetchAll() as $r) {
    echo "Room ID: {$r['id']} | Name: {$r['room_name']}\n";
}
// Check columns of hotel_rooms
$cols = $pdo->query("DESCRIBE hotel_rooms")->fetchAll();
echo "Columns:\n";
foreach ($cols as $c) {
    echo " - {$c['Field']}\n";
}
