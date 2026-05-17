<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\DB;

class BookingObserver
{
    const STATUS_ORDER = [
        'waiting'            => 0,
        'confirmed'          => 1,
        'processing'         => 2,
        'on_delivery'        => 3,
        'pending_completion' => 4,
        'completed'          => 5,
        'cancelled'          => 99,
    ];

    const STOCK_DEDUCTED_STATUSES = ['confirmed', 'processing', 'on_delivery', 'completed'];

    protected $notificationService;

    public function __construct(FirebaseNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function saving(Booking $booking): void
    {
        if ($booking->isDirty('status')) {
            $oldStatus = $booking->getOriginal('status');
            $newStatus = $booking->status;

            if ($oldStatus === null) {
                return;
            }

            $this->validateStatusTransition($oldStatus, $newStatus, $booking->booking_number);
        }
    }

    public function saved(Booking $booking): void
    {
        \Log::info("Booking Observer triggered for booking #" . $booking->booking_number);

        if ($booking->wasChanged('status')) {
            $oldStatus = $booking->getOriginal('status');
            $newStatus = $booking->status;

            \Log::info("Status changed from '{$oldStatus}' to '{$newStatus}'");

            if ($newStatus === 'confirmed' && $oldStatus === 'waiting') {
                $this->deductStock($booking);
            }

            if ($newStatus === 'cancelled' && in_array($oldStatus, self::STOCK_DEDUCTED_STATUSES)) {
                $this->restoreStock($booking);
            }

            $title = "Update Status Booking";
            $body  = $this->getNotificationBody($newStatus, $booking->booking_number);

            $data = [
                'screen'         => 'order_detail',
                'booking_id'     => (string) $booking->id,
                'booking_number' => $booking->booking_number,
                'status'         => $newStatus,
            ];

            if ($booking->user) {
                $this->notificationService->sendToUser($booking->user, $title, $body, $data);
            }
        }
    }

    protected function validateStatusTransition(string $oldStatus, string $newStatus, string $bookingNumber): void
    {
        if ($oldStatus === 'cancelled') {
            throw new \Exception("Booking #{$bookingNumber} sudah dibatalkan dan tidak dapat diubah statusnya.");
        }

        if ($newStatus === 'cancelled') {
            return;
        }

        $oldOrder = self::STATUS_ORDER[$oldStatus] ?? -1;
        $newOrder = self::STATUS_ORDER[$newStatus] ?? -1;

        $labels = [
            'waiting'            => 'Menunggu',
            'confirmed'          => 'Dikonfirmasi',
            'processing'         => 'Diproses',
            'on_delivery'        => 'Dalam Pengiriman',
            'pending_completion' => 'Menunggu Persetujuan',
            'completed'          => 'Selesai',
        ];

        if ($newOrder <= $oldOrder) {
            $oldLabel = $labels[$oldStatus] ?? $oldStatus;
            $newLabel = $labels[$newStatus] ?? $newStatus;
            throw new \Exception(
                "Status tidak dapat mundur: Booking #{$bookingNumber} sudah berstatus '{$oldLabel}', tidak bisa dikembalikan ke '{$newLabel}'."
            );
        }

        $allowedNextStatus = array_search($oldOrder + 1, self::STATUS_ORDER);
        if ($allowedNextStatus !== false && $newStatus !== $allowedNextStatus) {
            $oldLabel  = $labels[$oldStatus] ?? $oldStatus;
            $nextLabel = $labels[$allowedNextStatus] ?? $allowedNextStatus;
            throw new \Exception(
                "Transisi status tidak valid. Setelah '{$oldLabel}', status berikutnya haruslah '{$nextLabel}'."
            );
        }
    }

    protected function deductStock(Booking $booking): void
    {
        $booking->load('bookingDetails.product');

        DB::transaction(function () use ($booking) {
            foreach ($booking->bookingDetails as $detail) {
                $product = $detail->product;
                if ($product) {
                    $product->decrement('stock_qty', $detail->qty);
                    \Log::info("Stock deducted: Product #{$product->id} ({$product->name}) -{$detail->qty}");
                }
            }
        });
    }

    protected function restoreStock(Booking $booking): void
    {
        $booking->load('bookingDetails.product');

        DB::transaction(function () use ($booking) {
            foreach ($booking->bookingDetails as $detail) {
                $product = $detail->product;
                if ($product) {
                    $product->increment('stock_qty', $detail->qty);
                    \Log::info("Stock restored: Product #{$product->id} ({$product->name}) +{$detail->qty}");
                }
            }
        });
    }

    protected function getNotificationBody(string $status, string $bookingNumber): string
    {
        return match ($status) {
            'confirmed'   => "Booking #{$bookingNumber} Anda telah dikonfirmasi.",
            'processing'  => "Booking #{$bookingNumber} Anda sedang dalam proses.",
            'on_delivery' => "Booking #{$bookingNumber} Anda sedang dalam pengiriman.",
            'completed'   => "Booking #{$bookingNumber} Anda telah selesai. Terima kasih!",
            'cancelled'   => "Booking #{$bookingNumber} Anda telah dibatalkan.",
            default       => "Status booking #{$bookingNumber} Anda telah diperbarui menjadi {$status}.",
        };
    }
}
