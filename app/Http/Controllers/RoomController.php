<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('roomType.hotel')->get();

        return response()->json([
            'success' => true,
            'message' => 'List Semua Room',
            'data' => $rooms,
        ], 200);
    }

    public function show($id)
    {
        $room = Room::with('roomType.hotel')->find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Room',
            'data' => $room,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors(),
            ], 422);
        }

        $room = Room::create($request->only(['room_type_id', 'room_number', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil ditambahkan',
            'data' => $room,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'room_type_id' => 'sometimes|exists:room_types,id',
            'room_number' => 'sometimes|string|max:255|unique:rooms,room_number,' . $id,
            'status' => 'sometimes|in:available,occupied,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors(),
            ], 422);
        }

        $room->update($request->only(['room_type_id', 'room_number', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil diupdate',
            'data' => $room,
        ], 200);
    }

    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan',
            ], 404);
        }

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil dihapus',
        ], 200);
    }
}