<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json([
                'status' => false,
                'message' => 'Invalid Credentials'
            ],401);
        }

        if($user->is_active == 0){
            return response()->json([
                'status' => false,
                'message' => 'Account Inactive'
            ],401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true, 
            'message' => 'succes login',
            'token' => $token,
            'user' => $user
         ], 200);
    }
}
