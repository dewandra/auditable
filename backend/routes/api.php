<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;

Route::get('/', function () {
    return response()->json(['message' => 'API Service is running.']);
});

// Grup untuk otentikasi
Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::group(['middleware' => 'auth:api'], function() {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });
});

// Soal 1.2: Dashboard, hanya bisa diakses setelah login
Route::get('/dashboard', function () {
    return response()->json(['message' => 'Welcome to Dashboard!']);
})->middleware('auth:api');

// Soal 1.3: CRUD Role, hanya bisa diakses setelah login
Route::apiResource('/roles', RoleController::class)->middleware('auth:api');

// Soal 1.4: CRUD User, hanya bisa diakses oleh 'Administrator'
Route::apiResource('/users', UserController::class)->middleware(['auth:api', 'role:Administrator']);