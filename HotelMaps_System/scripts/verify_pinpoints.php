<?php
// scripts/verify_pinpoints.php
require_once __DIR__ . '/../db.php';

$ids = [
    'hotel-taal-vista',
    'hotel-holiday-inn-makati',
    'hotel-holiday-inn-galleria',
    'hotel-holiday-inn-express-newport',
    'hotel-holiday-inn-cebu',
    'hotel-holiday-inn-batangas',
    'hotel-manila-marriott',
    'hotel-clark-marriott',
    'hotel-courtyard-marriott-iloilo',
    'hotel-fairfield-marriott-cebu',
    'hotel-sheraton-manila-marriott',
    'resort-pico-sands',
    'resort-canyon-cove'
];

$inClause = "'" . implode("','", $ids) . "'";
$stmt = $pdo->query("SELECT id, title, city, address, lat, lng FROM hotels WHERE id IN ($inClause) ORDER BY id");
$hotels = $stmt->fetchAll();

echo str_pad("HOTEL ID", 34) . str_pad("CITY", 20) . str_pad("LATITUDE", 12) . str_pad("LONGITUDE", 12) . "TITLE\n";
echo str_repeat("-", 105) . "\n";
foreach ($hotels as $h) {
    echo str_pad($h['id'], 34) . str_pad($h['city'], 20) . str_pad($h['lat'], 12) . str_pad($h['lng'], 12) . $h['title'] . "\n";
}
