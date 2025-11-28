<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HoldController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;

Route::prefix('products')->group(function () {
    Route::get('{id}', [ProductController::class, 'show']);
});

Route::prefix('holds')->group(function () {
    Route::post('/', [HoldController::class, 'store']);
});

Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
});

Route::post('/payments/webhook', [PaymentWebhookController::class, 'handle']);

