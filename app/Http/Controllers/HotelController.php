<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class HotelController extends BaseController
{
    /**
     * Tampilkan daftar hotel dengan filter opsional.
     * Query params: ?city=Bali&search=grand&page=1
     */
    public function index(Request $request)
    {
        $query = Hotel::query();

        // Filter berdasarkan kota
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Pencarian teks berdasarkan nama, kota, atau alamat
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $hotels = $query->paginate(10);

        return response()->json($hotels);
    }

    /**
     * Tampilkan detail hotel beserta tipe kamar dan kamar fisiknya.
     */
    public function showById($id)
    {
        $hotel = Hotel::with(['roomTypes.rooms'])->findOrFail($id);

        return response()->json($hotel);
    }

    /**
     * Tambahkan hotel baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'city'        => 'required|string',
            'address'     => 'required|string',
            'description' => 'nullable|string',
            'rating'      => 'nullable|numeric|min:0|max:5',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data'    => $validator->errors(),
            ], 422);
        }

        $imgUrl = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path      = $image->storeAs('public/hotels', $imageName);
            $imgUrl    = Storage::url($path);
        }

        $hotel = Hotel::create([
            'name'        => $request->input('name'),
            'city'        => $request->input('city'),
            'address'     => $request->input('address'),
            'description' => $request->input('description'),
            'rating'      => $request->input('rating'),
            'img_url'     => $imgUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hotel berhasil ditambahkan',
            'data'    => $hotel,
        ], 201);
    }

    /**
     * Update data hotel berdasarkan ID.
     */
    public function update(Request $request, $id)
    {
        $hotel = Hotel::find($id);

        if (!$hotel) {
            return response()->json([
                'success' => false,
                'message' => 'Hotel tidak ditemukan',
            ], 404);
        }

        // Cek kepemilikan: admin hanya bisa edit hotel miliknya
        $user = $request->user();
        if (!$user->ownsHotel($hotel->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengelola hotel ini',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:255',
            'city'        => 'sometimes|string',
            'address'     => 'sometimes|string',
            'description' => 'nullable|string',
            'rating'      => 'nullable|numeric|min:0|max:5',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data'    => $validator->errors(),
            ], 422);
        }

        $imgUrl = $hotel->img_url;
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($hotel->img_url) {
                $oldImagePath = str_replace('/storage', 'public', $hotel->img_url);
                Storage::delete($oldImagePath);
            }
            $image     = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path      = $image->storeAs('public/hotels', $imageName);
            $imgUrl    = Storage::url($path);
        }

        $dataToUpdate            = $request->only(['name', 'city', 'address', 'description', 'rating']);
        $dataToUpdate['img_url'] = $imgUrl;

        $hotel->update($dataToUpdate);

        return response()->json([
            'success' => true,
            'message' => 'Hotel berhasil diupdate',
            'data'    => $hotel,
        ], 200);
    }

    /**
     * Hapus hotel berdasarkan ID.
     */
    public function destroy(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);

        // Cek kepemilikan: admin hanya bisa hapus hotel miliknya
        $user = $request->user();
        if (!$user->ownsHotel($hotel->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus hotel ini',
            ], 403);
        }

        $hotel->delete();

        return response()->json([
            'message' => 'Hotel deleted successfully',
        ]);
    }
}
