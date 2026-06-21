<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use Carbon\Carbon;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        Hotel::insert([
            [
                'name'        => 'Grand Hotel Bali',
                'city'        => 'Bali',
                'address'     => 'Jl. Pantai Kuta No. 88, Kuta, Badung',
                'description' => 'Hotel bintang 5 dengan pemandangan laut yang memukau, fasilitas kolam renang infinity, spa mewah, dan restoran seafood premium.',
                'rating'      => 4.8,
                'img_url'     => null,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'name'        => 'Jakarta Business Hotel',
                'city'        => 'Jakarta',
                'address'     => 'Jl. Jend. Sudirman No. 10, Tanah Abang, Jakarta Pusat',
                'description' => 'Hotel bisnis modern di jantung kota Jakarta, dekat pusat perbelanjaan dan kawasan perkantoran strategis. Dilengkapi ruang meeting dan business center.',
                'rating'      => 4.2,
                'img_url'     => null,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'name'        => 'Malioboro Palace Yogyakarta',
                'city'        => 'Yogyakarta',
                'address'     => 'Jl. Malioboro No. 52, Gedongtengen, Kota Yogyakarta',
                'description' => 'Hotel butik berdesain Jawa klasik di kawasan Malioboro yang ikonik. Berjalan kaki ke Keraton, Tamansari, dan pusat oleh-oleh khas Yogyakarta.',
                'rating'      => 4.5,
                'img_url'     => null,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
        ]);
    }
}