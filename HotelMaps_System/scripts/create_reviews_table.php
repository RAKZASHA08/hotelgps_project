<?php
require_once __DIR__ . '/../app/bootstrap.php';

if (!$pdo) {
    die("Database connection failed.");
}

$sql = "
CREATE TABLE IF NOT EXISTS `room_reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` VARCHAR(64) NOT NULL,
  `user_id` VARCHAR(64) NOT NULL,
  `user_name` VARCHAR(120) NOT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_room` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $pdo->exec($sql);
    echo "Successfully created room_reviews table.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
