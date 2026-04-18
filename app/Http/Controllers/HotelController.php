<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    
    public function index()
{
        $hotels = Hotel::all();
        return response()->json([
            'success' => true,
            'message' => 'List Semua Hotel',
            'data' => $hotels,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rating' => 'nullable|numeric|min:0|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',  // Validasi untuk file gambar
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Semua Kolom Harus Diisi !! ;(',
                'data' => $validator->errors(),
            ], 422);

        } else {
            $imgUrl = '';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('public/hotels', $imageName);  // Simpan di storage/app/public/hotels
                $imgUrl = Storage::url($path);  // Dapatkan URL publik
            }

            $hotels = Hotel::create([
                'name' => $request->input('name'),
                'city' => $request->input('city'),
                'address' => $request->input('address'),
                'description' => $request->input('description'),
                'rating' => $request->input('rating'),
                'img_url' => $imgUrl,  // Simpan path gambar
            ]);

            if ($hotels){
                return response()->json([
                    'success' => true,
                    'message' => 'Hotel Berhasil Ditambahkan',
                    'data' => $hotels,
                ], 201);

            }else {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel Gagal Ditambahkan',
                ], 400);
            }
        }
    }

}
