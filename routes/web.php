<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\PermissionController;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
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
});
