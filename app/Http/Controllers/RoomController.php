<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class RoomController extends BaseController
{
    /**
     * Tampilkan daftar kamar dengan filter opsional.
     * Query params: ?status=available&room_type_id=1
     */
    public function index(Request $request)
    {
        $query = Room::with(['roomType.hotel']);

        if ($request->filled(['check_in', 'check_out'])) {
            $this->validate($request, [
                'check_in'  => 'date|date_format:Y-m-d',
                'check_out' => 'date|date_format:Y-m-d|after:check_in',
            ]);
            $query->availableForDates($request->check_in, $request->check_out);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        // Filter berdasarkan hotel_id
        if ($request->filled('hotel_id')) {
            $query->whereHas('roomType', function ($q) use ($request) {
                $q->where('hotel_id', $request->hotel_id);
            });
        }

        $rooms = $query->paginate(10);

        return response()->json($rooms);
    }

    /**
     * Tampilkan detail kamar berdasarkan ID.
     */
    public function show($id)
    {
        $room = Room::with(['roomType.hotel'])->findOrFail($id);
        return response()->json($room);
    }

    /**
     * Tambahkan kamar baru.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'room_type_id' => 'required|exists:room_types,id',
            'room_number'  => 'required|string|max:255',
            'status'       => 'required|in:available,occupied,maintenance',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Cek kepemilikan: admin hanya bisa tambah kamar di hotel miliknya
        $roomType = RoomType::findOrFail($request->room_type_id);
        $user = $request->user();
        if (!$user->ownsHotel($roomType->hotel_id)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mengelola kamar di hotel ini',
            ], 403);
        }

        // Proses upload gambar
        $imgUrl = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destDir   = app()->basePath('public') . '/storage/rooms';

            if (!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $image->move($destDir, $imageName);
            $imgUrl = '/storage/rooms/' . $imageName;
        }

        $room = Room::create([
            'room_type_id' => $request->room_type_id,
            'room_number'  => $request->room_number,
            'status'       => $request->status,
            'description'  => $request->description,
            'price'        => $request->price,
            'img_url'      => $imgUrl,
        ]);

        return response()->json([
            'message' => 'Room created successfully',
            'room'    => $room,
        ], 201);
    }

    /**
     * Update data kamar berdasarkan ID.
     */
    public function update(Request $request, $id)
    {
        $room = Room::with('roomType')->findOrFail($id);

        // Cek kepemilikan: admin hanya bisa edit kamar di hotel miliknya
        $user = $request->user();
        if (!$user->ownsHotel($room->roomType->hotel_id)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mengelola kamar di hotel ini',
            ], 403);
        }

        $this->validate($request, [
            'room_type_id' => 'sometimes|required|exists:room_types,id',
            'room_number'  => 'sometimes|required|string|max:255',
            'status'       => 'sometimes|required|in:available,occupied,maintenance',
            'description'  => 'nullable|string',
            'price'        => 'sometimes|required|numeric|min:0',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $dataToUpdate = $request->only([
            'room_type_id', 'room_number', 'status', 'description', 'price',
        ]);

        // Proses upload gambar baru jika ada
        $imageFile = $request->file('image');
        if ($imageFile) {
            // Hapus gambar lama jika ada
            if ($room->img_url) {
                $oldPath = app()->basePath('public') . $room->img_url;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $imageName              = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $destDir                = app()->basePath('public') . '/storage/rooms';

            if (!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $imageFile->move($destDir, $imageName);
            $dataToUpdate['img_url'] = '/storage/rooms/' . $imageName;
        }

        $room->update($dataToUpdate);

        return response()->json([
            'message' => 'Room updated successfully',
            'room'    => $room,
        ]);
    }

    /**
     * Hapus kamar berdasarkan ID.
     */
    public function destroy(Request $request, $id)
    {
        $room = Room::with('roomType')->findOrFail($id);

        // Cek kepemilikan: admin hanya bisa hapus kamar di hotel miliknya
        $user = $request->user();
        if (!$user->ownsHotel($room->roomType->hotel_id)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk menghapus kamar di hotel ini',
            ], 403);
        }

        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully',
        ]);
    }
}