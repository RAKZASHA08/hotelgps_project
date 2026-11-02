<?php
// app/Controllers/BookingController.php

namespace App\Controllers;

use App\Repositories\BookingRepository;

class BookingController {
    private BookingRepository $bookingRepo;

    public function __construct() {
        $this->bookingRepo = new BookingRepository();
    }

    public function getBookings(): void {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        $bookings = $this->bookingRepo->getBookingsByUser($userId, $sessionId);
        echo json_encode($bookings);
        exit;
    }

    public function createBooking(): void {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $userId = $_SESSION['user_id'] ?? ('guest_' . substr(session_id(), 0, 8));
        $sessionId = session_id();
        $userEmail = $_SESSION['user_email'] ?? null;

        $data['user_id'] = $userId;
        $data['session_id'] = $sessionId;
        $data['guest_email'] = $userEmail;

        $bookingId = $this->bookingRepo->createBooking($data);

        echo json_encode([
            'status'     => 'success',
            'booking_id' => $bookingId,
            'message'    => 'Booking confirmed successfully!'
        ]);
        exit;
    }

    public function cancelBooking(): void {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $bookingId = $data['id'] ?? '';

        if (!$bookingId) {
            echo json_encode(['status' => 'error', 'message' => 'Missing booking ID']);
            exit;
        }

        $ok = $this->bookingRepo->cancelBooking($bookingId);
        echo json_encode(['status' => $ok ? 'success' : 'error']);
        exit;
    }
}
