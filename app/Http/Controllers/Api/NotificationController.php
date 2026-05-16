<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationController extends Controller
{
    public function saveToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'device_id' => 'required',
            'fcm_token' => 'required'
        ]);

        DeviceToken::updateOrCreate(
            ['device_id' => $request->device_id],
            [
                'user_id' => $request->user_id,
                'fcm_token' => $request->fcm_token,
                'device_name' => $request->device_name ?? 'Unknown Device'
            ]
        );

        return response()->json(['message' => 'Token berhasil disimpan!']);
    }

    public function testSendNotification(Messaging $messaging, Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'title' => 'required',
            'body' => 'required'
        ]);

        $tokens = DeviceToken::where('user_id', $request->user_id)->pluck('fcm_token')->toArray();

        if (empty($tokens)) {
            return response()->json(['message' => 'User tidak memiliki perangkat aktif'], 404);
        }

        $notification = Notification::create($request->title, $request->body);

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData(['screen' => 'order_detail', 'transaction_id' => 'TRX-001']);

        $report = $messaging->sendMulticast($message, $tokens);

        return response()->json([
            'message' => 'Notifikasi dikirim!',
            'success_count' => $report->successes()->count(),
            'failure_count' => $report->failures()->count()
        ]);
    }
}
