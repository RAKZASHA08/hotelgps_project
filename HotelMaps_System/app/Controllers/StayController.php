<?php
// app/Controllers/StayController.php

namespace App\Controllers;

use App\Repositories\StayRepository;

class StayController {
    private StayRepository $stayRepo;

    public function __construct() {
        $this->stayRepo = new StayRepository();
    }

    public function getStays(): void {
        header('Content-Type: application/json');
        $stays = $this->stayRepo->getAllStays();
        echo json_encode($stays);
        exit;
    }

    public function incrementViews(): void {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = $data['id'] ?? '';
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
            exit;
        }
        $views = $this->stayRepo->incrementViews($id);
        echo json_encode(['status' => 'success', 'views_count' => $views]);
        exit;
    }

    public function getStats(): void {
        header('Content-Type: application/json');
        $all = $this->stayRepo->getAllStays();
        $totalViews = array_sum(array_column($all, 'viewsCount'));
        $totalBookings = array_sum(array_column($all, 'totalBooked'));
        $hotelCount = count($all);
        $prices = array_column($all, 'price');
        $avgPrice = !empty($prices) ? round(array_sum($prices) / count($prices), 2) : 0;

        echo json_encode([
            'total_views' => $totalViews,
            'total_bookings' => $totalBookings,
            'hotel_count' => $hotelCount,
            'average_price' => $avgPrice
        ]);
        exit;
    }
}
