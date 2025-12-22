<?php

namespace App\Services;

use App\Models\Tarif;
use Illuminate\Support\Facades\Http;

class CalculationServices
{

    public static function calculateDistance(
        float $pickup_lat,
        float $pickup_lng,
        float $dest_lat,
        float $dest_lng,
        string $profile = 'driving'
    ) : array
    {
        $url = "https://router.project-osrm.org/route/v1/{$profile}/{$pickup_lng},{$pickup_lat};{$dest_lng},{$dest_lat}";

        $response = Http::get($url, [
            'overview' => 'false',
            'alternatives' => 'false',
            'steps' => 'false',
        ]);

        if (!$response->successful()) {
            return [
                'status' => "error",
                'message' => 'Gagal menghubungi OSRM',
            ];
        }

        $data = $response->json();

        if (($data['code'] ?? '') !== 'Ok') {
            return [
                'status' => "error",
                'message' => 'Routing tidak ditemukan',
            ];
        }

        $route = $data['routes'][0];

        return [
            'status' => "success",
            'distance_km' => round($route['distance'] / 1000, 2),
            'duration_minutes' => (int) ceil($route['duration'] / 60),
        ];
    }

    public static function calculateTarif(float $distance_km) : int
    {
        $tarif = Tarif::query()->where("is_active",true)->latest()->first();

        $chargedKm = max((int) $tarif->min_km, (int) ceil($distance_km));

        return (int) $tarif->harga_dasar + ($chargedKm * (int) $tarif->harga_per_km);
    }
}
