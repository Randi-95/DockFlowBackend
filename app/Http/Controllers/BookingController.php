<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function getBookingActive(Request $request){
        $user = $request->user();

        if(!$user){
            return response()->json([
                'status' => true, 
                'message' => 'Unauthorized'
            ], 401);
        }

         $totalPesanan = Booking::where([
            ['user_id', $user->id],
            ['status', '!=', 'completed'],
            ['status', '!=', 'cancelled']
         ])->count();

        return response()->json(
            [
                'status' => true, 
                'total_pesanan' => $totalPesanan
            ], 200
        );
    }

    public function getHistory(Request $request){
        $user = $request->user();

        if(!$user){
            return response()->json([
                'status' => false, 
                'message' => 'Unauthorized'
            ], 401);
        }

        $counts = [
            'waiting' => Booking::where('user_id', $user->id)->where('status', 'waiting')->count(),
            'confirmed' => Booking::where('user_id', $user->id)->where('status', 'confirmed')->count(),
            'processing' => Booking::where('user_id', $user->id)->where('status', 'processing')->count(),
            'completed' => Booking::where('user_id', $user->id)->where('status', 'completed')->count(),
            'cancelled' => Booking::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        $query = Booking::with(['vessel', 'bookingDetails'])
            ->where('user_id', $user->id);

        if ($request->has('status') && in_array($request->status, ['waiting', 'confirmed', 'processing', 'completed', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('booking_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('vessel', function($qVessel) use ($searchTerm) {
                      $qVessel->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        $formattedBookings = $bookings->map(function($booking) {
            return [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
                'vessel_name' => $booking->vessel ? $booking->vessel->name : null,
                'created_at' => $booking->created_at,
                'estimated_delivery_date' => $booking->estimated_delivery_date,
                'total_estimated_price' => $booking->total_estimated_price,
                'items_count' => $booking->bookingDetails->count(),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'summary' => $counts,
                'bookings' => $formattedBookings
            ]
        ], 200);
    }
}
