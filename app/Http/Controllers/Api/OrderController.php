<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Tarif;
use App\Services\CalculationServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request) : JsonResponse
    {
        $orders  = Order::query()
            ->where("user_id",auth()->id())
            ->latest()
            ->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Data order berhasil diambil',
            'data' => $orders->toArray()
        ]);
    }

    public function store(OrderRequest $request) : JsonResponse
    {
        try {
            DB::beginTransaction();
            $calculationServices = new CalculationServices();
            $hitungJarak = $calculationServices::calculateDistance(
                $request->pickup_lat,
                $request->pickup_lng,
                $request->destination_lat,
                $request->destination_lng
            );

            $hitungTarif = $calculationServices::calculateTarif($hitungJarak['distance_km']);
            $mergeRequest = $request->validated()+[
                    "distance_km" => $hitungTarif['charged_km'],
                    "total_tarif" => $hitungTarif['total_tarif'],
                    "tarif_dasar" => $hitungTarif['tarif_dasar'],
                    "tarif_per_km" => $hitungTarif['tarif_per_km'],
                ];

            Order::query()->create($mergeRequest);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil ditambahkan',
                'data' => $mergeRequest
            ]);
        }catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ]);
        }

    }

    public function show(Order $order) : JsonResponse
    {
        $order->load(['user','driver.user']);
        return response()->json([
            'status' => 'success',
            'message' => 'Detail order berhasil diambil',
            'data' => [
                "no_order" => $order->no_order,
                "created_at" => $order->created_at->toDateTimeString(),
                "status" => $order->status,
                "driver" => $order->driver->user->nama??'-',
                "no_polisi" => $order->driver->no_polisi??'-',
                "user" => $order->user->nama??'-'

            ]
        ]);
    }

    public function accept(Request $request,Order $order) : JsonResponse
    {
        try {
            $request->validate([
                'driver_id' => ['required','exists:driver,id,is_available,1']
            ]);

            if($order->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status order tidak valid',
                    'data' => []
                ]);
            }

            DB::beginTransaction();
            $order->update([
                'status' => 'accepted',
                'driver_id' => $request->driver_id,
                'accepted_at' => now()->toDateTimeString(),
            ]);

            Driver::query()->where('id',$request->driver_id)->update(['is_available' => 0]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil diterima',
                'data' => [
                    "no_order" => $order->no_order,
                    "driver_id" => $request->driver_id,
                    "accepted_at" => now()->toDateTimeString(),
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

    public function pickup(Request $request,Order $order) : JsonResponse
    {
        try {
            $request->validate([
                'driver_id' => ['required','exists:driver,id,is_available,0']
            ]);

            if($order->driver_id != $request->driver_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Driver tidak valid',
                    'data' => []
                ]);
            }

            if($order->status !== 'accepted') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status Order tidak valid',
                    'data' => []
                ]);
            }


            DB::beginTransaction();
            $order->update([
                'status' => 'on_delivery',
                'picked_up_at' => now()->toDateTimeString(),
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Customer berhasil dipickup',
                'data' => [
                    "no_order" => $order->no_order,
                    "picked_up_at" => now()->toDateTimeString(),
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

    public function completed(Request $request,Order $order) : JsonResponse
    {
        try {
            $request->validate([
                'driver_id' => ['required','exists:driver,id,is_available,0']
            ]);

            if($order->driver_id != $request->driver_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Driver tidak valid',
                    'data' => []
                ]);
            }

            if($order->status !== 'on_delivery') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status Order tidak valid',
                    'data' => []
                ]);
            }

            DB::beginTransaction();
            $order->update([
                'status' => 'complete',
                'completed_at' => now()->toDateTimeString(),
            ]);

            Driver::query()->where('id',$request->driver_id)->update(['is_available' => 1]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil diselesaikan',
                'data' => [
                    "no_order" => $order->no_order,
                    "completed_at" => now()->toDateTimeString(),
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


    public function destroy(Request $request,Order $order) : JsonResponse
    {


        try {
            $request->validate([
                "user_id" => "required|exists:users,id|exists:order,user_id,id,".($order->id??'')
            ]);

            if(!$order->status !== 'pending' ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order tidak dapat dibatalkan',
                    'data' => []
            ]);
            }

            DB::beginTransaction();

                $order->update(
                    [
                        'status' => 'cancel',
                        'cancelled_at' => now()->toDateTimeString(),
                    ]
                );
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil dibatalkan',
                'data' => [
                    'no_order' => $order->no_order,
                    'cancelled_at' => now()->toDateTimeString()
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
