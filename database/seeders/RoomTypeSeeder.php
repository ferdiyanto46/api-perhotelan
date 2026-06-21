<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;
use Carbon\Carbon;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Hotel 1 — Grand Hotel Bali (id=1)
        RoomType::insert([
            [
                'hotel_id'        => 1,
                'name'            => 'Standard Room',
                'capacity'        => 2,
                'facilities'      => 'AC, TV, WiFi, Kamar Mandi Dalam',
                'price_per_night' => 500000.00,
                'img_url'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'hotel_id'        => 1,
                'name'            => 'Deluxe Room',
                'capacity'        => 2,
                'facilities'      => 'AC, TV LED 42", WiFi, Balkon, Kamar Mandi Dalam, Bathtub',
                'price_per_night' => 800000.00,
                'img_url'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'hotel_id'        => 1,
                'name'            => 'Suite Room',
                'capacity'        => 4,
                'facilities'      => 'AC, TV 55", WiFi, Ruang Tamu, Dapur Kecil, 2 Kamar Mandi, View Laut',
                'price_per_night' => 1500000.00,
                'img_url'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],

            // Hotel 2 — Jakarta Business Hotel (id=2)
            [
                'hotel_id'        => 2,
                'name'            => 'Standard Room',
                'capacity'        => 2,
                'facilities'      => 'AC, TV, WiFi, Kamar Mandi Dalam, Meja Kerja',
                'price_per_night' => 600000.00,
                'img_url'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'hotel_id'        => 2,
                'name'            => 'Deluxe Room',
                'capacity'        => 2,
                'facilities'      => 'AC, TV LED 42", WiFi, Meja Kerja, Mini Bar, Kamar Mandi Dalam',
                'price_per_night' => 900000.00,
                'img_url'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],

            // Hotel 3 — Malioboro Palace Yogyakarta (id=3)
            [
                'hotel_id'        => 3,
                'name'            => 'Standard Room',
                'capacity'        => 2,
                'facilities'      => 'AC, TV, WiFi, Kamar Mandi Dalam',
                'price_per_night' => 400000.00,
                'img_url'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'hotel_id'        => 3,
                'name'            => 'Suite Room',
                'capacity'        => 4,
                'facilities'      => 'AC, TV 50", WiFi, Ruang Tamu, 2 Kamar Mandi, View Malioboro',
                'price_per_night' => 1000000.00,
                'img_url'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ]);
    }
}
