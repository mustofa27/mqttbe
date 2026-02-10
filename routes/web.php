<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SubscriptionPlanController;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/kebijakan', 'legal.policies')->name('legal.policies');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // Password Reset Routes
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('updateProfile');
        // Regenerate project secret
        Route::post('/projects/{project}/regenerate-secret', [ProjectController::class, 'regenerateSecret'])->name('projects.regenerate-secret');
    Route::delete('/profile', [DashboardController::class, 'deleteAccount'])->name('deleteAccount');

    // Project CRUD routes
    Route::resource('projects', ProjectController::class);

    // Device CRUD routes
    Route::resource('devices', DeviceController::class);

    // Topic CRUD routes
    Route::resource('topics', TopicController::class);

    // Permission CRUD routes
    Route::resource('permissions', PermissionController::class);

    // Usage and Analytics routes
    Route::get('/usage', [UsageController::class, 'dashboard'])->name('usage.dashboard');
    Route::get('/usage/project/{project}', [UsageController::class, 'projectUsage'])->name('usage.project');

    // API Key management routes
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::get('/api-keys/{apiKey}', [ApiKeyController::class, 'show'])->name('api-keys.show');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::patch('/api-keys/{apiKey}/deactivate', [ApiKeyController::class, 'deactivate'])->name('api-keys.deactivate');
    Route::patch('/api-keys/{apiKey}/activate', [ApiKeyController::class, 'activate'])->name('api-keys.activate');

    // Webhooks routes
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::post('/webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');
    Route::patch('/webhooks/{webhook}/toggle', [WebhookController::class, 'toggle'])->name('webhooks.toggle');
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');

    // Alerts routes
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts', [AlertController::class, 'store'])->name('alerts.store');
    Route::patch('/alerts/{alert}', [AlertController::class, 'update'])->name('alerts.update');
    Route::patch('/alerts/{alert}/toggle', [AlertController::class, 'toggle'])->name('alerts.toggle');
    Route::post('/alerts/{alert}/test', [AlertController::class, 'test'])->name('alerts.test');
    Route::delete('/alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');

    // Analytics routes
    Route::get('/analytics', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
    Route::get('/analytics/project/{project}/device/{device}', [AnalyticsController::class, 'deviceAnalytics'])->name('analytics.device');
    Route::get('/analytics/project/{project}/data', [AnalyticsController::class, 'projectData'])->name('analytics.project-data');
    Route::get('/analytics/project/{project}/device/{device}/data', [AnalyticsController::class, 'deviceAnalytics'])->name('analytics.device-data');

    // Export routes
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/project/{project}/messages', [ExportController::class, 'messagesCSV'])->name('messages');
        Route::get('/project/{project}/usage', [ExportController::class, 'usageCSV'])->name('usage');
        Route::get('/project/{project}/analytics', [ExportController::class, 'analyticsSummaryCSV'])->name('analytics');
        Route::get('/project/{project}/devices', [ExportController::class, 'deviceActivityCSV'])->name('devices');
        Route::get('/project/{project}/hourly-stats', [ExportController::class, 'hourlyStatsCSV'])->name('hourly');
    });

    // Subscription management routes
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/upgrade', [SubscriptionController::class, 'processUpgrade'])->name('processUpgrade');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::get('/payment/success', [SubscriptionController::class, 'paymentSuccess'])->name('payment.success');
        Route::get('/payment/failed', [SubscriptionController::class, 'paymentFailed'])->name('payment.failed');
    });

    // Admin routes (require is_admin = true)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // User management
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');

        // Subscription plan management
        Route::resource('subscription-plans', SubscriptionPlanController::class, [
            'only' => ['index', 'edit', 'update']
        ]);
        Route::patch('/subscription-plans/{plan}/reset', [SubscriptionPlanController::class, 'reset'])->name('subscription-plans.reset');
        Route::get('/subscription-plans/statistics', [SubscriptionPlanController::class, 'statistics'])->name('subscription-plans.statistics');
    });
    // Message History (only for users with advanced analytics access)
    Route::middleware(['auth'])->group(function () {
        Route::get('/messages/history', [App\Http\Controllers\MessageHistoryController::class, 'index'])
            ->name('messages.history')
            ->middleware('can:access-advanced-analytics');
    });
});
