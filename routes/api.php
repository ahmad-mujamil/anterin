<?php

use App\Http\Controllers\Api\LoginController;
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

    Route::apiResource('tarif', TarifController::class);
    Route::post('/hitung-tarif', [TarifController::class, 'calculate']);
});
