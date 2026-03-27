<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    //product module routes
    Route::post('/farmer/products', [ProductController::class, 'store']);
    Route::get('/farmer/products/listing', [ProductController::class, 'index']);
    Route::post('/vendor/transactions/initiate', [TransactionController::class, 'initiateTransaction']);#
    Route::get('/farmers/{farmer_id}/products', [ProductController::class, 'getFarmProducts']);
});
