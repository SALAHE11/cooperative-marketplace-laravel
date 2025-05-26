<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientRegistrationController;
use App\Http\Controllers\CoopRegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\CooperativeManagementController;
use App\Http\Middleware\CheckRole;

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

// Dashboard routes (protected with role middleware using direct class reference)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
         ->middleware(CheckRole::class . ':system_admin')
         ->name('admin.dashboard');

    Route::get('/coop/dashboard', [DashboardController::class, 'coopDashboard'])
         ->middleware(CheckRole::class . ':cooperative_admin')
         ->name('coop.dashboard');

    Route::get('/client/dashboard', [DashboardController::class, 'clientDashboard'])
         ->middleware(CheckRole::class . ':client')
         ->name('client.dashboard');
});

// Admin routes using direct middleware class reference
Route::middleware(['auth', CheckRole::class . ':system_admin'])->prefix('admin')->name('admin.')->group(function () {

    // Cooperative management routes
    Route::get('/cooperatives', [CooperativeManagementController::class, 'index'])->name('cooperatives.index');
    Route::get('/cooperatives/search', [CooperativeManagementController::class, 'search'])->name('cooperatives.search');
    Route::get('/cooperatives/{cooperative}', [CooperativeManagementController::class, 'show'])->name('cooperatives.show');
    Route::patch('/cooperatives/{cooperative}/approve', [CooperativeManagementController::class, 'approve'])->name('cooperatives.approve');
    Route::patch('/cooperatives/{cooperative}/reject', [CooperativeManagementController::class, 'reject'])->name('cooperatives.reject');
    Route::post('/cooperatives/{cooperative}/request-info', [CooperativeManagementController::class, 'requestInfo'])->name('cooperatives.request-info');
    Route::post('/send-email', [CooperativeManagementController::class, 'sendEmail'])->name('send-email');
    Route::patch('/cooperatives/{cooperative}/suspend', [CooperativeManagementController::class, 'suspend'])->name('cooperatives.suspend');
    Route::patch('/cooperatives/{cooperative}/unsuspend', [CooperativeManagementController::class, 'unsuspend'])->name('cooperatives.unsuspend');

    // Category management routes
    Route::get('/categories', [CategoryManagementController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryManagementController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryManagementController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryManagementController::class, 'destroy'])->name('categories.destroy');
    Route::get('/categories/ajax', [CategoryManagementController::class, 'getCategoriesAjax'])->name('categories.ajax');
});
