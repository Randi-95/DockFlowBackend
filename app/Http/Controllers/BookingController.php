<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function getBookingActive(Request $request){
        $user = $request->user();

        if(!$user){
            return response()->json([
                'status' => true, 
                'message' => 'Unauthorized'
            ], 401);
        }

         $totalPesanan = Booking::where([
            ['user_id', $user->id],
            ['status', '!=', 'completed'],
            ['status', '!=', 'cancelled']
         ])->count();

        return response()->json(
            [
                'status' => true, 
                'total_pesanan' => $totalPesanan
            ], 200
        );
    }

    public function getHistory(Request $request){
        $user = $request->user();

        if(!$user){
            return response()->json([
                'status' => false, 
                'message' => 'Unauthorized'
            ], 401);
        }

        $counts = [
            'waiting' => Booking::where('user_id', $user->id)->where('status', 'waiting')->count(),
            'confirmed' => Booking::where('user_id', $user->id)->where('status', 'confirmed')->count(),
            'processing' => Booking::where('user_id', $user->id)->where('status', 'processing')->count(),
            'completed' => Booking::where('user_id', $user->id)->where('status', 'completed')->count(),
            'cancelled' => Booking::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        $query = Booking::with(['vessel', 'bookingDetails.product'])
            ->where('user_id', $user->id);

        if ($request->has('status') && in_array($request->status, ['waiting', 'confirmed', 'processing', 'completed', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('booking_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('vessel', function($qVessel) use ($searchTerm) {
                      $qVessel->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        $formattedBookings = $bookings->map(function($booking) {
            return [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
                'vessel_name' => $booking->vessel ? $booking->vessel->name : null,
                'created_at' => $booking->created_at,
                'estimated_delivery_date' => $booking->estimated_delivery_date,
                'total_estimated_price' => $booking->total_estimated_price,
                'items_count' => $booking->bookingDetails->count(),
                'barcode_url' => $booking->barcode ? asset('storage/' . $booking->barcode) : null,
                'dock_location' => $booking->dock_location,
                'proof_of_delivery_url' => $booking->proof_of_delivery ? asset('storage/' . $booking->proof_of_delivery) : null,
                'items' => $booking->bookingDetails->map(function($detail) {
                    return [
                        'product_name' => $detail->product->name,
                        'qty' => $detail->qty,
                        'price' => $detail->price_at_booking,
                        'image_url' => $detail->product->image ? asset('storage/' . $detail->product->image) : null,
                    ];
                })->toArray(),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'summary' => $counts,
                'bookings' => $formattedBookings
            ]
        ], 200);
    }

    public function getVessels()
    {
        $vessels = Vessel::select('id', 'name')->get();
        return response()->json([
            'status' => true,
            'data' => $vessels
        ], 200);
    }

    public function checkout(Request $request)
    {
        $user = $request->user();

        if(!$user){
            return response()->json([
                'status' => false, 
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'dock_location' => 'required|string',
            'estimated_delivery_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price_at_booking' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $vesselId = $request->vessel_id;

            if (empty($vesselId) && !empty($request->vessel_name)) {
                $vessel = Vessel::firstOrCreate(['name' => $request->vessel_name]);
                $vesselId = $vessel->id;
            }

            if (empty($vesselId)) {
                 return response()->json([
                    'status' => false,
                    'message' => 'Vessel is required.'
                 ], 400);
            }

            $totalPrice = 0;
            foreach ($request->items as $item) {
                $totalPrice += ($item['price_at_booking'] * $item['qty']);
            }

            $bookingNumber = 'BK-' . date('YmdHis') . rand(1000, 9999);

            $options = new QROptions([
                'outputType'   => QROutputInterface::GDIMAGE_PNG,
                'outputBase64' => false,
                'scale'        => 8,
            ]);
            $qrcode = new QRCode($options);
            $qrData = $qrcode->render($bookingNumber);

            $filename = 'barcodes/' . $bookingNumber . '.png';
            Storage::disk('public')->put($filename, $qrData);

            $booking = Booking::create([
                'booking_number' => $bookingNumber,
                'barcode' => $filename,
                'user_id' => $user->id,
                'vessel_id' => $vesselId,
                'dock_location' => $request->dock_location,
                'estimated_delivery_date' => $request->estimated_delivery_date,
                'total_estimated_price' => $totalPrice,
                'status' => 'waiting',
            ]);

            foreach ($request->items as $item) {
                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price_at_booking' => $item['price_at_booking'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Checkout successful',
                'data' => $booking
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to process checkout. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

 
    public function uploadProofOfDelivery(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        if ($booking->status !== 'on_delivery') {
            return response()->json([
                'status' => false,
                'message' => 'Bukti pengiriman hanya dapat diunggah saat pesanan berstatus "Dalam Pengiriman" (on_delivery).',
            ], 422);
        }

        $request->validate([
            'proof_of_delivery' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        try {
            if ($booking->proof_of_delivery) {
                Storage::disk('public')->delete($booking->proof_of_delivery);
            }

            $file = $request->file('proof_of_delivery');
            $filename = 'proof_of_delivery/' . $booking->booking_number . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('proof_of_delivery', basename($filename), 'public');

            $booking->update([
                'proof_of_delivery' => $path,
                'status'            => 'pending_completion',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Bukti pengiriman berhasil diunggah. Menunggu konfirmasi admin untuk menyelesaikan pesanan.',
                'data'    => [
                    'booking_id'          => $booking->id,
                    'booking_number'      => $booking->booking_number,
                    'status'              => $booking->status,
                    'proof_of_delivery_url' => asset('storage/' . $path),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Upload Proof of Delivery Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengunggah bukti pengiriman. Silakan coba lagi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
