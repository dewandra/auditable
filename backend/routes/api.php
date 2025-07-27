<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemTransactionController;

// Public route
Route::get('/', fn () => response()->json(['message' => 'API Service is running.']));

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });
});

// Optional: if public access allowed
Route::get('/category-options', function () {
    $categories = \App\Models\Category::where('is_active', true)
        ->select('id', 'name')
        ->get();
    return response()->json($categories);
});

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Dashboard
    Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome to Dashboard!']));

    // Role & user (admin only for users)
    Route::apiResource('/roles', RoleController::class);
    Route::apiResource('/users', UserController::class)->middleware('role:Administrator');

    // Categories
    Route::apiResource('/categories', CategoryController::class);
    Route::get('/categories/{category}/history', [CategoryController::class, 'history']);
    Route::post('/categories/import', [CategoryController::class, 'import']);
    Route::post('/categories/export', [CategoryController::class, 'export']);

    // Items
    Route::apiResource('/items', ItemController::class);
    Route::get('/items/{item}/history', [ItemController::class, 'history']);
    Route::post('/items/import', [ItemController::class, 'import']);
    Route::post('/items/export', [ItemController::class, 'export']);

    // Item Transactions
    Route::apiResource('/item-transactions', ItemTransactionController::class)->only(['index', 'store', 'show']);
    Route::get('/item-transactions/{item_transaction}/history', [ItemTransactionController::class, 'history']);
    Route::post('/item-transactions/export', [ItemTransactionController::class, 'export']);
});
