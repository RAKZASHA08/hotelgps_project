<?php
// app/Models/Booking.php

namespace App\Models;

class Booking {
    public string $id;
    public string $hotelId;
    public string $hotelTitle;
    public ?string $roomId;
    public string $roomName;
    public string $roomNumber;
    public string $guestName;
    public string $guestPhone;
    public ?string $guestEmail;
    public string $stayType;
    public string $checkInTime;
    public int $nights;
    public float $amount;
    public string $status;
    public string $createdAt;

    public function __construct(array $data) {
        $this->id          = $data['id'] ?? $data['booking_id'] ?? '';
        $this->hotelId     = $data['hotel_id'] ?? $data['stay_id'] ?? '';
        $this->hotelTitle  = $data['hotel_title'] ?? $data['stayTitle'] ?? '';
        $this->roomId      = $data['room_id'] ?? $data['roomId'] ?? null;
        $this->roomName    = $data['room_name'] ?? $data['roomName'] ?? 'Deluxe Room';
        $this->roomNumber  = $data['room_number'] ?? $data['roomNumber'] ?? '';
        $this->guestName   = $data['guest_name'] ?? $data['guestName'] ?? '';
        $this->guestPhone  = $data['guest_phone'] ?? $data['guestPhone'] ?? '';
        $this->guestEmail  = $data['guest_email'] ?? $data['guestEmail'] ?? null;
        $this->stayType    = $data['stay_type'] ?? $data['stayType'] ?? '3-Hour Wash Rate';
        $this->checkInTime = $data['check_in_time'] ?? $data['checkInTime'] ?? date('Y-m-d H:i:s');
        $this->nights      = (int)($data['nights'] ?? 1);
        $this->amount      = (float)($data['amount'] ?? 0);
        $this->status      = $data['status'] ?? 'CONFIRMED';
        $this->createdAt   = $data['created_at'] ?? $data['createdAt'] ?? date('Y-m-d H:i:s');
    }

    public function toArray(): array {
        return [
            'id'          => $this->id,
            'stayId'      => $this->hotelId,
            'stayTitle'   => $this->hotelTitle,
            'roomId'      => $this->roomId,
            'roomName'    => $this->roomName,
            'roomNumber'  => $this->roomNumber,
            'guestName'   => $this->guestName,
            'guestPhone'  => $this->guestPhone,
            'stayType'    => $this->stayType,
            'checkInTime' => $this->checkInTime,
            'nights'      => $this->nights,
            'amount'      => $this->amount,
            'status'      => $this->status,
            'createdAt'   => $this->createdAt
        ];
    }
}
