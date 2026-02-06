<?php

use App\Http\Controllers\MqttAuthController;
use App\Http\Controllers\MqttAclController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaypoolWebhookController;

Route::post('/mqtt/auth', [MqttAuthController::class, 'auth']);
Route::post('/mqtt/acl', [MqttAclController::class, 'acl']);

// Paypool webhook (no auth required)
Route::post('/paypool/webhook', [PaypoolWebhookController::class, 'handle']);

// Subscription API endpoints (protected by auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/subscription/limits', [SubscriptionController::class, 'limits']);
});
