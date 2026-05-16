<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Str;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
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

            if (empty($tokens)) {
                Log::info("No FCM tokens found for user ID: {$user->id}");
                return;
            }

            $notification = FirebaseNotification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $report = $this->messaging->sendMulticast($message, $tokens);
            
            Log::info("FCM Notification sent to user ID: {$user->id}. Success: {$report->successes()->count()}, Failures: {$report->failures()->count()}");

        } catch (\Exception $e) {
            Log::error("Error sending notification: " . $e->getMessage());
        }
    }
}
