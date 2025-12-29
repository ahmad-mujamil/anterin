<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenggunaRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PenggunaController extends Controller
{
    public function index() : JsonResponse
    {
        $users  = User::query()
            ->where("role","!=","admin")
            ->latest()
            ->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Data pengguna berhasil diambil',
            'data' => $users->toArray()
        ]);
    }

    public function store(PenggunaRequest $request) : JsonResponse
    {
        try {
            DB::beginTransaction();
            User::query()->create($request->validated()+["role"=>"user"]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil ditambahkan',
                'data' => Arr::except($request->validated(),'password')
            ]);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ]);
        }

    }

    public function show(User $user) : JsonResponse
    {
        $user->load('driver');

        return response()->json([
            'status' => 'success',
            'message' => 'Detail user berhasil diambil',
            'data' => [
                "nama" => $user->nama,
                "email" => $user->email,
                "role" => $user->role

            ]
        ]);
    }

    public function update(PenggunaRequest $request,User $user) : JsonResponse
    {
        try {
            DB::beginTransaction();
            $user->update($request->validated());
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil di update',
                'data' => Arr::except($request->validated(),'password')
            ]);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ]);
        }
    }


    public function destroy($id) : JsonResponse
    {

        try {
            if(!$user = User::find($id)) return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan',
                'data' => []
            ],404);
            DB::beginTransaction();
            $user->update(['is_active' => false]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil di non aktifkan',
                'data' => [
                    'nama' => $user->nama,
                    'non_active_at' => now()->toDateTimeString()
                ]
            ]);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ]);
        }


    }

}
