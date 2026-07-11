<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;

class BookingController extends BaseController
{
    /**
     * Tampilkan daftar booking.
     * Admin melihat semua booking; customer hanya melihat booking miliknya.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            // Super Admin: lihat semua booking
            $query = Booking::with(['user', 'room.roomType.hotel', 'payment']);
        } elseif ($user->isAdmin()) {
            // Admin hotel: hanya lihat booking di hotel miliknya
            $query = Booking::with(['user', 'room.roomType.hotel', 'payment'])
                ->whereHas('room.roomType', function ($q) use ($user) {
                    $q->where('hotel_id', $user->hotel_id);
                });
        } else {
            // Customer: hanya lihat booking miliknya sendiri
            $query = $user->bookings()->with(['room.roomType.hotel', 'payment']);
        }

        $bookings = $query->paginate(10);

        return response()->json($bookings);
    }

    /**
     * Tampilkan detail booking berdasarkan ID.
     * Admin bisa melihat semua; customer hanya miliknya.
     */
    public function show($id)
    {
        $user  = request()->user();
        $query = Booking::with(['room.roomType.hotel', 'payment']);

        if ($user->isSuperAdmin()) {
            // Super Admin: akses semua booking
        } elseif ($user->isAdmin()) {
            // Admin hotel: hanya booking di hotel miliknya
            $query->whereHas('room.roomType', function ($q) use ($user) {
                $q->where('hotel_id', $user->hotel_id);
            });
        } else {
            // Customer: hanya booking miliknya sendiri
            $query->where('user_id', $user->id);
        }

        $booking = $query->findOrFail($id);

        return response()->json($booking);
    }

    /**
     * Buat booking baru, simpan ke DB, lalu minta Snap Token dari Midtrans.
     */
    public function checkout(Request $request)
    {
        $this->validate($request, [
            'room_id'        => 'required|exists:rooms,id',
            'check_in'       => 'required|date|after_or_equal:today',
            'check_out'      => 'required|date|after:check_in',
            'payment_method' => 'required|string',
        ]);

        $user = $request->user();
        $room = Room::with('roomType.hotel')->findOrFail($request->room_id);

        // Cek ketersediaan kamar di tanggal yang diminta
        if (!$room->isAvailable($request->check_in, $request->check_out)) {
            return response()->json([
                'message' => 'Room is not available for selected dates',
            ], 422);
        }

        // Hitung total harga
        $checkIn    = new \DateTime($request->check_in);
        $checkOut   = new \DateTime($request->check_out);
        $nights     = $checkIn->diff($checkOut)->days;
        $totalPrice = $room->price * $nights;

        DB::beginTransaction();

        try {
            // Simpan data booking
            $booking = Booking::create([
                'user_id'     => $user->id,
                'room_id'     => $room->id,
                'check_in'    => $request->check_in,
                'check_out'   => $request->check_out,
                'total_price' => $totalPrice,
                'status'      => 'pending',
            ]);

            // Simpan data pembayaran awal (status: pending)
            $payment = Payment::create([
                'booking_id'     => $booking->id,
                'external_id'    => 'BOOK-' . time() . '-' . $booking->id,
                'payment_method' => $request->payment_method,
                'amount'         => $totalPrice,
                'status'         => 'pending',
                'raw_response'   => null,
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create booking',
                'error'   => $e->getMessage(),
            ], 500);
        }

        // ── Konfigurasi Midtrans (di luar transaksi DB) ───────────────────────
        \Midtrans\Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        // ── Buat parameter transaksi untuk Midtrans ───────────────────────────
        $snapParams = [
            'transaction_details' => [
                'order_id'     => $payment->external_id,  // ID unik per transaksi
                'gross_amount' => (int) $totalPrice,       // Integer, dalam Rupiah
            ],
            'customer_details'    => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
            'item_details'        => [
                [
                    'id'       => 'ROOM-' . $room->id,
                    'price'    => (int) $room->price,
                    'quantity' => $nights,
                    'name'     => ($room->roomType->name ?? 'Kamar')
                                  . ' - ' . ($room->roomType->hotel->name ?? ''),
                ],
            ],
        ];

        // ── Minta Snap Token ke server Midtrans ───────────────────────────────
        try {
            $snapToken = \Midtrans\Snap::getSnapToken($snapParams);
        } catch (\Exception $e) {
            // Booking sudah tersimpan, tapi gagal ambil snap token
            // Kembalikan info booking agar frontend bisa retry
            return response()->json([
                'message'    => 'Booking created but failed to get payment token',
                'booking'    => $booking->load(['room.roomType.hotel', 'payment']),
                'snap_token' => null,
                'error'      => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'message'    => 'Booking created successfully',
            'booking'    => $booking->load(['room.roomType.hotel', 'payment']),
            'snap_token' => $snapToken, // Kirim ke frontend → snap.pay(snap_token)
        ], 201);
    }

    /**
     * Retry pembayaran untuk booking yang masih pending.
     * Membuat external_id baru dan meminta Snap Token baru dari Midtrans.
     *
     * Super Admin: bisa retry semua booking.
     * Admin hotel: hanya booking di hotel miliknya.
     * Customer: hanya booking miliknya sendiri.
     */
    public function retryPayment(Request $request, $id)
    {
        $user  = $request->user();
        $query = Booking::with(['user', 'room.roomType.hotel', 'payment']);

        if ($user->isSuperAdmin()) {
            // Super Admin: akses semua booking
        } elseif ($user->isAdmin()) {
            // Admin hotel: hanya booking di hotel miliknya
            $query->whereHas('room.roomType', function ($q) use ($user) {
                $q->where('hotel_id', $user->hotel_id);
            });
        } else {
            // Customer: hanya booking miliknya sendiri
            $query->where('user_id', $user->id);
        }

        $booking = $query->findOrFail($id);

        // Hanya booking dengan status pending yang bisa di-retry
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Booking ini tidak dapat dibayar ulang karena statusnya bukan pending',
            ], 422);
        }

        $payment = $booking->payment;

        if (!$payment) {
            return response()->json([
                'message' => 'Data pembayaran tidak ditemukan untuk booking ini',
            ], 404);
        }

        // Buat external_id baru agar tidak konflik dengan transaksi sebelumnya di Midtrans
        $newExternalId = 'BOOK-' . time() . '-' . $booking->id;
        $payment->update([
            'external_id' => $newExternalId,
            'status'      => 'pending',
        ]);

        // ── Konfigurasi Midtrans ──────────────────────────────────────────────
        \Midtrans\Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        // Gunakan data pemilik booking (bukan admin yang mengakses)
        $bookingOwner = $booking->user;
        $room         = $booking->room;
        $nights       = $booking->nights;

        // ── Buat parameter transaksi untuk Midtrans ───────────────────────────
        $snapParams = [
            'transaction_details' => [
                'order_id'     => $newExternalId,
                'gross_amount' => (int) $booking->total_price,
            ],
            'customer_details'    => [
                'first_name' => $bookingOwner->name,
                'email'      => $bookingOwner->email,
            ],
            'item_details'        => [
                [
                    'id'       => 'ROOM-' . $room->id,
                    'price'    => (int) $room->price,
                    'quantity' => $nights,
                    'name'     => ($room->roomType->name ?? 'Kamar')
                                  . ' - ' . ($room->roomType->hotel->name ?? ''),
                ],
            ],
        ];

        // ── Minta Snap Token baru ke server Midtrans ──────────────────────────
        try {
            $snapToken = \Midtrans\Snap::getSnapToken($snapParams);
        } catch (\Exception $e) {
            return response()->json([
                'message'    => 'Gagal mendapatkan token pembayaran dari Midtrans',
                'booking'    => $booking->load(['user', 'room.roomType.hotel', 'payment']),
                'snap_token' => null,
                'error'      => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'message'    => 'Snap token berhasil dibuat, silakan lanjutkan pembayaran',
            'booking'    => $booking->load(['user', 'room.roomType.hotel', 'payment']),
            'snap_token' => $snapToken,
        ]);
    }

    /**
     * Handle webhook notifikasi dari Midtrans.
     */
    public function handleNotification(Request $request)
    {
        $payload = $request->all();

        DB::beginTransaction();

        try {
            $payment = Payment::where('external_id', $payload['order_id'])->firstOrFail();

            $payment->update([
                'status'       => $payload['transaction_status'],
                'raw_response' => $payload,
            ]);

            if (in_array($payload['transaction_status'], ['capture', 'settlement'])) {
                $payment->markAsPaid();
            } elseif (in_array($payload['transaction_status'], ['deny', 'expire', 'cancel'])) {
                $payment->markAsFailed();
            }

            DB::commit();

            return response()->json(['message' => 'Notification handled successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to handle notification'], 500);
        }
    }
}