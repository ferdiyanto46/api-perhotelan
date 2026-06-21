<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@example.com')->first();
        $admin    = User::where('email', 'admin@hotel.com')->first();

        // Ambil beberapa kamar berdasarkan nomor + room_type untuk menghindari konflik
        $baliStd101   = Room::where('room_number', '101')->where('room_type_id', 1)->first(); // Bali Standard
        $baliDlx201   = Room::where('room_number', '201')->where('room_type_id', 2)->first(); // Bali Deluxe
        $jktStd101    = Room::where('room_number', '101')->where('room_type_id', 4)->first(); // Jakarta Standard
        $jktDlx201    = Room::where('room_number', '201')->where('room_type_id', 5)->first(); // Jakarta Deluxe
        $yogyaStd101  = Room::where('room_number', '101')->where('room_type_id', 6)->first(); // Yogya Standard

        // =============================================
        // Booking 1: SELESAI (paid) — customer di Bali Standard, bulan lalu
        // =============================================
        $b1 = Booking::create([
            'user_id'     => $customer->id,
            'room_id'     => $baliStd101->id,
            'check_in'    => '2026-04-10',
            'check_out'   => '2026-04-13',
            'total_price' => 500000 * 3, // 3 malam
            'status'      => 'paid',
        ]);
        Payment::create([
            'booking_id'     => $b1->id,
            'external_id'    => 'BOOK-PAID-001',
            'payment_method' => 'bank_transfer',
            'amount'         => $b1->total_price,
            'status'         => 'paid',
            'raw_response'   => ['transaction_status' => 'settlement', 'payment_type' => 'bank_transfer'],
        ]);

        // =============================================
        // Booking 2: PENDING — customer di Bali Deluxe, minggu depan
        // =============================================
        $b2 = Booking::create([
            'user_id'     => $customer->id,
            'room_id'     => $baliDlx201->id,
            'check_in'    => '2026-05-20',
            'check_out'   => '2026-05-23',
            'total_price' => 800000 * 3, // 3 malam
            'status'      => 'pending',
        ]);
        Payment::create([
            'booking_id'     => $b2->id,
            'external_id'    => 'BOOK-PENDING-002',
            'payment_method' => 'gopay',
            'amount'         => $b2->total_price,
            'status'         => 'pending',
            'raw_response'   => null,
        ]);

        // =============================================
        // Booking 3: GAGAL (failed) — customer di Jakarta Standard
        // =============================================
        $b3 = Booking::create([
            'user_id'     => $customer->id,
            'room_id'     => $jktStd101->id,
            'check_in'    => '2026-04-01',
            'check_out'   => '2026-04-03',
            'total_price' => 600000 * 2, // 2 malam
            'status'      => 'failed',
        ]);
        Payment::create([
            'booking_id'     => $b3->id,
            'external_id'    => 'BOOK-FAILED-003',
            'payment_method' => 'credit_card',
            'amount'         => $b3->total_price,
            'status'         => 'failed',
            'raw_response'   => ['transaction_status' => 'deny', 'status_message' => 'Card declined'],
        ]);

        // =============================================
        // Booking 4: SELESAI (paid) — admin di Jakarta Deluxe
        // =============================================
        $b4 = Booking::create([
            'user_id'     => $admin->id,
            'room_id'     => $jktDlx201->id,
            'check_in'    => '2026-04-15',
            'check_out'   => '2026-04-17',
            'total_price' => 900000 * 2, // 2 malam
            'status'      => 'paid',
        ]);
        Payment::create([
            'booking_id'     => $b4->id,
            'external_id'    => 'BOOK-PAID-004',
            'payment_method' => 'qris',
            'amount'         => $b4->total_price,
            'status'         => 'paid',
            'raw_response'   => ['transaction_status' => 'settlement', 'payment_type' => 'qris'],
        ]);

        // =============================================
        // Booking 5: PENDING — customer di Yogya Standard, bulan depan
        // =============================================
        $b5 = Booking::create([
            'user_id'     => $customer->id,
            'room_id'     => $yogyaStd101->id,
            'check_in'    => '2026-06-10',
            'check_out'   => '2026-06-14',
            'total_price' => 400000 * 4, // 4 malam
            'status'      => 'pending',
        ]);
        Payment::create([
            'booking_id'     => $b5->id,
            'external_id'    => 'BOOK-PENDING-005',
            'payment_method' => 'bank_transfer',
            'amount'         => $b5->total_price,
            'status'         => 'pending',
            'raw_response'   => null,
        ]);
    }
}
