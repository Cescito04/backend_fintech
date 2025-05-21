<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CardController;
use Illuminate\Support\Facades\Route;

// Routes d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [AuthController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Routes de gestion du profil utilisateur
    Route::put('/users/{user}', [AuthController::class, 'update']);
    Route::delete('/users/{user}', [AuthController::class, 'destroy']);

    // Routes des transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/recharge', [TransactionController::class, 'recharge']);
    Route::post('/transfer', [TransactionController::class, 'transfer']);
    Route::get('/balance', [TransactionController::class, 'balance']);

    // Routes pour la carte virtuelle
    Route::get('/card', [CardController::class, 'getCardInfo']);
});
