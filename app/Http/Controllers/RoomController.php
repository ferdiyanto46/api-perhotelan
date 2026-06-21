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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
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
            'img_url'      => 'nullable|url',
        ]);

        // Cek kepemilikan: admin hanya bisa tambah kamar di hotel miliknya
        $roomType = RoomType::findOrFail($request->room_type_id);
        $user = $request->user();
        if (!$user->ownsHotel($roomType->hotel_id)) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mengelola kamar di hotel ini',
            ], 403);
        }

        $room = Room::create($request->only([
            'room_type_id', 'room_number', 'status',
            'description', 'price', 'img_url',
        ]));

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
            'img_url'      => 'nullable|url',
        ]);

        $room->update($request->only([
            'room_type_id', 'room_number', 'status',
            'description', 'price', 'img_url',
        ]));

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