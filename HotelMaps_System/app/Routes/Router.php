<?php
// app/Routes/Router.php - Application Request Router

namespace App\Routes;

use App\Controllers\StayController;
use App\Controllers\BookingController;
use App\Repositories\SavedLocationRepository;

class Router {
    public static function dispatch(): void {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'get_stays':
            case 'get_hotels':
                (new StayController())->getStays();
                break;

            case 'get_stats':
                (new StayController())->getStats();
                break;

            case 'increment_views':
                (new StayController())->incrementViews();
                break;

            case 'get_bookings':
                (new BookingController())->getBookings();
                break;

            case 'create_booking':
                (new BookingController())->createBooking();
                break;

            case 'cancel_booking':
                (new BookingController())->cancelBooking();
                break;

            case 'get_saved_destinations':
                header('Content-Type: application/json');
                $userId = $_SESSION['user_id'] ?? ('guest_' . substr(session_id(), 0, 8));
                $saved = (new SavedLocationRepository())->getSavedIds($userId);
                echo json_encode($saved);
                exit;

            case 'save_destination':
                header('Content-Type: application/json');
                $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                $userId = $_SESSION['user_id'] ?? ('guest_' . substr(session_id(), 0, 8));
                $hotelId = $data['hotel_id'] ?? '';
                if ($hotelId) {
                    (new SavedLocationRepository())->save($userId, $hotelId);
                    echo json_encode(['status' => 'success', 'message' => 'Saved to favorites']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Missing hotel_id']);
                }
                exit;

            case 'remove_destination':
                header('Content-Type: application/json');
                $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
                $userId = $_SESSION['user_id'] ?? ('guest_' . substr(session_id(), 0, 8));
                $hotelId = $data['hotel_id'] ?? '';
                if ($hotelId) {
                    (new SavedLocationRepository())->remove($userId, $hotelId);
                    echo json_encode(['status' => 'success', 'message' => 'Removed from favorites']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Missing hotel_id']);
                }
                exit;

            default:
                // Let api.php continue with fallback or other legacy handlers (e.g. reviews, verification)
                break;
        }
    }
}
