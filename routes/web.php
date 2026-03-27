<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckAdmin;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoanPaymentRuleController;
use App\Http\Controllers\TransactionController;

Route::redirect('/', '/login-view');

Route::get('/login-view', [AuthController::class, 'showLoginForm'])->name('login-view');
Route::post('/login', [AuthController::class, 'login'])->name('loggedIn');

Route::middleware(['auth', CheckAdmin::class])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'user_index'])->name('users.index');
    Route::resource('admin/rules', LoanPaymentRuleController::class)->names('admin.rules');
    Route::get('/admin/transactions', [TransactionController::class, 'index'])->name('admin.transactions.index');
    Route::get('/admin/transactions/{transaction}', [TransactionController::class, 'show'])->name('admin.transactions.show');
    Route::post('/admin/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('admin.transactions.approve');
});
