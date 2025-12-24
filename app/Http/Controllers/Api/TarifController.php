<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HitungTarifRequest;
use App\Http\Requests\TarifRequest;
use App\Models\Tarif;
use App\Services\CalculationServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifController extends Controller
{
    public function index(Request $request) : JsonResponse
    {
        $tarif = Tarif::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Data tarif berhasil diambil',
            'data' => $tarif->toArray()
        ]);
    }

    public function store(TarifRequest $request) : JsonResponse
    {
        try {
            DB::beginTransaction();
            Tarif::query()->create($request->validated());
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Tarif berhasil ditambahkan',
                'data' => $request->validated()
            ]);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ]);
        }

    }

    public function update(TarifRequest $request,Tarif $tarif) : JsonResponse
    {
        try {
            DB::beginTransaction();
            $tarif->update($request->validated());
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Tarif berhasil diupdate',
                'data' => $request->validated()
            ]);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ]);
        }
    }

    public function show(Tarif $tarif) : JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Data tarif berhasil diambil',
            'data' => $tarif->toArray()
        ]);
    }

    public function destroy(Tarif $tarif) : JsonResponse
    {
        try {
            $tarif->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Tarif berhasil dihapus',
                'data' => $tarif->toArray()
            ]);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ]);
        }
    }

    public function calculate(HitungTarifRequest $request) : JsonResponse
    {
        try{

            $calculationService = new CalculationServices();
            $hitungJarak = $calculationService::calculateDistance(
                $request->pickup_lat,
                $request->pickup_lng,
                $request->destination_lat,
                $request->destination_lng
            );

            $hitungTarif = $calculationService::calculateTarif($hitungJarak["distance_km"]);

            return response()->json([
                'status' => 'success',
                'message' => 'Hitung tarif berhasil',
                'data' => [
                    'jarak' => $hitungTarif["charged_km"],
                    "tarif_dasar" => $hitungTarif["tarif_dasar"],
                    "tarif_per_km" => $hitungTarif["tarif_per_km"],
                    'total_tarif' => $hitungTarif["total_tarif"],
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
