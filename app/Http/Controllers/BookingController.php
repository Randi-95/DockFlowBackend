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

        $query = Booking::with(['vessel', 'bookingDetails'])
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

            $generator = new BarcodeGeneratorPNG();
            $barcodeData = $generator->getBarcode($bookingNumber, $generator::TYPE_CODE_128, 2, 60);

            $padding = 20;
            $image = imagecreatefromstring($barcodeData);
            $width = imagesx($image);
            $height = imagesy($image);

            $newWidth = $width + ($padding * 2);
            $newHeight = $height + ($padding * 2);

            $whiteImage = imagecreatetruecolor($newWidth, $newHeight);
            $white = imagecolorallocate($whiteImage, 255, 255, 255);
            imagefill($whiteImage, 0, 0, $white);

            imagecopy($whiteImage, $image, $padding, $padding, 0, 0, $width, $height);

            ob_start();
            imagepng($whiteImage);
            $finalBarcode = ob_get_clean();

            imagedestroy($image);
            imagedestroy($whiteImage);

            $filename = 'barcodes/' . $bookingNumber . '.png';
            Storage::disk('public')->put($filename, $finalBarcode);

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
}
