<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getProfile(Request $request){
        $user = $request->user();
        $booking = Booking::where('user_id', $user->id)->count();

        if(!$user){
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials'
            ]);
        }
        
        return response()->json([
            'status' => true, 
            'data' => $user,
            'bookingActive' => $booking
      ]);
    }
}
