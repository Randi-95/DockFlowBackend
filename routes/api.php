<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'getProfile']);

    // Product
    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::get('/product/{id}', [ProductController::class, 'getProductDetail']);
    Route::get('/information-products', [ProductController::class, 'getInformationProduct']);

    // Booking
    Route::get('/booking-active', [BookingController::class, 'getBookingActive']);
    Route::get('/booking-history', [BookingController::class, 'getHistory']);

    // Attendance
    Route::get('/get-statistik', [AttendanceController::class, 'statsAttendance']);

    // Category
    Route::get('/categories', [CategoryController::class, 'getCategories']);
});

Route::post('/login', [AuthController::class, 'login']);
