<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function checkout(Request $request)
    {
        // 1. Validasi Input
        $rules = [
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ];

        if (!$request->user()) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $this->validate($request, $rules);

        // 2. Cari Kamar yang Tersedia (Status: available)
        $room = Room::where('room_type_id', $request->room_type_id)
                    ->where('status', 'available')
                    ->first();

        if (!$room) {
            return response()->json(['message' => 'Maaf, kamar tipe ini sudah penuh.'], 404);
        }

        // 3. Hitung Total Harga
        $roomType = RoomType::find($request->room_type_id);
        $days = (new Booking)->calculateTotalDays($request->check_in, $request->check_out);
        $totalPrice = $days * $roomType->price_per_night;

        $userId = $request->user() ? $request->user()->id : $request->user_id;
        $userName = $request->user() ? $request->user()->name : null;
        $userEmail = $request->user() ? $request->user()->email : null;

        // 4. Simpan Data ke Tabel Bookings (Status: pending)
        $booking = Booking::create([
            'user_id' => $userId,
            'room_id' => $room->id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'total_price' => $totalPrice,
            'status' => 'pending'
        ]);

        // 5. Buat Parameter untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => 'INV-' . $booking->id . '-' . time(),
                'gross_amount' => (int) $totalPrice,
            ],
            'customer_details' => [
                'first_name' => $userName,
                'email' => $userEmail,
            ],
        ];

        // 6. Dapatkan Snap Token dari Midtrans
        try {
            $snapToken = Snap::getSnapToken($params);
            
            return response()->json([
                'status' => 'success',
                'booking_id' => $booking->id,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'room.roomType', 'payments']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        $booking = Booking::with(['user', 'room.roomType', 'payments'])->find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan'], 404);
        }

        return response()->json($booking);
    }

    public function handleNotification(Request $request)
    {
    // 1. Inisialisasi Notifikasi dari Midtrans
    $notif = new \Midtrans\Notification();

    $transaction = $notif->transaction_status;
    $type = $notif->payment_type;
    $orderId = $notif->order_id; // Format: INV-{booking_id}-{timestamp}
    $fraud = $notif->fraud_status;

    // Ambil Booking ID dari string order_id
    $explodedOrderId = explode('-', $orderId);
    $bookingId = $explodedOrderId[1];

    $booking = Booking::find($bookingId);
    if (!$booking) {
        return response()->json(['message' => 'Booking tidak ditemukan'], 404);
    }

    // 2. Logika Update Status dengan Database Transaction agar Aman
    DB::transaction(function () use ($notif, $transaction, $booking, $type) {
        
        // Simpan data ke tabel payments
        Payment::updateOrCreate(
            ['external_id' => $notif->transaction_id],
            [
                'booking_id' => $booking->id,
                'payment_method' => $type,
                'amount' => $notif->gross_amount,
                'status' => $transaction,
                'raw_response' => json_encode($notif)
            ]
        );

        // Update status di tabel bookings dan rooms berdasarkan status transaksi Midtrans
        if ($transaction == 'settlement' || $transaction == 'capture') {
            $booking->update(['status' => 'paid']);
            
            $room = Room::find($booking->room_id);
            if ($room) {
                $room->update(['status' => 'occupied']);
            }

        } elseif ($transaction == 'pending') {
            $booking->update(['status' => 'pending']);
        } elseif ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
            $booking->update(['status' => 'failed']);
            
            $room = Room::find($booking->room_id);
            if ($room) {
                $room->update(['status' => 'available']);
            }
        }
    });

    return response()->json(['message' => 'Notification handled successfully']);
}
}