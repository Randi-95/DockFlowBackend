<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/attendance', function () {
    return view('attendance');
});

Route::post('/attendance/scan', [AttendanceController::class, 'scan']);
