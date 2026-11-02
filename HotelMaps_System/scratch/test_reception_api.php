<?php
require_once __DIR__ . '/../db.php';

function testEndpoint($url, $method = 'GET', $data = null, $files = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/HotelMaps_System/' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($files) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($data ?: [], $files));
        } elseif (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'response' => $res];
}

$testImagePath = __DIR__ . '/test_pano.jpg';
file_put_contents($testImagePath, "\xFF\xD8\xFF\xE0\x00\x10JFIF" . str_repeat("\x00", 100));

$cfile = new CURLFile($testImagePath, 'image/jpeg', 'test_pano.jpg');
$r5 = testEndpoint('api.php?action=reception_upload_360', 'POST', [
    'room_id' => 'rm-pen-101'
], ['panorama_file' => $cfile]);

echo "HTTP " . $r5['code'] . ": " . $r5['response'] . "\n";
