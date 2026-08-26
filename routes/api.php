<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\PasswordResetController;
use App\Http\Middleware\SetLocale;


Route::middleware([SetLocale::class])->group(function () {
    
    // المسارات العامة (Public Routes)
    Route::post('/register/company', [AuthController::class, 'register']);
    Route::post('/register/candidate', [AuthController::class, 'registerCandidate']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    // نسيان واستعادة كلمة المرور
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetOtp']);
    Route::post('/verify-reset-otp', [PasswordResetController::class, 'verifyResetOtp']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

    // المسارات المحمية بـ Sanctum (Protected Routes)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAllDevices']);
    });
});
