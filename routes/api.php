<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Routes d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [AuthController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Routes des transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/recharge', [TransactionController::class, 'recharge']);
    Route::post('/transfer', [TransactionController::class, 'transfer']);
    Route::get('/balance', [TransactionController::class, 'balance']);
});
