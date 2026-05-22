<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\RepaymentController;
use App\Http\Controllers\API\MpesaCallbackController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// M-Pesa async callbacks — unauthenticated, called directly by M-Pesa
Route::post('/mpesa/callback/disbursement', [MpesaCallbackController::class, 'disbursement']);
Route::post('/mpesa/callback/repayment',    [MpesaCallbackController::class, 'repayment']);

Route::middleware('auth:sanctum')->group(function () {

    // Products
    Route::post('/farmer/products',          [ProductController::class, 'store']);
    Route::get('/farmer/products/listing',   [ProductController::class, 'index']);
    Route::get('/farmers/{farmer_id}/products', [ProductController::class, 'getFarmProducts']);

    // Transactions
    Route::post('/vendor/transactions/initiate',  [TransactionController::class, 'initiateTransaction']);
    Route::get('/vendor/purchases',               [TransactionController::class, 'purchases']);

    // Repayments (vendor)
    Route::get('/vendor/installments',                    [RepaymentController::class, 'index']);
    Route::post('/vendor/installments/{installment}/pay', [RepaymentController::class, 'pay']);
    Route::get('/vendor/payment-logs',                    [RepaymentController::class, 'paymentLogs']);

    // Notifications
    Route::get('/notifications', function () {
        return response()->json([
            'notifications' => auth()->user()->unreadNotifications,
        ]);
    });
    Route::post('/notifications/{id}/read', function (string $id) {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        return response()->json(['message' => 'Marked as read.']);
    });
});
