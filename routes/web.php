<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CUSTOMER ROUTES (Responsive Mobile-First UI)
|--------------------------------------------------------------------------
| Catatan: Tidak boleh ada link / tombol kasir atau admin di halaman ini.
*/
Route::get('/', [CustomerController::class, 'index'])->name('menu');
Route::get('/menu/{slug}', [CustomerController::class, 'show'])->name('menu.show');
Route::get('/cart', [CustomerController::class, 'cart'])->name('cart');
Route::post('/cart/add', [CustomerController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update', [CustomerController::class, 'updateCart'])->name('cart.update');
Route::get('/checkout', [CustomerController::class, 'checkout'])->name('checkout');
Route::post('/checkout/pay', [PaymentController::class, 'processCheckout'])->name('checkout.pay');
Route::get('/orders/status', [CustomerController::class, 'orderTracking'])->name('orders.status');
Route::get('/orders/receipt/{orderNumber}', [CustomerController::class, 'receipt'])->name('orders.receipt');

// AI Assistant Chat (Customer)
Route::get('/ai', [AiAssistantController::class, 'chat'])->name('ai.chat');
Route::post('/ai/send', [AiAssistantController::class, 'sendMessage'])->name('ai.send');

// Real-Time Polling & Gateway Simulation
Route::get('/api/orders/{orderNumber}/status', [CustomerController::class, 'streamStatus'])->name('api.orders.status');
Route::post('/payment/qris/simulate/{orderNumber}', [PaymentController::class, 'simulatePayment'])->name('payment.simulate');
Route::post('/api/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

Route::get('/login', fn() => redirect()->route('cashier.login'))->name('login');

/*
|--------------------------------------------------------------------------
| CASHIER AUTH & ROUTES (/kasir)
|--------------------------------------------------------------------------
*/
Route::get('/kasir/login', [AuthController::class, 'showCashierLogin'])->name('cashier.login');
Route::post('/kasir/login', [AuthController::class, 'cashierLogin'])->name('cashier.login.post');
Route::post('/kasir/logout', [AuthController::class, 'cashierLogout'])->name('cashier.logout');

Route::middleware(['auth', 'cashier'])->prefix('kasir')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'dashboard'])->name('dashboard');
    Route::get('/api/orders/live', [CashierController::class, 'liveOrders'])->name('api.orders');
    Route::post('/orders/{id}/confirm-cash', [CashierController::class, 'confirmCash'])->name('orders.confirm_cash');
    Route::post('/orders/{id}/status', [CashierController::class, 'updateStatus'])->name('orders.update_status');
    Route::get('/orders/{id}', [CashierController::class, 'showOrder'])->name('orders.show');
});

/*
|--------------------------------------------------------------------------
| ADMIN AUTH & ROUTES (/admin)
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Menu Management
    Route::get('/menu', [AdminController::class, 'menuIndex'])->name('menu.index');
    Route::get('/menu/create', [AdminController::class, 'menuCreate'])->name('menu.create');
    Route::post('/menu', [AdminController::class, 'menuStore'])->name('menu.store');
    Route::get('/menu/{id}/edit', [AdminController::class, 'menuEdit'])->name('menu.edit');
    Route::put('/menu/{id}', [AdminController::class, 'menuUpdate'])->name('menu.update');
    Route::delete('/menu/{id}', [AdminController::class, 'menuDestroy'])->name('menu.destroy');
    Route::post('/menu/{id}/stock', [AdminController::class, 'updateStock'])->name('menu.stock.update');
    Route::post('/menu/reset-daily-stock', [AdminController::class, 'resetDailyStock'])->name('menu.stock.reset_daily');

    // Categories
    Route::post('/categories', [AdminController::class, 'categoryStore'])->name('category.store');
    Route::delete('/categories/{id}', [AdminController::class, 'categoryDestroy'])->name('category.destroy');

    // Order Management
    Route::get('/orders', [AdminController::class, 'orderIndex'])->name('orders.index');

    // Financial Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');

    // Restaurant Settings
    Route::get('/settings', [AdminController::class, 'settingsIndex'])->name('settings.index');
    Route::post('/settings', [AdminController::class, 'settingsUpdate'])->name('settings.update');

    // Cashier User Management
    Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
    Route::post('/users', [AdminController::class, 'userStore'])->name('users.store');
    Route::put('/users/{id}', [AdminController::class, 'userUpdate'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'userDestroy'])->name('users.destroy');

    // Table QR Management
    Route::get('/tables', [AdminController::class, 'tablesIndex'])->name('tables.index');
    Route::post('/tables', [AdminController::class, 'tableStore'])->name('tables.store');
});
