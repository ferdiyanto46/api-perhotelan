<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Default role for new registrations is 'customer'
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer', 
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // Gunakan guard 'api' secara eksplisit
        if ($token = auth('api')->attempt($credentials)) {
            $user = auth('api')->user();
            /** @var \Tymon\JWTAuth\JWTGuard $auth */
            $auth = auth('api');

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'role' => $user->role,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $auth->factory()->getTTL() * 60
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function registerAdmin(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,super-admin',
            'hotel_id' => 'nullable|exists:hotels,id',
        ]);

        // Jika role admin, hotel_id wajib diisi
        if ($request->role === 'admin' && !$request->hotel_id) {
            return response()->json([
                'message' => 'hotel_id wajib diisi untuk role admin',
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'hotel_id' => $request->role === 'admin' ? $request->hotel_id : null,
        ]);

        return response()->json([
            'message' => 'Admin registered successfully',
            'user' => $user
        ], 201);
    }
}