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
}
