<?php
// app/Repositories/StayRepository.php

namespace App\Repositories;

use App\Database\Database;
use PDO;

class StayRepository {
    private ?PDO $db;
    private string $inventoryJsonPath;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->inventoryJsonPath = dirname(__DIR__, 2) . '/data/stays_inventory.json';
    }

    /**
     * Get all stays with room details, hourly tiers, commuter info, and live booking counts
     */
    public function getAllStays(): array {
        if ($this->db) {
            try {
                // Fetch all hotels
                $stmt = $this->db->query("SELECT * FROM hotels ORDER BY rating DESC, price_from ASC");
                $hotels = $stmt->fetchAll();

                // Live room bookings (CONFIRMED reservations only)
                $rbStmt = $this->db->query("SELECT room_id, COUNT(*) as r_bookings FROM bookings WHERE status = 'CONFIRMED' AND room_id IS NOT NULL AND room_id != '' GROUP BY room_id");
                $liveRoomBookings = [];
                while ($rb = $rbStmt->fetch()) {
                    $liveRoomBookings[$rb['room_id']] = (int)$rb['r_bookings'];
                }

                // Reviews
                $reviewsMap = [];
                try {
                    $revStmt = $this->db->query("SELECT room_id, COUNT(*) as review_count, AVG(rating) as avg_rating FROM room_reviews GROUP BY room_id");
                    while ($rRow = $revStmt->fetch()) {
                        $reviewsMap[$rRow['room_id']] = [
                            'count' => (int)$rRow['review_count'],
                            'avg' => round((float)$rRow['avg_rating'], 1)
                        ];
                    }
                } catch (\Exception $e) {}

                // Rooms
                $roomsStmt = $this->db->query("SELECT * FROM hotel_rooms ORDER BY price ASC");
                $allRooms = $roomsStmt->fetchAll();
                $roomsByHotel = [];
                foreach ($allRooms as $room) {
                    $hid = $room['hotel_id'];
                    if (!isset($roomsByHotel[$hid])) {
                        $roomsByHotel[$hid] = [];
                    }
                    $rNameLower = strtolower($room['room_name'] ?? '');
                    if (str_contains($rNameLower, 'suite') || str_contains($rNameLower, 'executive') || str_contains($rNameLower, 'luxury') || str_contains($rNameLower, 'presidential')) {
                        $room360Img = 'img/luxury_suite.jpg';
                    } elseif (str_contains($rNameLower, 'condo') || str_contains($rNameLower, 'studio') || str_contains($rNameLower, 'loft') || str_contains($rNameLower, 'balcony')) {
                        $room360Img = 'img/skyline_condo.jpg';
                    } elseif (str_contains($rNameLower, 'transit') || str_contains($rNameLower, 'capsule') || str_contains($rNameLower, 'cabin') || str_contains($rNameLower, 'wash')) {
                        $room360Img = 'img/transit_lounge.jpg';
                    } else {
                        $room360Img = 'img/pano_bedroom.jpg';
                    }

                    $officialBooked = $liveRoomBookings[$room['id']] ?? 0;
                    $bookingLimit = (int)($room['booking_limit'] ?? 10);
                    $roomStatus = $room['status'] ?? 'Available';
                    if ($officialBooked >= $bookingLimit && $roomStatus === 'Available') {
                        $roomStatus = 'Sold Out';
                    }

                    $roomsByHotel[$hid][] = [
                        'id' => $room['id'],
                        'room_number' => $room['room_number'] ?? '',
                        'room_name' => $room['room_name'],
                        'room_type' => $room['room_type'],
                        'price' => (float)$room['price'],
                        'rate_type' => $room['rate_type'] ?? 'hourly',
                        'duration' => $room['duration'] ?? '3 Hours',
                        'capacity' => $room['capacity'],
                        'bed_type' => $room['bed_type'],
                        'size_sqm' => (int)$room['size_sqm'],
                        'amenities' => $room['amenities'],
                        'status' => $roomStatus,
                        'total_booked' => $officialBooked,
                        'booking_limit' => $bookingLimit,
                        'available_slots' => max(0, $bookingLimit - $officialBooked),
                        'review_count' => $reviewsMap[$room['id']]['count'] ?? 0,
                        'avg_rating' => $reviewsMap[$room['id']]['avg'] ?? 0.0,
                        'image' => $room['image'],
                        'image_360' => $room360Img
                    ];
                }

                // Live confirmed bookings count
                $bCountStmt = $this->db->query("SELECT hotel_id, COUNT(*) as active_bookings FROM bookings WHERE status = 'CONFIRMED' GROUP BY hotel_id");
                $liveBookingCounts = [];
                while ($bRow = $bCountStmt->fetch()) {
                    $liveBookingCounts[$bRow['hotel_id']] = (int)$bRow['active_bookings'];
                }

                $result = [];
                foreach ($hotels as $h) {
                    $hid = $h['id'];
                    $hotelRooms = $roomsByHotel[$hid] ?? [];

                    $roomPrices = array_column($hotelRooms, 'price');
                    $avgRate = !empty($roomPrices) ? round(array_sum($roomPrices) / count($roomPrices), 2) : (float)$h['price_from'];

                    $availableRoomsCount = count(array_filter($hotelRooms, function($rm) {
                        return strtolower($rm['status']) === 'available';
                    }));

                    $totalBooked = (int)($liveBookingCounts[$hid] ?? 0);
                    $tags = !empty($h['tags']) ? array_map('trim', explode(',', $h['tags'])) : ['Walk-in Ready', 'Commuter Friendly', 'Aircon'];

                    // Hourly pricing rates map or parsed JSON
                    $hourlyRates = !empty($h['hourly_rates']) ? json_decode($h['hourly_rates'], true) : null;
                    if (!$hourlyRates) {
                        $base = (float)$h['price_from'];
                        $hourlyRates = [
                            '2hr'  => round($base * 0.8),
                            '3hr'  => round($base),
                            '6hr'  => round($base * 1.6),
                            '12hr' => round($base * 2.3),
                            '24hr' => round($base * 3.2),
                        ];
                    }

                    $result[] = [
                        'id' => $h['id'],
                        'title' => $h['title'],
                        'branch_name' => $h['branch_name'] ?? 'Main Branch',
                        'brand' => $h['brand'] ?? 'Motel / Hotel',
                        'category' => $h['category'],
                        'categoryDisplay' => $h['category_display'] ?? ucfirst($h['category']),
                        'address' => $h['address'],
                        'city' => $h['city'] ?? 'Metro Manila',
                        'rating' => (float)$h['rating'],
                        'ratingCount' => (int)($h['rating_count'] ?? 0),
                        'viewsCount' => (int)($h['views_count'] ?? 0),
                        'totalBooked' => $totalBooked,
                        'price' => (float)$h['price_from'],
                        'price_from' => (float)$h['price_from'],
                        'averageRate' => $avgRate,
                        'availableRoomsCount' => $availableRoomsCount,
                        'durationLabel' => $h['duration_label'] ?? '3-Hour Wash Rate',
                        'rateTypeLabel' => ($h['category'] === 'motel' || $h['category'] === 'sogo') ? 'HOURLY WALK-IN RATE' : 'OVERNIGHT RATE',
                        'lat' => (float)$h['lat'],
                        'lng' => (float)$h['lng'],
                        'image' => $h['image'],
                        'tags' => $tags,
                        'statusText' => $h['status_text'] ?? 'Rooms Available Now',
                        'statusBadgeClass' => $h['status_badge'] ?? 'status-walkin',
                        'instant' => true,
                        // Commuter specific fields
                        'nearest_terminal' => $h['nearest_terminal'] ?? 'Nearest Transit Hub',
                        'transit_type' => $h['transit_type'] ?? 'MRT / Busway',
                        'walk_time' => $h['walk_time'] ?? '3-5 mins walk',
                        'hourly_rates' => $hourlyRates,
                        'rooms' => $hotelRooms,
                        'showroom' => \App\Models\Stay::buildDefaultShowroom($h['category'] ?? 'motel', $h['title'] ?? '')
                    ];
                }

                if (!empty($result)) {
                    return $result;
                }
            } catch (\Exception $e) {
                // fall through to JSON
            }
        }

        // Fallback to JSON file
        return $this->getFallbackInventory();
    }

    public function getFallbackInventory(): array {
        if (file_exists($this->inventoryJsonPath)) {
            $data = json_decode(file_get_contents($this->inventoryJsonPath), true);
            if (is_array($data)) return $data;
        }
        return [];
    }

    public function findById(string $id): ?array {
        $all = $this->getAllStays();
        foreach ($all as $stay) {
            if ($stay['id'] === $id) return $stay;
        }
        return null;
    }

    public function incrementViews(string $id): int {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("UPDATE hotels SET views_count = views_count + 1 WHERE id = ?");
                $stmt->execute([$id]);
                $stmt2 = $this->db->prepare("SELECT views_count FROM hotels WHERE id = ?");
                $stmt2->execute([$id]);
                return (int)$stmt2->fetchColumn();
            } catch (\Exception $e) {}
        }
        return 0;
    }
}
