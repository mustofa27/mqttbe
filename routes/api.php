<?php

use App\Http\Controllers\MqttAuthController;
use App\Http\Controllers\MqttAclController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaypoolWebhookController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\AnalyticsController;

Route::post('/mqtt/auth', [MqttAuthController::class, 'auth']);
Route::post('/mqtt/acl', [MqttAclController::class, 'acl']);

// Paypool webhook (no auth required)
Route::post('/paypool/webhook', [PaypoolWebhookController::class, 'handle']);

// Subscription API endpoints (protected by auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/subscription/limits', [SubscriptionController::class, 'limits']);
});

// Analytics API endpoints (protected by auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/analytics/project/{project}', [AnalyticsController::class, 'projectData']);
    Route::get('/analytics/project/{project}/device/{device}', [AnalyticsController::class, 'deviceAnalytics']);
});

// API v1 routes (with Bearer token authentication)
Route::prefix('v1')->middleware(['api', 'validate.api.key', 'enforce.plan.limits'])->group(function () {
    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // Devices
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::post('/devices', [DeviceController::class, 'store']);
    Route::get('/devices/{device}', [DeviceController::class, 'show']);
    Route::put('/devices/{device}', [DeviceController::class, 'update']);
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy']);

    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

    // API Keys management
    Route::get('/api-keys', [ApiKeyController::class, 'index']);
    Route::post('/api-keys', [ApiKeyController::class, 'store']);
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy']);
    Route::post('/api-keys/{apiKey}/deactivate', [ApiKeyController::class, 'deactivate']);

    // Advanced filtering and analytics
    Route::prefix('filter')->name('api.filter.')->group(function () {
        Route::get('/project/{project}/messages', [FilterController::class, 'messages'])->name('messages');
        Route::get('/project/{project}/options', [FilterController::class, 'options'])->name('options');
        Route::get('/project/{project}/summary', [FilterController::class, 'summary'])->name('summary');
        Route::get('/project/{project}/device-activity', [FilterController::class, 'deviceActivity'])->name('device-activity');
        Route::get('/project/{project}/time-series', [FilterController::class, 'timeSeries'])->name('time-series');
    });
});
