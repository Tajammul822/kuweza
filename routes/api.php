<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    //product module routes
    Route::post('/farmer/products', [ProductController::class, 'store']);
    Route::get('/farmer/products/listing', [ProductController::class, 'index']);
});
