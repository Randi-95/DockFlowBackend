<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Warehouse\WarehouseAuthController;
use App\Http\Controllers\Warehouse\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/attendance', function () {
    return view('attendance');
});

Route::post('/attendance/scan', [AttendanceController::class, 'scan']);

Route::get('/admin/products/print-barcodes', function (\Illuminate\Http\Request $request) {
    $ids = explode(',', $request->query('ids', ''));
    $products = \App\Models\Product::whereIn('id', $ids)->get();
    return view('products.print-barcodes', compact('products'));
})->name('products.print-barcodes')->middleware('auth');

Route::prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/login',  [WarehouseAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WarehouseAuthController::class, 'rfidLogin'])->name('login.submit');

    Route::middleware('warehouse.admin')->group(function () {
        Route::post('/logout', [WarehouseAuthController::class, 'logout'])->name('logout');
        Route::get('/queue',   [WarehouseController::class, 'queue'])->name('queue');
        Route::get('/packing/{booking}', [WarehouseController::class, 'packingDetail'])->name('packing');
        Route::post('/packing/{booking}/scan', [WarehouseController::class, 'scanBarcode'])->name('packing.scan');
        Route::post('/packing/{booking}/complete', [WarehouseController::class, 'completePacking'])->name('packing.complete');
        Route::get('/handover', [WarehouseController::class, 'handover'])->name('handover');
        Route::post('/handover/scan', [WarehouseController::class, 'scanBookingBarcode'])->name('handover.scan');
    });
});

