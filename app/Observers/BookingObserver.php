<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\FirebaseNotificationService;

class BookingObserver
{
    protected $notificationService;

    public function __construct(FirebaseNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function saved(Booking $booking): void
    {
        \Log::info("Booking Observer triggered for booking #" . $booking->booking_number);
        if ($booking->wasChanged('status')) {
            \Log::info("Status changed to: " . $booking->status);
            $status = $booking->status;
            $title = "Update Status Booking";
            $body = $this->getNotificationBody($status, $booking->booking_number);

            $data = [
                'screen' => 'order_detail',
                'booking_id' => (string) $booking->id,
                'booking_number' => $booking->booking_number,
                'status' => $status,
            ];

            if ($booking->user) {
                $this->notificationService->sendToUser($booking->user, $title, $body, $data);
            }
        }
    }

    protected function getNotificationBody(string $status, string $bookingNumber): string
    {
        return match ($status) {
            'confirmed' => "Booking #{$bookingNumber} Anda telah dikonfirmasi.",
            'processing' => "Booking #{$bookingNumber} Anda sedang dalam proses.",
            'completed' => "Booking #{$bookingNumber} Anda telah selesai. Terima kasih!",
            'cancelled' => "Booking #{$bookingNumber} Anda telah dibatalkan.",
            default => "Status booking #{$bookingNumber} Anda telah diperbarui menjadi {$status}.",
        };
    }
}
