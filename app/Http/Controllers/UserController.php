<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getProfile(Request $request){
        $user = $request->user();

        if(!$user){
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials'
            ]);
        }
        
        return response()->json([
            'status' => true, 
            'data' => $user
      ]);
    }
}
