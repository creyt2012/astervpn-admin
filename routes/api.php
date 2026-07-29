<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ServerController;
use App\Http\Controllers\Api\V1\VmessConfigController;
use App\Http\Controllers\Api\V1\TrafficController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    
    // Subscription endpoint (public, no auth required - token in URL)
    Route::get('/subscription/{token}', [SubscriptionController::class, 'getSubscription']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        
        // User profile & subscription
        Route::get('/user/profile', [UserController::class, 'profile']);
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::get('/user/subscription', [UserController::class, 'subscription']);
        Route::post('/user/subscription/regenerate', [UserController::class, 'regenerateSubscription']);
        Route::get('/user/traffic', [UserController::class, 'traffic']);
        Route::get('/user/devices', [UserController::class, 'devices']);
        Route::delete('/user/devices/{device}', [UserController::class, 'revokeDevice']);
        
        // Admin routes
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            // Users
            Route::apiResource('users', UserController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);
            Route::post('users/{user}/regenerate-subscription', [UserController::class, 'regenerateSubscription']);
            
            // Servers
            Route::apiResource('servers', ServerController::class);
            Route::post('servers/{server}/sync', [ServerController::class, 'sync']);
            
            // VMess Configs
            Route::apiResource('vmess-configs', VmessConfigController::class);
            Route::post('vmess-configs/{config}/test', [VmessConfigController::class, 'testConnection']);
            
            // Traffic & Stats
            Route::get('traffic/summary', [TrafficController::class, 'summary']);
            Route::get('traffic/users/{user}', [TrafficController::class, 'userTraffic']);
        });
    });
});

// Web routes for admin panel (if using SPA)
Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::get('/admin/{any}', function () {
        return view('admin.app');
    })->where('any', '.*');
});