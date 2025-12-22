<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request) : JsonResponse
    {

        try {

            $validated = $request->validate([
                'email'    => ['required','email'],
                'password' => ['required','string'],
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email atau password salah',
                    'data' => []
                ], 401);
            }

            if ($user->is_active === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun tidak aktif',
                    'data' => []
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil',
                'data' => [
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ]);

        }catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }

    }

    public function logout(Request $request) : JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }

    public function me(Request $request) : JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role
            ],
        ]);
    }
}
