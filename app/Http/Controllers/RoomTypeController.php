<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Routing\Controller as BaseController;

class RoomTypeController extends BaseController
{
    public function index()
    {
        $roomTypes = RoomType::with('hotel')->paginate(10);
        return response()->json($roomTypes);
    }

    public function show($id)
    {
        $roomType = RoomType::with(['hotel', 'rooms'])->findOrFail($id);
        return response()->json($roomType);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors(),
            ], 422);
        }

        // Cek kepemilikan: admin hanya bisa tambah tipe kamar di hotel miliknya
        $user = $request->user();
        if (!$user->ownsHotel($request->input('hotel_id'))) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengelola hotel ini',
            ], 403);
        }

        $imgUrl = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/room-types', $imageName);
            $imgUrl = Storage::url($path);
        }

        $roomType = RoomType::create([
            'hotel_id' => $request->input('hotel_id'),
            'name' => $request->input('name'),
            'capacity' => $request->input('capacity'),
            'price_per_night' => $request->input('price_per_night'),
            'img_url' => $imgUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room Type berhasil ditambahkan',
            'data' => $roomType,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $roomType = RoomType::find($id);

        if (!$roomType) {
            return response()->json([
                'success' => false,
                'message' => 'Room Type tidak ditemukan',
            ], 404);
        }

        // Cek kepemilikan: admin hanya bisa edit tipe kamar di hotel miliknya
        $user = $request->user();
        if (!$user->ownsHotel($roomType->hotel_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengelola tipe kamar ini',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'hotel_id' => 'sometimes|exists:hotels,id',
            'name' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'price_per_night' => 'sometimes|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors(),
            ], 422);
        }

        $imgUrl = $roomType->img_url;
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($roomType->img_url) {
                Storage::delete(str_replace('/storage', 'public', $roomType->img_url));
            }
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/room-types', $imageName);
            $imgUrl = Storage::url($path);
        }

        $roomType->update(array_merge($request->only(['hotel_id', 'name', 'capacity', 'price_per_night']), ['img_url' => $imgUrl]));

        return response()->json([
            'success' => true,
            'message' => 'Room Type berhasil diupdate',
            'data' => $roomType,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $roomType = RoomType::findOrFail($id);

        // Cek kepemilikan: admin hanya bisa hapus tipe kamar di hotel miliknya
        $user = $request->user();
        if (!$user->ownsHotel($roomType->hotel_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus tipe kamar ini',
            ], 403);
        }

        $roomType->delete();

        return response()->json([
            'message' => 'Room type deleted successfully'
        ]);
    }
}