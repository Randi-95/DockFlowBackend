<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Handle the incoming RFID scan for check-in or check-out.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'rfid' => 'required|string',
        ]);

        $rfidUid = $request->input('rfid');
        $user = User::where('rfid_uid', $rfidUid)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu tidak dikenali di sistem!',
            ], 404);
        }

        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            $now = Carbon::now();
            $cutoffTime = Carbon::today()->setTime(7, 30, 0);

            $status = $now->greaterThan($cutoffTime) ? 'late' : 'present';

            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => $now,
                'status' => $status,
            ]);

            return response()->json([
                'success' => true,
                'action' => 'check_in',
                'user_name' => $user->name,
                'message' => 'Berhasil Check-In',
            ]);
        }


        if (is_null($attendance->check_out)) {
            $attendance->update([
                'check_out' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'action' => 'check_out',
                'user_name' => $user->name,
                'message' => 'Berhasil Check-Out',
            ]);
        }


        return response()->json([
            'success' => false,
            'message' => 'Anda sudah melakukan Check-Out hari ini.',
        ], 400);
    }


    public function statsAttendance(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => true,
                'message' => 'Unauthorized'
            ], 401);
        }

        $now = Carbon::now()->day;


        $totalDayWork = $now;
        $totalPresent = Attendance::where('user_id', $user->id)->where('status', 'present')->count();
        $totalLate = Attendance::where('user_id', $user->id)->where('status',  'late')->count();
        $totalAbsent = Attendance::where('user_id', $user->id)->where('status', 'absent')->count();
        $present = $totalPresent + $totalLate;
        $percentage = ($present / $totalDayWork) * 100;

        return response()->json([
            'status' => true,
            'message' => 'Succes Get Stats Attendance', 
            'totalPresent' => $totalPresent,
            'totalLate' => $totalLate,
            'totalAbsent' => $totalAbsent,
            'totalDayWork' => $totalDayWork,
            'percentage' => $percentage
        ], 200);
    }
}
