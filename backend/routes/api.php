<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function() {
    Route::post('/getOtp', [AuthController::class, 'getOtp']);
    Route::post('/checkOtp', [AuthController::class, 'checkOtp']);

    Route::post('/complete_profile', [AuthController::class, 'completeProfile'])->middleware('auth:api');

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    
    Route::post('/refresh', [AuthController::class, 'refresh']);
});