<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class AccountController extends BaseController
{
    /**
     * Tampilkan daftar semua user dengan filter & pagination.
     * Query params: ?role=admin&search=john&hotel_id=1&page=1
     */
    public function index(Request $request)
    {
        $query = User::with('hotel');

        // Filter berdasarkan role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Pencarian berdasarkan nama atau email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan hotel_id
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    /**
     * Tampilkan detail satu user berdasarkan ID.
     */
    public function show($id)
    {
        $user = User::with('hotel')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    /**
     * Update data user (name, email, password, role, hotel_id).
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        // Proteksi: Super Admin tidak bisa mengubah role dirinya sendiri
        $currentUser = $request->user();
        if ((int) $currentUser->id === (int) $user->id && $request->filled('role') && $request->role !== $currentUser->role) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat mengubah role akun Anda sendiri',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'role'     => 'sometimes|in:super-admin,admin,customer',
            'hotel_id' => 'nullable|exists:hotels,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Jika role diubah ke admin, hotel_id wajib diisi
        $newRole = $request->input('role', $user->role);
        if ($newRole === 'admin' && !$request->input('hotel_id', $user->hotel_id)) {
            return response()->json([
                'success' => false,
                'message' => 'hotel_id wajib diisi untuk role admin',
            ], 422);
        }

        // Update data
        $dataToUpdate = $request->only(['name', 'email', 'role']);

        // Hash password jika diubah
        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        // Set hotel_id berdasarkan role
        if ($request->has('role')) {
            if ($newRole === 'admin') {
                $dataToUpdate['hotel_id'] = $request->input('hotel_id', $user->hotel_id);
            } else {
                // customer dan super-admin tidak perlu hotel_id
                $dataToUpdate['hotel_id'] = null;
            }
        } elseif ($request->has('hotel_id')) {
            $dataToUpdate['hotel_id'] = $request->hotel_id;
        }

        $user->update($dataToUpdate);
        $user->load('hotel');

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate',
            'data'    => $user,
        ]);
    }

    /**
     * Hapus user berdasarkan ID.
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        // Proteksi: Super Admin tidak bisa menghapus dirinya sendiri
        $currentUser = $request->user();
        if ((int) $currentUser->id === (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus',
        ]);
    }
}
