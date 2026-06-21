<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // 1. USERS tanpa hotel_id (Super Admin & Customer)
        // =============================================
        User::firstOrCreate(
            ['email' => 'superadmin@hotel.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password123'),
                'role'     => 'super-admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name'     => 'John Doe',
                'password' => Hash::make('password123'),
                'role'     => 'customer',
            ]
        );

        // =============================================
        // 2. HOTELS (harus sebelum Admin user & RoomType)
        // =============================================
        $this->call(HotelSeeder::class);

        // =============================================
        // 3. ADMIN USER (butuh hotel_id, jadi setelah Hotel)
        // =============================================
        User::firstOrCreate(
            ['email' => 'admin@hotel.com'],
            [
                'name'     => 'Hotel Admin',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
                'hotel_id' => 1, // Grand Hotel Bali
            ]
        );

        // =============================================
        // 3. ROOM TYPES (harus sebelum Room)
        // =============================================
        $this->call(RoomTypeSeeder::class);

        // =============================================
        // 4. ROOMS (harus sebelum Booking)
        // =============================================
        $this->call(RoomSeeder::class);

        // =============================================
        // 5. BOOKINGS & PAYMENTS (terakhir)
        // =============================================
        $this->call(BookingSeeder::class);
    }
}
