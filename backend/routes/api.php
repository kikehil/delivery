<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\CouponController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/register-partner', [AuthController::class, 'registerPartner'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
});

// Store & Product Routes (Public)
Route::get('/zones', [StoreController::class, 'getZones']);
Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/{id}', [StoreController::class, 'show']);
Route::get('/stores/{id}/products', [ProductController::class, 'getByStore']);
Route::get('/productos', [ProductoController::class, 'index']);

// Public promotions
Route::get('/promotions', [App\Http\Controllers\Api\PromocionController::class, 'index']);
Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);

// Orders
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}/status', [OrderController::class, 'status']);

// User Routes (Logged in)
Route::group(['middleware' => 'auth:api'], function () {
    
    // Admin Only
    Route::group(['prefix' => 'admin', 'middleware' => 'role:admin'], function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\AdminController::class, 'getDashboard']);
        Route::get('/businesses', [\App\Http\Controllers\Api\AdminController::class, 'getBusinesses']);
        Route::put('/businesses/{id}/status', [\App\Http\Controllers\Api\AdminController::class, 'updateBusinessStatus']);
        
        // Admin Promotions
        Route::get('/promotions', [App\Http\Controllers\Api\PromocionController::class, 'adminIndex']);
        Route::post('/promotions', [App\Http\Controllers\Api\PromocionController::class, 'store']);
        Route::put('/promotions/{id}', [App\Http\Controllers\Api\PromocionController::class, 'update']);
        Route::delete('/promotions/{id}', [App\Http\Controllers\Api\PromocionController::class, 'destroy']);
        
        Route::get('/users', function() {
            return response()->json(['data' => \App\Models\User::all()]);
        });

        // Liquidaciones
        Route::get('/payments', [\App\Http\Controllers\Api\LiquidacionController::class, 'index']);
        Route::post('/payments', [\App\Http\Controllers\Api\LiquidacionController::class, 'store']);
        Route::put('/payments/{id}/status', [\App\Http\Controllers\Api\LiquidacionController::class, 'updateStatus']);
    });

    // Socio (Partner) Only
    Route::group(['prefix' => 'partner', 'middleware' => 'role:socio'], function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\PartnerController::class, 'getDashboard']);
        
        // Products
        Route::get('/products', [\App\Http\Controllers\Api\PartnerController::class, 'getProducts']);
        Route::post('/products', [\App\Http\Controllers\Api\PartnerController::class, 'storeProduct']);
        Route::put('/products/{id}', [\App\Http\Controllers\Api\PartnerController::class, 'updateProduct']);
        Route::delete('/products/{id}', [\App\Http\Controllers\Api\PartnerController::class, 'deleteProduct']);
        Route::post('/products/{id}/toggle', [\App\Http\Controllers\Api\PartnerController::class, 'toggleProduct']);
        
        // Orders
        Route::get('/orders', [\App\Http\Controllers\Api\PartnerController::class, 'getOrders']);
        Route::put('/orders/{id}/status', [\App\Http\Controllers\Api\PartnerController::class, 'updateOrderStatus']);

        // Settings
        Route::get('/settings', [\App\Http\Controllers\Api\PartnerController::class, 'getSettings']);
        Route::put('/settings', [\App\Http\Controllers\Api\PartnerController::class, 'updateSettings']);

        // Liquidaciones del socio
        Route::get('/payments', [\App\Http\Controllers\Api\LiquidacionController::class, 'partnerLiquidaciones']);
    });

    // Cliente Only
    Route::group(['prefix' => 'customer', 'middleware' => 'role:cliente'], function () {
        Route::get('/orders', function () {
            return response()->json(['message' => 'Historial de tus pedidos']);
        });
    });

    // Generic Upload
    Route::post('/upload', [\App\Http\Controllers\Api\UploadController::class, 'upload']);
});
