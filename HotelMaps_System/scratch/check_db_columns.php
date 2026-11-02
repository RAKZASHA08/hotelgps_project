<?php
require_once __DIR__ . '/../db.php';
if (!$pdo) {
    echo "NO PDO CONNECTION\n";
    exit;
}
echo "--- BOOKINGS COLUMNS ---\n";
$bCols = $pdo->query("SHOW COLUMNS FROM bookings")->fetchAll(PDO::FETCH_ASSOC);
foreach ($bCols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n--- HOTEL_ROOMS COLUMNS ---\n";
$rCols = $pdo->query("SHOW COLUMNS FROM hotel_rooms")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rCols as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
