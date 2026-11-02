<?php
// app/Repositories/SavedLocationRepository.php

namespace App\Repositories;

use App\Database\Database;
use PDO;

class SavedLocationRepository {
    private ?PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
        if ($this->db) {
            try {
                $this->db->exec("CREATE TABLE IF NOT EXISTS `saved_locations` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` VARCHAR(64) NOT NULL,
                    `hotel_id` VARCHAR(64) NOT NULL,
                    `saved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY user_hotel (user_id, hotel_id)
                )");
            } catch (\Exception $e) {}
        }
    }

    public function getSavedIds(string $userId): array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT hotel_id FROM saved_locations WHERE user_id = ?");
                $stmt->execute([$userId]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            } catch (\Exception $e) {}
        }
        return $_SESSION['saved_locations'] ?? [];
    }

    public function save(string $userId, string $hotelId): bool {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("INSERT IGNORE INTO saved_locations (user_id, hotel_id) VALUES (?, ?)");
                return $stmt->execute([$userId, $hotelId]);
            } catch (\Exception $e) {}
        }
        if (!isset($_SESSION['saved_locations'])) $_SESSION['saved_locations'] = [];
        if (!in_array($hotelId, $_SESSION['saved_locations'])) {
            $_SESSION['saved_locations'][] = $hotelId;
        }
        return true;
    }

    public function remove(string $userId, string $hotelId): bool {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("DELETE FROM saved_locations WHERE user_id = ? AND hotel_id = ?");
                return $stmt->execute([$userId, $hotelId]);
            } catch (\Exception $e) {}
        }
        if (isset($_SESSION['saved_locations'])) {
            $_SESSION['saved_locations'] = array_values(array_filter($_SESSION['saved_locations'], fn($id) => $id !== $hotelId));
        }
        return true;
    }
}
