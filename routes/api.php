<?php

use App\Http\Controllers\Api\NotificationController;
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
    Route::get('/inventory-data', [ProductController::class, 'getInventoryData']);

    // Booking
    Route::get('/booking-active', [BookingController::class, 'getBookingActive']);
    Route::get('/booking-history', [BookingController::class, 'getHistory']);
    Route::post('/checkout', [BookingController::class, 'checkout']);

    // Vessel
    Route::get('/vessels', [BookingController::class, 'getVessels']);

    // Attendance
    Route::get('/get-statistik', [AttendanceController::class, 'statsAttendance']);

    // Category
    Route::get('/categories', [CategoryController::class, 'getCategories']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/save-token', [NotificationController::class, 'saveToken']);
    Route::post('/test-notification', [NotificationController::class, 'testSendNotification']);
});

Route::post('/login', [AuthController::class, 'login']);
