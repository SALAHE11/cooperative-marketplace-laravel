<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientRegistrationController;
use App\Http\Controllers\CoopRegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\CooperativeManagementController;
use App\Http\Controllers\Admin\AdminInvitationController;
use App\Http\Controllers\Auth\PasswordResetController;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset routes
Route::get('/password/reset', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/password/send-code', [PasswordResetController::class, 'sendResetCode'])->name('password.send-code');
Route::get('/password/verify-code', [PasswordResetController::class, 'showVerifyCodeForm'])->name('password.verify-code');
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyCode'])->name('password.verify-code.submit');
Route::get('/password/new', [PasswordResetController::class, 'showNewPasswordForm'])->name('password.new');
Route::post('/password/new', [PasswordResetController::class, 'setNewPassword'])->name('password.new.submit');
Route::post('/password/resend-code', [PasswordResetController::class, 'resendCode'])->name('password.resend-code');

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

// Admin registration routes (public - accessed via invitation link)
Route::get('/admin/register/{token}', [AdminInvitationController::class, 'showRegistrationForm'])->name('admin.register');
Route::post('/admin/register/{token}', [AdminInvitationController::class, 'register'])->name('admin.register.submit');
Route::get('/admin/verify-email', [AdminInvitationController::class, 'showVerifyEmailForm'])->name('admin.verify-email');
Route::post('/admin/verify-email', [AdminInvitationController::class, 'verifyEmail'])->name('admin.verify-email.submit');

// Dashboard routes (protected with role middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
         ->middleware('check.role:system_admin')
         ->name('admin.dashboard');

    Route::get('/coop/dashboard', [DashboardController::class, 'coopDashboard'])
         ->middleware('check.role:cooperative_admin')
         ->name('coop.dashboard');

    Route::get('/client/dashboard', [DashboardController::class, 'clientDashboard'])
         ->middleware('check.role:client')
         ->name('client.dashboard');
});

// Admin routes
Route::middleware(['auth', 'check.role:system_admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin invitation routes
    Route::post('/send-invitation', [AdminInvitationController::class, 'sendInvitation'])->name('send-invitation');

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

    // NEW: Category hierarchy routes
    Route::get('/categories/tree', [CategoryManagementController::class, 'getTreeData'])->name('categories.tree');
    Route::post('/categories/{category}/move', [CategoryManagementController::class, 'moveCategory'])->name('categories.move');
    Route::post('/categories/reorder', [CategoryManagementController::class, 'reorderCategories'])->name('categories.reorder');
    Route::get('/categories/breadcrumb/{category}', [CategoryManagementController::class, 'getBreadcrumb'])->name('categories.breadcrumb');

    // User management routes
    Route::get('/users', [App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/status', [App\Http\Controllers\Admin\UserManagementController::class, 'updateStatus'])->name('users.updateStatus');
    Route::post('/users/activate-all-pending', [App\Http\Controllers\Admin\UserManagementController::class, 'activateAllPending'])->name('users.activateAllPending');
    Route::post('/users/suspend-multiple', [App\Http\Controllers\Admin\UserManagementController::class, 'suspendMultiple'])->name('users.suspendMultiple');
});


