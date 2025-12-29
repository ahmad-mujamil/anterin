<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PenggunaController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\TarifController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'registerUser']);
Route::post('/register-driver', [RegisterController::class, 'registerDriver']);

Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',[LoginController::class, 'me']);
    Route::post('/logout',[LoginController::class, 'logout']);

    Route::apiResource('pengguna', PenggunaController::class);

    Route::apiResource('tarif', TarifController::class);
    Route::post('/hitung-tarif', [TarifController::class, 'calculate']);

    Route::apiResource('order', OrderController::class)->except('update');
    Route::post('/order/{order}/accepted', [OrderController::class, 'accept']);
    Route::post('/order/{order}/picked-up', [OrderController::class, 'pickup']);
    Route::post('/order/{order}/completed', [OrderController::class, 'completed']);
});
