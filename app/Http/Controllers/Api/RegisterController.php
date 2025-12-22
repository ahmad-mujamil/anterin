<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterDriverRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function registerUser(RegisterUserRequest $request) : \Illuminate\Http\JsonResponse
    {

        try {
            $validated = $request->validated();
            $user = User::create([
                'nama'     => $validated['nama'],
                'email'    => $validated['email'],
                'role'     => 'user',
                'password' => Hash::make($validated['password']),
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'message' => 'Registrasi user berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);

        }catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }

    }

    public function registerDriver(RegisterDriverRequest $request) : \Illuminate\Http\JsonResponse
    {

        try {
            $validated = $request->validated();
            DB::beginTransaction();
            $user = User::create([
                'nama'     => $validated['nama'],
                'email'    => $validated['email'],
                'role'     => 'driver',
                'password' => Hash::make($validated['password']),
            ]);

            Driver::query()->create([
                'user_id' => $user->id,
                'no_polisi'   => $validated['no_polisi'],
                'merek_kendaraan' => $validated['merek_kendaraan'],
                'jenis_kendaraan'  => $validated['jenis_kendaraan'] ?? null,
                'is_available'   => false,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Registrasi driver berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
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
}
