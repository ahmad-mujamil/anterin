<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, $request) {

            // Paksa semua request API balik JSON
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null; // biarkan web error page normal
            }

            // 1) Validation error (422)
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validasi gagal',
                    'errors'  => $e->errors(),
                ], 422);
            }

            // 2) Belum login / token salah (401)
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // 3) Forbidden (403)
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Forbidden',
                ], 403);
            }

            // 4) Model tidak ditemukan (404)
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            if ($e instanceof \Illuminate\Database\RecordNotFoundException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            if ($e instanceof \Illuminate\Database\QueryException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            if ($e instanceof \Illuminate\Database\RecordsNotFoundException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            if ($e instanceof \Symfony\Component\Translation\Exception\NotFoundResourceException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            // 5) Route tidak ditemukan (404)
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Endpoint / Data tidak ditemukan',
                ], 404);
            }

            // 6) Method salah (405)
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Method tidak diizinkan',
                ], 405);
            }

            // 7) HTTP exception lain (mis. 429, 503, dll)
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage() ?: 'Terjadi kesalahan',
                ], $e->getStatusCode());
            }

            // 8) Error umum (500)
            return response()->json([
                'status'  => 'error',
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Terjadi kesalahan pada server',
            ], 500);
        });
    })->create();
