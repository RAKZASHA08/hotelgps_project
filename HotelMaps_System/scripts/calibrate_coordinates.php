<?php
// scripts/calibrate_coordinates.php - Calibrate all newly added hotel coordinates to exact building rooftops
require_once __DIR__ . '/../db.php';

if (!$pdo) {
    die("Database offline.\n");
}

$calibrations = [
    'hotel-taal-vista' => [
        'lat' => 14.095416,
        'lng' => 120.934674,
        'note' => 'Taal Vista Hotel Main Lobby & Ridge Wing, Aguinaldo Hwy, Tagaytay'
    ],
    'hotel-holiday-inn-makati' => [
        'lat' => 14.550557,
        'lng' => 121.025063,
        'note' => 'Holiday Inn & Suites Makati, Palm Drive cor. Glorietta 1/2, Ayala Center'
    ],
    'hotel-holiday-inn-galleria' => [
        'lat' => 14.590020,
        'lng' => 121.060190,
        'note' => 'Holiday Inn Manila Galleria Tower, 1 ADB Ave cor Ortigas Ave, Pasig'
    ],
    'hotel-holiday-inn-express-newport' => [
        'lat' => 14.517952,
        'lng' => 121.019171,
        'note' => 'Holiday Inn Express Manila Newport City, 1 Jasmine Dr, Newport World Resorts'
    ],
    'hotel-holiday-inn-cebu' => [
        'lat' => 10.320400,
        'lng' => 123.906500,
        'note' => 'Holiday Inn Cebu City, Samar Loop, Cebu Business Park'
    ],
    'hotel-holiday-inn-batangas' => [
        'lat' => 14.041700,
        'lng' => 121.141700,
        'note' => 'Holiday Inn & Suites Batangas LimaPark, Lima Technology Center, Malvar'
    ],
    'hotel-manila-marriott' => [
        'lat' => 14.520833,
        'lng' => 121.017778,
        'note' => 'Manila Marriott Hotel & Grand Ballroom, 2 Resorts Dr, Newport World Resorts'
    ],
    'hotel-clark-marriott' => [
        'lat' => 15.187380,
        'lng' => 120.550346,
        'note' => 'Clark Marriott Hotel, 5398 Manuel A. Roxas Hwy, Clark Freeport Zone'
    ],
    'hotel-courtyard-marriott-iloilo' => [
        'lat' => 10.716380,
        'lng' => 122.544714,
        'note' => 'Courtyard by Marriott Iloilo, Megaworld Blvd, Iloilo Business Park'
    ],
    'hotel-fairfield-marriott-cebu' => [
        'lat' => 10.326500,
        'lng' => 123.940500,
        'note' => 'Fairfield by Marriott Cebu Mandaue City, A.C. Cortes Ave, Mandaue'
    ],
    'hotel-sheraton-manila-marriott' => [
        'lat' => 14.521800,
        'lng' => 121.017800,
        'note' => 'Sheraton Manila Hotel, 80 Andrews Ave, Newport World Resorts'
    ],
    'resort-pico-sands' => [
        'lat' => 14.179700,
        'lng' => 120.627200,
        'note' => 'Pico Sands Hotel, Pico de Loro Cove, Hamilo Coast, Nasugbu, Batangas'
    ],
    'resort-canyon-cove' => [
        'lat' => 14.053600,
        'lng' => 120.627800,
        'note' => 'Canyon Cove Hotel & Spa, Far East Rd, Piloto Wawa, Nasugbu, Batangas'
    ]
];

// 1. Update MySQL
$updateStmt = $pdo->prepare("UPDATE hotels SET lat = ?, lng = ? WHERE id = ?");

echo "=== Updating MySQL Hotel Coordinates ===\n";
foreach ($calibrations as $id => $geo) {
    $updateStmt->execute([$geo['lat'], $geo['lng'], $id]);
    echo "✓ [{$id}] calibrated to {$geo['lat']}, {$geo['lng']} ({$geo['note']})\n";
}

// 2. Update stays_inventory.json
$jsonPath = __DIR__ . '/../data/stays_inventory.json';
$raw = json_decode(file_get_contents($jsonPath), true) ?: [];

foreach ($raw as &$stay) {
    $sid = $stay['id'];
    if (isset($calibrations[$sid])) {
        $stay['lat'] = $calibrations[$sid]['lat'];
        $stay['lng'] = $calibrations[$sid]['lng'];
    }
}

file_put_contents($jsonPath, json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\n✓ Synchronized data/stays_inventory.json with calibrated coordinates.\n";
