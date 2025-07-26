
// use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemTransactionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;

// Route::get('/', function () {
//     return response()->json(['message' => 'API Service is running.']);
// });

// // Grup untuk otentikasi
// Route::group(['prefix' => 'auth'], function () {
//     Route::post('/login', [AuthController::class, 'login']);
//     Route::post('/register', [AuthController::class, 'register']);

//     Route::group(['middleware' => 'auth:api'], function() {
//         Route::post('/logout', [AuthController::class, 'logout']);
//         Route::post('/refresh', [AuthController::class, 'refresh']);
//         Route::get('/user-profile', [AuthController::class, 'userProfile']);
//     });
// });

// Route::get('/dashboard', function () {
//     return response()->json(['message' => 'Welcome to Dashboard!']);
// })->middleware('auth:api');

// Route::apiResource('/roles', RoleController::class)->middleware('auth:api');

// Route::apiResource('/users', UserController::class)->middleware(['auth:api', 'role:Administrator']);


// Route::get('/categories/{category}/history', [CategoryController::class, 'history'])->middleware('auth:api');
// Route::get('/items/{item}/history', [ItemController::class, 'history'])->middleware('auth:api');
// Route::get('/item-transactions/{item_transaction}/history', [ItemTransactionController::class, 'history'])->middleware('auth:api');


// Route::apiResource('/categories', CategoryController::class)->middleware('auth:api');

// Route::apiResource('/items', ItemController::class)->middleware('auth:api');

// Route::apiResource('/item-transactions', ItemTransactionController::class)->only(['index', 'store', 'show'])->middleware('auth:api');

// Route::get('category-options', function() {
//     // Ambil hanya ID dan Nama dari kategori yang aktif
//     $categories = \App\Models\Category::where('is_active', true)->select('id', 'name')->get();
//     return response()->json($categories);
// });

// Route::post('/items/export', [ItemController::class, 'export'])->middleware('auth:api');

// Route::post('/items/import', [ItemController::class, 'import'])->middleware('auth:api');

// Route::post('/categories/export', [CategoryController::class, 'export'])->middleware('auth:api');
// Route::post('/categories/import', [CategoryController::class, 'import'])->middleware('auth:api');

// // Rute Export untuk Item Transactions
// Route::post('/item-transactions/export', [ItemTransactionController::class, 'export'])->middleware('auth:api');


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
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:api')->group(function () {
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
