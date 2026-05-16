<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Str;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $filePath = storage_path('app/firebase-auth.json');

        if (!file_exists($filePath)) {
            Log::error("Setup Firebase Gagal: File JSON tidak ditemukan di " . $filePath);
            throw new \Exception("File kunci Firebase tidak ditemukan di: " . $filePath);
        }

        $factory = (new Factory)->withServiceAccount($filePath);
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send notification to user via FCM and save to database.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [])
    {
        try {
            $user->notifications()->create([
                'id' => Str::uuid(),
                'type' => 'BookingStatusChanged',
                'data' => array_merge([
                    'title' => $title,
                    'body' => $body,
                ], $data),
            ]);

            $tokens = DeviceToken::where('user_id', $user->id)->pluck('fcm_token')->toArray();
            Log::info("Mencari Token: Menemukan " . count($tokens) . " perangkat aktif untuk User ID: " . $user->id);

            if (empty($tokens)) {
                Log::info("Pengiriman Dibatalkan: Tidak ada FCM token terdaftar untuk User ID: {$user->id}");
                return;
            }

            $notification = FirebaseNotification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $report = $this->messaging->sendMulticast($message, $tokens);
            
            Log::info("FCM Sukses Terkirim ke User ID: {$user->id}. Berhasil: {$report->successes()->count()}, Gagal: {$report->failures()->count()}");

        } catch (\Exception $e) {
            Log::error("Gagal mengeksekusi sendToUser: " . $e->getMessage());
        }
    }
}