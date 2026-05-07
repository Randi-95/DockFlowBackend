<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $today = Carbon::today();
    
    $absentUsers = User::whereDoesntHave('attendances', function ($query) use ($today) {
        $query->whereDate('date', $today);
    })->get();

    foreach ($absentUsers as $user) {
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => $today->copy()->setTime(23, 59, 59),
            'status' => 'absent',
        ]);
    }
})->dailyAt('23:59');
