<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class WarehouseAuthController extends Controller
{
    public function showLogin()
    {
        if (session('warehouse_admin_id')) {
            return redirect()->route('warehouse.queue');
        }

        return view('warehouse.login');
    }

    public function rfidLogin(Request $request)
    {
        $request->validate(['rfid' => 'required|string']);

        $rfidUid = trim($request->input('rfid'));
        $user = User::where('rfid_uid', $rfidUid)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu RFID tidak dikenali di sistem.',
            ], 404);
        }

        if ($user->role !== 'warehouse_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Kartu ini bukan untuk Warehouse Admin.',
            ], 403);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif. Hubungi administrator.',
            ], 403);
        }

        session(['warehouse_admin_id' => $user->id]);

        return response()->json([
            'success'   => true,
            'user_name' => $user->name,
            'redirect'  => route('warehouse.queue'),
        ]);
    }

    public function logout(Request $request)
    {
        session()->forget('warehouse_admin_id');
        session()->forget('packing_progress');
        return redirect()->route('warehouse.login');
    }
}
