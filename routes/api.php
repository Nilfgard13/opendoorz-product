<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\Api\ProductController;

// Route::post('/products/upload-image', [ProductController::class, 'storeImage']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Mendapatkan data user yang sedang login berdasarkan token
    Route::get('user', [AuthController::class, 'getUserData']);

    // Mendapatkan semua data user
    Route::get('users', [UserController::class, 'index']);

    Route::post('users/create', [UserController::class, 'store']);

    // Mendapatkan data user berdasarkan ID
    Route::get('users/search/{id}', [UserController::class, 'show']);

    // Update data user berdasarkan ID
    Route::put('users/update/{id}', [UserController::class, 'update']);

    // Delete data user berdasarkan ID
    Route::delete('users/delete/{id}', [UserController::class, 'destroy']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products/create', [ProductController::class, 'store']);
    Route::get('/products/search/{title?}/{location?}/{status?}', [ProductController::class, 'search']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/{product}/upload-images', [ProductController::class, 'uploadImages']);
});

// Route untuk register dan login (tidak perlu autentikasi)
Route::post('register', [AuthController::class, 'registerUser']);
Route::post('login', [AuthController::class, 'loginUser'])->name('login');
