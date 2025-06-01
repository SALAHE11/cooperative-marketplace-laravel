<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\User;
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
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Coop\OrderManagementController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\ProductBrowsingController;
use App\Http\Controllers\Client\ReceiptController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// =============================================================================
// PUBLIC ROUTES
// =============================================================================

// Home page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// =============================================================================
// AUTHENTICATION ROUTES
// =============================================================================

// Login & Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =============================================================================
// PASSWORD RESET ROUTES
// =============================================================================

Route::prefix('password')->name('password.')->group(function () {
    Route::get('/reset', [PasswordResetController::class, 'showForgotForm'])->name('request');
    Route::post('/send-code', [PasswordResetController::class, 'sendResetCode'])->name('send-code');
    Route::get('/verify-code', [PasswordResetController::class, 'showVerifyCodeForm'])->name('verify-code');
    Route::post('/verify-code', [PasswordResetController::class, 'verifyCode'])->name('verify-code.submit');
    Route::get('/new', [PasswordResetController::class, 'showNewPasswordForm'])->name('new');
    Route::post('/new', [PasswordResetController::class, 'setNewPassword'])->name('new.submit');
    Route::post('/resend-code', [PasswordResetController::class, 'resendCode'])->name('resend-code');
});

// =============================================================================
// CLIENT REGISTRATION ROUTES
// =============================================================================

Route::prefix('register/client')->name('client.')->group(function () {
    Route::get('/', [ClientRegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/', [ClientRegistrationController::class, 'register']);
    Route::get('/verify-email', [ClientRegistrationController::class, 'showVerifyEmailForm'])->name('verify-email');
    Route::post('/verify-email', [ClientRegistrationController::class, 'verifyEmail']);
});

// =============================================================================
// COOPERATIVE REGISTRATION ROUTES
// =============================================================================

Route::prefix('register/cooperative')->name('coop.')->group(function () {
    // New cooperative registration
    Route::get('/', [CoopRegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/', [CoopRegistrationController::class, 'register']);
    Route::get('/verify-emails', [CoopRegistrationController::class, 'showVerifyEmailsForm'])->name('verify-emails');
    Route::post('/verify-emails', [CoopRegistrationController::class, 'verifyEmails']);

    // Join existing cooperative
    Route::get('/search', [CoopRegistrationController::class, 'searchCooperatives'])->name('search');
    Route::get('/{id}/details', [CoopRegistrationController::class, 'getCooperativeDetails'])->name('details');
    Route::get('/verify-join-request', [CoopRegistrationController::class, 'showVerifyJoinRequestForm'])->name('verify-join-request');
    Route::post('/verify-join-request', [CoopRegistrationController::class, 'verifyJoinRequest']);
    Route::get('/join-request-sent', [CoopRegistrationController::class, 'showJoinRequestSent'])->name('join-request-sent');
});

// =============================================================================
// ADMIN REGISTRATION ROUTES (PUBLIC - VIA INVITATION)
// =============================================================================

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/register/{token}', [AdminInvitationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register/{token}', [AdminInvitationController::class, 'register'])->name('register.submit');
    Route::get('/verify-email', [AdminInvitationController::class, 'showVerifyEmailForm'])->name('verify-email');
    Route::post('/verify-email', [AdminInvitationController::class, 'verifyEmail'])->name('verify-email.submit');
});

// =============================================================================
// PROTECTED ROUTES (REQUIRE AUTHENTICATION)
// =============================================================================

Route::middleware(['auth'])->group(function () {

    // =========================================================================
    // DASHBOARD ROUTES
    // =========================================================================

    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
         ->middleware('check.role:system_admin')
         ->name('admin.dashboard');

    Route::get('/coop/dashboard', [DashboardController::class, 'coopDashboard'])
         ->middleware('check.role:cooperative_admin')
         ->name('coop.dashboard');

    Route::get('/client/dashboard', [DashboardController::class, 'clientDashboard'])
         ->middleware('check.role:client')
         ->name('client.dashboard');

    // =========================================================================
    // SYSTEM ADMIN ROUTES
    // =========================================================================

    Route::middleware(['check.role:system_admin'])->prefix('admin')->name('admin.')->group(function () {

        // Admin invitation routes
        Route::post('/send-invitation', [AdminInvitationController::class, 'sendInvitation'])->name('send-invitation');

        // Cooperative management routes
        Route::prefix('cooperatives')->name('cooperatives.')->group(function () {
            Route::get('/', [CooperativeManagementController::class, 'index'])->name('index');
            Route::get('/search', [CooperativeManagementController::class, 'search'])->name('search');
            Route::get('/{cooperative}', [CooperativeManagementController::class, 'show'])->name('show');
            Route::patch('/{cooperative}/approve', [CooperativeManagementController::class, 'approve'])->name('approve');
            Route::patch('/{cooperative}/reject', [CooperativeManagementController::class, 'reject'])->name('reject');
            Route::post('/{cooperative}/request-info', [CooperativeManagementController::class, 'requestInfo'])->name('request-info');
            Route::patch('/{cooperative}/suspend', [CooperativeManagementController::class, 'suspend'])->name('suspend');
            Route::patch('/{cooperative}/unsuspend', [CooperativeManagementController::class, 'unsuspend'])->name('unsuspend');
        });

        // Email management
        Route::post('/send-email', [CooperativeManagementController::class, 'sendEmail'])->name('send-email');

        // Category management routes
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryManagementController::class, 'index'])->name('index');
            Route::post('/', [CategoryManagementController::class, 'store'])->name('store');
            Route::put('/{category}', [CategoryManagementController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryManagementController::class, 'destroy'])->name('destroy');
            Route::get('/ajax', [CategoryManagementController::class, 'getCategoriesAjax'])->name('ajax');

            // Category hierarchy routes
            Route::get('/tree', [CategoryManagementController::class, 'getTreeData'])->name('tree');
            Route::post('/{category}/move', [CategoryManagementController::class, 'moveCategory'])->name('move');
            Route::post('/reorder', [CategoryManagementController::class, 'reorderCategories'])->name('reorder');
            Route::get('/breadcrumb/{category}', [CategoryManagementController::class, 'getBreadcrumb'])->name('breadcrumb');
        });

        // User management routes
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
            Route::patch('/{user}/status', [UserManagementController::class, 'updateStatus'])->name('updateStatus');
            Route::post('/activate-all-pending', [UserManagementController::class, 'activateAllPending'])->name('activateAllPending');
            Route::post('/suspend-multiple', [UserManagementController::class, 'suspendMultiple'])->name('suspendMultiple');
        });

        // Product request management routes
        Route::prefix('product-requests')->name('product-requests.')->group(function () {
            Route::get('/', [ProductRequestManagementController::class, 'index'])->name('index');
            Route::get('/{product}', [ProductRequestManagementController::class, 'show'])->name('show');
            Route::post('/{product}/approve', [ProductRequestManagementController::class, 'approve'])->name('approve');
            Route::post('/{product}/reject', [ProductRequestManagementController::class, 'reject'])->name('reject');
            Route::post('/{product}/request-info', [ProductRequestManagementController::class, 'requestInfo'])->name('request-info');
            Route::get('/{product}/images', [ProductRequestManagementController::class, 'getImages'])->name('images');
        });

    });

    // =========================================================================
    // COOPERATIVE ADMIN ROUTES
    // =========================================================================

    Route::middleware(['check.role:cooperative_admin'])->prefix('coop')->name('coop.')->group(function () {

        // Admin request management routes (PRIMARY ADMIN ONLY)
        Route::prefix('admin-requests')->name('admin-requests.')->group(function () {
            Route::get('/pending', [CooperativeRequestManagementController::class, 'getPendingRequests'])->name('pending');
            Route::get('/current-admins', [CooperativeRequestManagementController::class, 'getCurrentAdmins'])->name('current-admins');
            Route::get('/inactive-admins', [CooperativeRequestManagementController::class, 'getInactiveAdmins'])->name('inactive-admins');

            // Request action routes
            Route::post('/{request}/approve', [CooperativeRequestManagementController::class, 'approveRequest'])->name('approve');
            Route::post('/{request}/reject', [CooperativeRequestManagementController::class, 'rejectRequest'])->name('reject');
            Route::post('/{request}/clarification', [CooperativeRequestManagementController::class, 'requestClarification'])->name('clarification');
        });

        // Admin management routes (PRIMARY ADMIN ONLY)
        Route::prefix('admins')->name('admins.')->group(function () {
            Route::delete('/{admin}/remove', [CooperativeRequestManagementController::class, 'removeAdmin'])->name('remove');
            Route::post('/{admin}/reactivate', [CooperativeRequestManagementController::class, 'reactivateAdmin'])->name('reactivate');
            Route::delete('/{admin}/permanently-remove', [CooperativeRequestManagementController::class, 'permanentlyRemoveAdmin'])->name('permanently-remove');
        });

        // Product management routes
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductManagementController::class, 'index'])->name('index');
            Route::get('/create', [ProductManagementController::class, 'create'])->name('create');
            Route::post('/', [ProductManagementController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductManagementController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductManagementController::class, 'update'])->name('update');
            Route::post('/{product}/submit', [ProductManagementController::class, 'submit'])->name('submit');
            Route::delete('/{product}', [ProductManagementController::class, 'destroy'])->name('destroy');
            Route::get('/{product}', [ProductManagementController::class, 'show'])->name('show');

            // Stock alert configuration routes
            Route::post('/{product}/configure-stock-alert', [ProductManagementController::class, 'configureStockAlert'])->name('configure-stock-alert');
            Route::post('/bulk-configure-stock-alerts', [ProductManagementController::class, 'bulkConfigureStockAlerts'])->name('bulk-configure-stock-alerts');

            // Image management routes
            Route::post('/{product}/manage-images', [ProductManagementController::class, 'manageImages'])->name('manage-images');
        });

        // Order management routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderManagementController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderManagementController::class, 'show'])->name('show');
            Route::post('/{order}/update-status', [OrderManagementController::class, 'updateStatus'])->name('update-status');
            Route::post('/{order}/mark-picked-up', [OrderManagementController::class, 'markPickedUp'])->name('mark-picked-up');
        });

    });

    // =========================================================================
    // CLIENT ROUTES
    // =========================================================================

    Route::middleware(['check.role:client'])->prefix('client')->name('client.')->group(function () {

        // Product browsing routes
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductBrowsingController::class, 'index'])->name('index');
            Route::get('/{product}', [ProductBrowsingController::class, 'show'])->name('show');
            Route::get('/search/ajax', [ProductBrowsingController::class, 'search'])->name('search');
        });

        // Cart management routes
        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
            Route::post('/update', [CartController::class, 'update'])->name('update');
            Route::post('/remove', [CartController::class, 'remove'])->name('remove');
            Route::post('/clear', [CartController::class, 'clear'])->name('clear');
            Route::get('/count', [CartController::class, 'count'])->name('count');
        });

        // Checkout routes
        Route::prefix('checkout')->name('checkout.')->group(function () {
            Route::get('/', [CheckoutController::class, 'show'])->name('show');
            Route::post('/process', [CheckoutController::class, 'process'])->name('process');
            Route::get('/success', [CheckoutController::class, 'success'])->name('success');
        });

        // Order routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::get('/success', [CheckoutController::class, 'success'])->name('success');
        });

        // Receipt routes
        Route::prefix('receipts')->name('receipts.')->group(function () {
            Route::get('/client/{receipt}', [ReceiptController::class, 'downloadClientReceipt'])->name('client');
            Route::get('/authorization/{authReceipt}', [ReceiptController::class, 'downloadAuthorizationReceipt'])->name('authorization');
            Route::post('/{receipt}/create-authorization', [ReceiptController::class, 'createAuthorizationReceipt'])->name('create-authorization');
        });

    });

    // =========================================================================
    // API ROUTES FOR DASHBOARD STATS
    // =========================================================================

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/dashboard/insights', [DashboardController::class, 'getQuickInsights'])->name('dashboard.insights');
    });

});

// =============================================================================
// FALLBACK ROUTES
// =============================================================================



// Handle undefined routes
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
