<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientRegistrationController;
use App\Http\Controllers\CoopRegistrationController;
use App\Http\Controllers\CooperativeRequestManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\CooperativeManagementController;
use App\Http\Controllers\Admin\AdminInvitationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ProductManagementController;
use App\Http\Controllers\Admin\ProductRequestManagementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ===== PUBLIC ROUTES =====

// Home page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ===== AUTHENTICATION ROUTES =====

// Login & Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===== PASSWORD RESET ROUTES =====

Route::get('/password/reset', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/password/send-code', [PasswordResetController::class, 'sendResetCode'])->name('password.send-code');
Route::get('/password/verify-code', [PasswordResetController::class, 'showVerifyCodeForm'])->name('password.verify-code');
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyCode'])->name('password.verify-code.submit');
Route::get('/password/new', [PasswordResetController::class, 'showNewPasswordForm'])->name('password.new');
Route::post('/password/new', [PasswordResetController::class, 'setNewPassword'])->name('password.new.submit');
Route::post('/password/resend-code', [PasswordResetController::class, 'resendCode'])->name('password.resend-code');

// ===== CLIENT REGISTRATION ROUTES =====

Route::get('/register/client', [ClientRegistrationController::class, 'showRegistrationForm'])->name('client.register');
Route::post('/register/client', [ClientRegistrationController::class, 'register']);
Route::get('/verify-email', [ClientRegistrationController::class, 'showVerifyEmailForm'])->name('client.verify-email');
Route::post('/verify-email', [ClientRegistrationController::class, 'verifyEmail']);

// ===== COOPERATIVE REGISTRATION ROUTES =====

// New cooperative registration
Route::get('/register/cooperative', [CoopRegistrationController::class, 'showRegistrationForm'])->name('coop.register');
Route::post('/register/cooperative', [CoopRegistrationController::class, 'register']);
Route::get('/verify-coop-emails', [CoopRegistrationController::class, 'showVerifyEmailsForm'])->name('coop.verify-emails');
Route::post('/verify-coop-emails', [CoopRegistrationController::class, 'verifyEmails']);

// Cooperative search routes (for joining existing cooperative)
Route::get('/cooperatives/search', [CoopRegistrationController::class, 'searchCooperatives'])->name('coop.search');
Route::get('/cooperatives/{id}/details', [CoopRegistrationController::class, 'getCooperativeDetails'])->name('coop.details');

// Join request routes
Route::get('/verify-join-request', [CoopRegistrationController::class, 'showVerifyJoinRequestForm'])->name('coop.verify-join-request');
Route::post('/verify-join-request', [CoopRegistrationController::class, 'verifyJoinRequest']);
Route::get('/join-request-sent', [CoopRegistrationController::class, 'showJoinRequestSent'])->name('coop.join-request-sent');

// ===== ADMIN REGISTRATION ROUTES (PUBLIC - ACCESSED VIA INVITATION LINK) =====

Route::get('/admin/register/{token}', [AdminInvitationController::class, 'showRegistrationForm'])->name('admin.register');
Route::post('/admin/register/{token}', [AdminInvitationController::class, 'register'])->name('admin.register.submit');
Route::get('/admin/verify-email', [AdminInvitationController::class, 'showVerifyEmailForm'])->name('admin.verify-email');
Route::post('/admin/verify-email', [AdminInvitationController::class, 'verifyEmail'])->name('admin.verify-email.submit');

// ===== PROTECTED ROUTES (REQUIRE AUTHENTICATION) =====

Route::middleware(['auth'])->group(function () {

    // ===== DASHBOARD ROUTES =====

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

// ===== SYSTEM ADMIN ROUTES =====

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

    // Category hierarchy routes
    Route::get('/categories/tree', [CategoryManagementController::class, 'getTreeData'])->name('categories.tree');
    Route::post('/categories/{category}/move', [CategoryManagementController::class, 'moveCategory'])->name('categories.move');
    Route::post('/categories/reorder', [CategoryManagementController::class, 'reorderCategories'])->name('categories.reorder');
    Route::get('/categories/breadcrumb/{category}', [CategoryManagementController::class, 'getBreadcrumb'])->name('categories.breadcrumb');

    // User management routes
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('users.updateStatus');
    Route::post('/users/activate-all-pending', [UserManagementController::class, 'activateAllPending'])->name('users.activateAllPending');
    Route::post('/users/suspend-multiple', [UserManagementController::class, 'suspendMultiple'])->name('users.suspendMultiple');

    // Product request management routes
    Route::get('/product-requests', [ProductRequestManagementController::class, 'index'])->name('product-requests.index');
    Route::get('/product-requests/{product}', [ProductRequestManagementController::class, 'show'])->name('product-requests.show');
    Route::post('/product-requests/{product}/approve', [ProductRequestManagementController::class, 'approve'])->name('product-requests.approve');
    Route::post('/product-requests/{product}/reject', [ProductRequestManagementController::class, 'reject'])->name('product-requests.reject');
    Route::post('/product-requests/{product}/request-info', [ProductRequestManagementController::class, 'requestInfo'])->name('product-requests.request-info');
    Route::get('/product-requests/{product}/images', [ProductRequestManagementController::class, 'getImages'])->name('product-requests.images');
});

// ===== COOPERATIVE ADMIN ROUTES =====

Route::middleware(['auth', 'check.role:cooperative_admin'])->prefix('coop')->name('coop.')->group(function () {

    // ===== ADMIN REQUEST MANAGEMENT ROUTES =====

    // Get data routes
    Route::get('/admin-requests/pending', [CooperativeRequestManagementController::class, 'getPendingRequests'])->name('admin-requests.pending');
    Route::get('/admin-requests/current-admins', [CooperativeRequestManagementController::class, 'getCurrentAdmins'])->name('admin-requests.current-admins');
    Route::get('/admin-requests/inactive-admins', [CooperativeRequestManagementController::class, 'getInactiveAdmins'])->name('admin-requests.inactive-admins');

    // Request action routes
    Route::post('/admin-requests/{request}/approve', [CooperativeRequestManagementController::class, 'approveRequest'])->name('admin-requests.approve');
    Route::post('/admin-requests/{request}/reject', [CooperativeRequestManagementController::class, 'rejectRequest'])->name('admin-requests.reject');
    Route::post('/admin-requests/{request}/clarification', [CooperativeRequestManagementController::class, 'requestClarification'])->name('admin-requests.clarification');

    // Admin management routes
    Route::delete('/admins/{admin}/remove', [CooperativeRequestManagementController::class, 'removeAdmin'])->name('admins.remove');
    Route::post('/admins/{admin}/reactivate', [CooperativeRequestManagementController::class, 'reactivateAdmin'])->name('admins.reactivate');
    Route::delete('/admins/{admin}/permanently-remove', [CooperativeRequestManagementController::class, 'permanentlyRemoveAdmin'])->name('admins.permanently-remove');

    // Product management routes
    Route::get('/products', [ProductManagementController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductManagementController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductManagementController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductManagementController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductManagementController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/submit', [ProductManagementController::class, 'submit'])->name('products.submit');
    Route::delete('/products/{product}', [ProductManagementController::class, 'destroy'])->name('products.destroy');
});

// ===== CLIENT ROUTES =====

Route::middleware(['auth', 'check.role:client'])->prefix('client')->name('client.')->group(function () {

    // Client-specific routes can be added here
    // Example: Profile management, order history, etc.
});
