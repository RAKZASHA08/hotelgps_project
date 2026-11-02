<?php
require_once __DIR__ . '/../db.php';
$stmt = $pdo->query("SELECT id, title FROM hotels LIMIT 10");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Sample hotel IDs:\n";
foreach ($rows as $r) {
    echo "ID: [{$r['id']}] | Title: {$r['title']}\n";
}
