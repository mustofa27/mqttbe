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
    Route::delete('/profile', [DashboardController::class, 'deleteAccount'])->name('deleteAccount');

    // Project CRUD routes
    Route::resource('projects', ProjectController::class);

    // Device CRUD routes
    Route::resource('devices', DeviceController::class);

    // Topic CRUD routes
    Route::resource('topics', TopicController::class);

    // Permission CRUD routes
    Route::resource('permissions', PermissionController::class);

    // Subscription management routes
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/upgrade', [SubscriptionController::class, 'processUpgrade'])->name('processUpgrade');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::get('/payment/success', [SubscriptionController::class, 'paymentSuccess'])->name('payment.success');
        Route::get('/payment/failed', [SubscriptionController::class, 'paymentFailed'])->name('payment.failed');
    });
});
