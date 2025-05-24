<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientRegistrationController;
use App\Http\Controllers\CoopRegistrationController;
use App\Http\Controllers\DashboardController;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Client registration routes
Route::get('/register/client', [ClientRegistrationController::class, 'showRegistrationForm'])->name('client.register');
Route::post('/register/client', [ClientRegistrationController::class, 'register']);
Route::get('/verify-email', [ClientRegistrationController::class, 'showVerifyEmailForm'])->name('client.verify-email');
Route::post('/verify-email', [ClientRegistrationController::class, 'verifyEmail']);

// Cooperative registration routes
Route::get('/register/cooperative', [CoopRegistrationController::class, 'showRegistrationForm'])->name('coop.register');
Route::post('/register/cooperative', [CoopRegistrationController::class, 'register']);
Route::get('/verify-coop-emails', [CoopRegistrationController::class, 'showVerifyEmailsForm'])->name('coop.verify-emails');
Route::post('/verify-coop-emails', [CoopRegistrationController::class, 'verifyEmails']);

// Dashboard routes (protected with role middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
         ->middleware('role:system_admin')
         ->name('admin.dashboard');

    Route::get('/coop/dashboard', [DashboardController::class, 'coopDashboard'])
         ->middleware('role:cooperative_admin')
         ->name('coop.dashboard');

    Route::get('/client/dashboard', [DashboardController::class, 'clientDashboard'])
         ->middleware('role:client')
         ->name('client.dashboard');
});

// Admin routes for cooperative management
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/cooperatives', [App\Http\Controllers\Admin\CooperativeManagementController::class, 'index'])
         ->name('cooperatives.index');
    Route::get('/cooperatives/{cooperative}', [App\Http\Controllers\Admin\CooperativeManagementController::class, 'show'])
         ->name('cooperatives.show');
    Route::patch('/cooperatives/{cooperative}/approve', [App\Http\Controllers\Admin\CooperativeManagementController::class, 'approve'])
         ->name('cooperatives.approve');
    Route::patch('/cooperatives/{cooperative}/reject', [App\Http\Controllers\Admin\CooperativeManagementController::class, 'reject'])
         ->name('cooperatives.reject');
    Route::post('/cooperatives/{cooperative}/request-info', [App\Http\Controllers\Admin\CooperativeManagementController::class, 'requestInfo'])
         ->name('cooperatives.request-info');
});

