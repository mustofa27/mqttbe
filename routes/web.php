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
use App\Http\Controllers\AdvanceDashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\MqttListenerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SubscriptionAddonController;

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

// Email verification routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        if (request()->user()->hasVerifiedEmail()) {
            return redirect()->route('home.dashboard');
        }

        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home.dashboard')->with('success', 'Email already verified.');
        }

        $request->fulfill();

        return redirect()->route('home.dashboard')->with('success', 'Email verified! Welcome aboard.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Verification link sent to your email.');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// Protected routes (require authentication + email verification)
Route::middleware(['auth', 'verified'])->group(function () {
   //Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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

    // Home/Dashboard routes
    Route::get('/home', [UsageController::class, 'dashboard'])->name('home.dashboard');
    Route::get('/home/project/{project}', [UsageController::class, 'projectUsage'])->name('home.project');
    Route::view('/setup-guide', 'dashboard.setup-guide')->name('setup.guide');

    // API Key management routes
    Route::middleware('check.subscription.limit:api')->group(function () {
        Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
        Route::get('/api-keys/{apiKey}', [ApiKeyController::class, 'show'])->name('api-keys.show');
        Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
        Route::patch('/api-keys/{apiKey}/deactivate', [ApiKeyController::class, 'deactivate'])->name('api-keys.deactivate');
        Route::patch('/api-keys/{apiKey}/activate', [ApiKeyController::class, 'activate'])->name('api-keys.activate');
    });

    // Webhooks routes
    Route::middleware('check.subscription.limit:webhooks')->group(function () {
        Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
        Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
        Route::post('/webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');
        Route::patch('/webhooks/{webhook}/toggle', [WebhookController::class, 'toggle'])->name('webhooks.toggle');
        Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    });

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

    // Advanced Dashboard routes
    Route::get('/advance-dashboard', [AdvanceDashboardController::class, 'index'])->name('advance-dashboard.index');
    Route::post('/advance-dashboard/widgets', [AdvanceDashboardController::class, 'store'])->name('advance-dashboard.widgets.store');
    Route::delete('/advance-dashboard/widgets/{widget}', [AdvanceDashboardController::class, 'destroy'])->name('advance-dashboard.widgets.destroy');
    Route::get('/advance-dashboard/widgets/{widget}/data', [AdvanceDashboardController::class, 'data'])->name('advance-dashboard.widgets.data');
    Route::patch('/advance-dashboard/widgets/reorder', [AdvanceDashboardController::class, 'reorder'])->name('advance-dashboard.widgets.reorder');
    Route::patch('/advance-dashboard/widgets/{widget}/size', [AdvanceDashboardController::class, 'updateSize'])->name('advance-dashboard.widgets.update-size');

    // MQTT listener controls (advanced analytics users only; verified in controller)
    Route::get('/mqtt-listener/status', [MqttListenerController::class, 'status'])->name('mqtt-listener.status');
    Route::post('/mqtt-listener/config', [MqttListenerController::class, 'saveConfig'])->name('mqtt-listener.config');
    Route::post('/mqtt-listener/start', [MqttListenerController::class, 'start'])->name('mqtt-listener.start');
    Route::post('/mqtt-listener/stop', [MqttListenerController::class, 'stop'])->name('mqtt-listener.stop');
    Route::post('/mqtt-listener/restart', [MqttListenerController::class, 'restart'])->name('mqtt-listener.restart');

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

        // MQTT listener monitoring (admin)
        Route::get('/mqtt-listeners', [MqttListenerController::class, 'adminOverview'])->name('mqtt-listeners.index');

        // Subscription plan management
        Route::resource('subscription-plans', SubscriptionPlanController::class, [
            'only' => ['index', 'edit', 'update']
        ]);
        Route::patch('/subscription-plans/{plan}/reset', [SubscriptionPlanController::class, 'reset'])->name('subscription-plans.reset');
        Route::get('/subscription-plans/statistics', [SubscriptionPlanController::class, 'statistics'])->name('subscription-plans.statistics');

        // Subscription add-on management
        Route::resource('subscription-addons', SubscriptionAddonController::class)->except(['show']);
    });
    // Message History (only for users with advanced analytics access)
    Route::middleware(['auth'])->group(function () {
        Route::get('/messages/history', [App\Http\Controllers\MessageHistoryController::class, 'index'])
            ->name('messages.history');
    });
});
