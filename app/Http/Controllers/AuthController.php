<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        // 1. Simpan User baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 2. Cari Role 'customer'
        $customerRole = Role::where('name', 'customer')->first();

        // 3. Hubungkan User ke Role melalui tabel pivot role_user
        if ($customerRole) {
            $user->roles()->attach($customerRole->id);
        }

        return response()->json([
            'message' => 'Registrasi berhasil!',
            'user' => $user->with('roles')->find($user->id)
        ], 201);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'password']);

        // Mencoba login dan generate token
        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Email atau Password salah'], 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => Auth::user()->load('roles'), // Sertakan info role di respon
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}