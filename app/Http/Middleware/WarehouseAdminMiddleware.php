<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class WarehouseAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $userId = session('warehouse_admin_id');

        if (!$userId) {
            return redirect()->route('warehouse.login');
        }

        $user = User::find($userId);

        if (!$user || $user->role !== 'warehouse_admin' || !$user->is_active) {
            session()->forget('warehouse_admin_id');
            return redirect()->route('warehouse.login');
        }

        $request->attributes->set('warehouse_user', $user);

        return $next($request);
    }
}
