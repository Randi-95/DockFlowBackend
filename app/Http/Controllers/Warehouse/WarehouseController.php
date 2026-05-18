<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    private function getWarehouseUser(Request $request): User
    {
        return $request->attributes->get('warehouse_user')
            ?? User::findOrFail(session('warehouse_admin_id'));
    }

    public function queue(Request $request)
    {
        $user = $this->getWarehouseUser($request);

        $bookings = Booking::with(['user', 'vessel', 'bookingDetails.product'])
            ->where('status', 'confirmed')
            ->orderBy('updated_at', 'asc')
            ->get();

        return view('warehouse.queue', compact('bookings', 'user'));
    }

    public function packingDetail(Request $request, Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return redirect()->route('warehouse.queue')
                ->with('error', 'Pesanan ini tidak dalam status Dikonfirmasi.');
        }

        $user = $this->getWarehouseUser($request);
        $booking->load(['user', 'vessel', 'bookingDetails.product']);

        $sessionKey = 'packing_progress_' . $booking->id;
        $scannedBarcodes = session($sessionKey, []);

        return view('warehouse.packing', compact('booking', 'user', 'scannedBarcodes'));
    }

    public function scanBarcode(Request $request, Booking $booking)
    {
        $request->validate(['barcode' => 'required|string']);

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dalam status Dikonfirmasi.',
            ], 400);
        }

        $barcode = trim($request->input('barcode'));
        $booking->load('bookingDetails.product');

        $matchedDetail = null;
        foreach ($booking->bookingDetails as $detail) {
            $product = $detail->product;
            if ($product && $product->barcode) {
                $productBarcode = basename($product->barcode, '.png');
                if ($productBarcode === $barcode || $product->sku_code === $barcode) {
                    $matchedDetail = $detail;
                    break;
                }
            }
        }

        if (!$matchedDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode tidak cocok dengan barang dalam pesanan ini.',
            ], 422);
        }

        $sessionKey = 'packing_progress_' . $booking->id;
        $scannedBarcodes = session($sessionKey, []);

        $productId = (string) $matchedDetail->product_id;
        $scannedBarcodes[$productId] = true;
        session([$sessionKey => $scannedBarcodes]);

        $totalItems = $booking->bookingDetails->count();
        $scannedCount = count($scannedBarcodes);
        $allDone = $scannedCount >= $totalItems;

        return response()->json([
            'success'       => true,
            'product_name'  => $matchedDetail->product->name,
            'product_id'    => $productId,
            'scanned_count' => $scannedCount,
            'total_items'   => $totalItems,
            'all_done'      => $allDone,
        ]);
    }

    public function completePacking(Request $request, Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dalam status Dikonfirmasi.',
            ], 400);
        }

        $sessionKey = 'packing_progress_' . $booking->id;
        $scannedBarcodes = session($sessionKey, []);
        $booking->load('bookingDetails');
        $totalItems = $booking->bookingDetails->count();

        if (count($scannedBarcodes) < $totalItems) {
            return response()->json([
                'success' => false,
                'message' => 'Belum semua barang di-scan. Pastikan semua item telah diverifikasi.',
            ], 422);
        }

        try {
            $booking->update(['status' => 'processing']);
            session()->forget($sessionKey);

            return response()->json([
                'success'  => true,
                'message'  => 'Packing selesai! Status pesanan berubah ke Diproses.',
                'redirect' => route('warehouse.queue'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function printThermal(Request $request, Booking $booking)
    {
        $user = $this->getWarehouseUser($request);
        $booking->load(['user', 'vessel', 'bookingDetails.product']);

        return view('warehouse.print-thermal', compact('booking', 'user'));
    }

    public function handover(Request $request)
    {
        $user = $this->getWarehouseUser($request);
        return view('warehouse.handover', compact('user'));
    }

    public function scanBookingBarcode(Request $request)
    {
        $request->validate(['barcode' => 'required|string']);

        $barcode = trim($request->input('barcode'));

        $booking = Booking::with(['user', 'vessel', 'bookingDetails.product'])
            ->where(function ($q) use ($barcode) {
                $q->where('booking_number', $barcode)
                  ->orWhere('barcode', 'like', '%' . $barcode . '%');
            })
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan untuk barcode: ' . $barcode,
            ], 404);
        }

        if ($booking->status !== 'processing') {
            $labels = [
                'waiting'     => 'Menunggu',
                'confirmed'   => 'Dikonfirmasi',
                'on_delivery' => 'Dalam Pengiriman',
                'completed'   => 'Selesai',
                'cancelled'   => 'Dibatalkan',
            ];
            $currentLabel = $labels[$booking->status] ?? $booking->status;
            return response()->json([
                'success' => false,
                'message' => "Pesanan ini berstatus '{$currentLabel}', bukan Diproses. Tidak bisa diserahterimakan.",
            ], 422);
        }

        try {
            $booking->update(['status' => 'on_delivery']);

            $items = $booking->bookingDetails->map(fn($d) => [
                'name' => $d->product?->name ?? '-',
                'qty'  => $d->qty,
            ]);

            return response()->json([
                'success'        => true,
                'booking_number' => $booking->booking_number,
                'vessel_name'    => $booking->vessel?->name,
                'dock_location'  => $booking->dock_location,
                'customer_name'  => $booking->user?->name,
                'items'          => $items,
                'message'        => 'Serah terima berhasil! Status berubah ke Dalam Pengiriman.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
