<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DiklatController;
use Illuminate\Support\Facades\Route;

/*
| Endpoint API diberi versi agar perubahan di masa depan dapat dibuat
| tanpa merusak aplikasi Android yang sudah menggunakan v1.
*/
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::apiResource('diklat', DiklatController::class);
    });
});
