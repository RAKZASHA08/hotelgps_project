<?php
// app/Repositories/BookingRepository.php

namespace App\Repositories;

use App\Database\Database;
use PDO;

class BookingRepository {
    private ?PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getBookingsByUser(?string $userId, ?string $sessionId): array {
        if (!$this->db) {
            return $_SESSION['local_bookings'] ?? [];
        }

        try {
            if ($userId && strpos($userId, 'guest_') !== 0) {
                $stmt = $this->db->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
                $stmt->execute([$userId]);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM bookings WHERE session_id = ? ORDER BY created_at DESC");
                $stmt->execute([$sessionId]);
            }
            $rows = $stmt->fetchAll();
            $bookings = [];
            foreach ($rows as $row) {
                $amt = (float)$row['amount'];
                $dp = (float)($row['downpayment_amount'] ?? round($amt * 0.20, 2));
                $bal = (float)($row['balance_due'] ?? max(0, $amt - $dp));
                $bookings[] = [
                    'id'                => $row['booking_id'],
                    'stayId'            => $row['hotel_id'],
                    'stayTitle'         => $row['hotel_title'],
                    'roomId'            => $row['room_id'] ?? '',
                    'roomName'          => $row['room_name'] ?? 'Deluxe Room',
                    'roomNumber'        => $row['room_number'] ?? '',
                    'guestName'         => $row['guest_name'],
                    'guestPhone'        => $row['guest_phone'],
                    'checkInTime'       => $row['check_in_time'],
                    'stayType'          => $row['stay_type'],
                    'nights'            => (int)($row['nights'] ?? 1),
                    'amount'            => $amt,
                    'payment_method'    => $row['payment_method'] ?? 'GCash',
                    'payment_status'    => $row['payment_status'] ?? 'Downpayment Verified',
                    'downpayment_amount'=> $dp,
                    'balance_due'       => $bal,
                    'payment_ref'       => $row['payment_ref'] ?? ('PAY-' . substr($row['booking_id'], 3)),
                    'status'            => $row['status'] ?? 'CONFIRMED',
                    'createdAt'         => $row['created_at']
                ];
            }
            return $bookings;
        } catch (\Exception $e) {
            return $_SESSION['local_bookings'] ?? [];
        }
    }

    public function createBooking(array $data): string {
        $bookingId = 'BK-' . strtoupper(substr(uniqid(), 7, 6));
        $amt = (float)($data['amount'] ?? 0);
        $dp = isset($data['downpayment_amount']) ? (float)$data['downpayment_amount'] : round($amt * 0.20, 2);
        $bal = isset($data['balance_due']) ? (float)$data['balance_due'] : max(0, $amt - $dp);
        $pMethod = $data['payment_method'] ?? 'GCash';
        $pStatus = $data['payment_status'] ?? 'Downpayment Verified';
        $pRef = $data['payment_ref'] ?? ('PAY-' . substr($bookingId, 3));

        if ($this->db) {
            try {
                try {
                    $this->db->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'GCash'");
                    $this->db->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_status VARCHAR(50) DEFAULT 'Downpayment Verified'");
                    $this->db->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS downpayment_amount DECIMAL(10,2) DEFAULT 0.00");
                    $this->db->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS balance_due DECIMAL(10,2) DEFAULT 0.00");
                    $this->db->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_ref VARCHAR(100) DEFAULT ''");
                } catch (\Exception $exCol) {}

                $stmt = $this->db->prepare("
                    INSERT INTO bookings 
                    (booking_id, hotel_id, session_id, user_id, room_id, hotel_title, room_name, room_number, guest_name, guest_email, guest_phone, stay_type, check_in_time, nights, amount, status, payment_method, payment_status, downpayment_amount, balance_due, payment_ref) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'CONFIRMED', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $bookingId,
                    $data['hotel_id'] ?? $data['stay_id'],
                    $data['session_id'],
                    $data['user_id'],
                    $data['room_id'] ?? null,
                    $data['hotel_title'] ?? 'Metro Manila Stay',
                    $data['room_name'] ?? 'Deluxe Room',
                    $data['room_number'] ?? '',
                    $data['guest_name'] ?? 'Guest Traveler',
                    $data['guest_email'] ?? null,
                    $data['guest_phone'] ?? '',
                    $data['stay_type'] ?? '3-Hour Wash Rate',
                    $data['check_in_time'] ?? date('Y-m-d H:i:s'),
                    $data['nights'] ?? 1,
                    $amt,
                    $pMethod,
                    $pStatus,
                    $dp,
                    $bal,
                    $pRef
                ]);
            } catch (\Exception $e) {
                // If db fails, save in session
                $this->saveToSession($bookingId, $data);
            }
        } else {
            $this->saveToSession($bookingId, $data);
        }

        return $bookingId;
    }

    private function saveToSession(string $bookingId, array $data): void {
        if (!isset($_SESSION['local_bookings'])) {
            $_SESSION['local_bookings'] = [];
        }
        $amt = (float)($data['amount'] ?? 0);
        $dp = isset($data['downpayment_amount']) ? (float)$data['downpayment_amount'] : round($amt * 0.20, 2);
        $bal = isset($data['balance_due']) ? (float)$data['balance_due'] : max(0, $amt - $dp);
        $_SESSION['local_bookings'][] = [
            'id'                => $bookingId,
            'stayId'            => $data['hotel_id'] ?? $data['stay_id'],
            'stayTitle'         => $data['hotel_title'] ?? 'Metro Manila Stay',
            'roomId'            => $data['room_id'] ?? '',
            'roomName'          => $data['room_name'] ?? 'Deluxe Room',
            'roomNumber'        => $data['room_number'] ?? '',
            'guestName'         => $data['guest_name'] ?? 'Guest Traveler',
            'guestPhone'        => $data['guest_phone'] ?? '',
            'checkInTime'       => $data['check_in_time'] ?? date('Y-m-d H:i:s'),
            'stayType'          => $data['stay_type'] ?? '3-Hour Wash Rate',
            'nights'            => (int)($data['nights'] ?? 1),
            'amount'            => $amt,
            'payment_method'    => $data['payment_method'] ?? 'GCash',
            'payment_status'    => $data['payment_status'] ?? 'Downpayment Verified',
            'downpayment_amount'=> $dp,
            'balance_due'       => $bal,
            'payment_ref'       => $data['payment_ref'] ?? ('PAY-' . substr($bookingId, 3)),
            'status'            => 'CONFIRMED',
            'createdAt'         => date('Y-m-d H:i:s')
        ];
    }

    public function cancelBooking(string $bookingId): bool {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("DELETE FROM bookings WHERE booking_id = ?");
                return $stmt->execute([$bookingId]);
            } catch (\Exception $e) {}
        }
        if (isset($_SESSION['local_bookings'])) {
            $_SESSION['local_bookings'] = array_values(array_filter($_SESSION['local_bookings'], fn($b) => $b['id'] !== $bookingId));
        }
        return true;
    }
}
