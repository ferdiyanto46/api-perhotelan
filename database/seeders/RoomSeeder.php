<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use Carbon\Carbon;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // =============================================
        // Hotel 1 — Grand Hotel Bali
        // =============================================

        // RoomType 1: Standard Room Bali
        Room::insert([
            ['room_type_id' => 1, 'room_number' => '101', 'status' => 'available', 'description' => 'Standard room lantai 1 view taman',        'price' => 500000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 1, 'room_number' => '102', 'status' => 'available', 'description' => 'Standard room lantai 1 view kolam renang',  'price' => 500000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 1, 'room_number' => '103', 'status' => 'available', 'description' => 'Standard room lantai 1',                    'price' => 500000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // RoomType 2: Deluxe Room Bali
        Room::insert([
            ['room_type_id' => 2, 'room_number' => '201', 'status' => 'available', 'description' => 'Deluxe room lantai 2 dengan balkon',        'price' => 800000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 2, 'room_number' => '202', 'status' => 'available', 'description' => 'Deluxe room lantai 2 view taman',            'price' => 800000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 2, 'room_number' => '203', 'status' => 'occupied',  'description' => 'Deluxe room lantai 2',                       'price' => 800000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // RoomType 3: Suite Room Bali
        Room::insert([
            ['room_type_id' => 3, 'room_number' => '301', 'status' => 'available', 'description' => 'Suite mewah lantai 3 dengan view laut',     'price' => 1500000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 3, 'room_number' => '302', 'status' => 'available', 'description' => 'Suite premium lantai 3 corner room',         'price' => 1500000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // =============================================
        // Hotel 2 — Jakarta Business Hotel
        // =============================================

        // RoomType 4: Standard Room Jakarta
        Room::insert([
            ['room_type_id' => 4, 'room_number' => '101', 'status' => 'available', 'description' => 'Standard room bisnis lantai 1',             'price' => 600000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 4, 'room_number' => '102', 'status' => 'available', 'description' => 'Standard room bisnis lantai 1',             'price' => 600000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 4, 'room_number' => '103', 'status' => 'available', 'description' => 'Standard room bisnis lantai 1',             'price' => 600000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // RoomType 5: Deluxe Room Jakarta
        Room::insert([
            ['room_type_id' => 5, 'room_number' => '201', 'status' => 'available',    'description' => 'Deluxe room bisnis lantai 2 view kota',   'price' => 900000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 5, 'room_number' => '202', 'status' => 'maintenance',  'description' => 'Deluxe room sedang dalam perbaikan',       'price' => 900000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // =============================================
        // Hotel 3 — Malioboro Palace Yogyakarta
        // =============================================

        // RoomType 6: Standard Room Yogya
        Room::insert([
            ['room_type_id' => 6, 'room_number' => '101', 'status' => 'available', 'description' => 'Standard room dekat Malioboro',             'price' => 400000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 6, 'room_number' => '102', 'status' => 'available', 'description' => 'Standard room lantai 1',                    'price' => 400000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 6, 'room_number' => '103', 'status' => 'available', 'description' => 'Standard room lantai 1',                    'price' => 400000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // RoomType 7: Suite Room Yogya
        Room::insert([
            ['room_type_id' => 7, 'room_number' => '301', 'status' => 'available', 'description' => 'Suite mewah dengan view Malioboro',         'price' => 1000000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
            ['room_type_id' => 7, 'room_number' => '302', 'status' => 'available', 'description' => 'Suite lantai 3 corner room',                'price' => 1000000, 'img_url' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
