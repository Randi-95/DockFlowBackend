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

Route::get('/admin/products/print-barcodes', function (\Illuminate\Http\Request $request) {
    $ids = explode(',', $request->query('ids', ''));
    $products = \App\Models\Product::whereIn('id', $ids)->get();
    return view('products.print-barcodes', compact('products'));
})->name('products.print-barcodes')->middleware('auth');
