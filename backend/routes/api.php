<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/getOtp', [AuthController::class, 'getOtp']);
Route::post('/checkOtp', [AuthController::class, 'checkOtp']);
Route::post('/complete_profile', [AuthController::class, 'CompleteProfile'])->middleware('auth:api');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');