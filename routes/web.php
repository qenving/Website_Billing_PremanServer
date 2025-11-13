<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use App\Http\Controllers\Install\InstallController;
use Illuminate\Support\Facades\Route;

// Installation Routes
Route::prefix('install')->middleware('guest')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.index');
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('install.requirements');
    Route::get('/database', [InstallController::class, 'database'])->name('install.database');
    Route::post('/database', [InstallController::class, 'databaseStore'])->name('install.database.store');
    Route::get('/admin', [InstallController::class, 'admin'])->name('install.admin');
    Route::post('/admin', [InstallController::class, 'adminStore'])->name('install.admin.store');
    Route::get('/complete', [InstallController::class, 'complete'])->name('install.complete');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [Auth\LoginController::class, 'login']);
    Route::get('/register', [Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [Auth\RegisterController::class, 'register']);
    Route::get('/password/reset', [Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [Auth\LoginController::class, 'logout'])->name('logout');
    Route::get('/2fa/verify', [Auth\TwoFactorController::class, 'show'])->name('2fa.verify');
    Route::post('/2fa/verify', [Auth\TwoFactorController::class, 'verify']);
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'check.2fa'])->group(function () {
    // Dashboard & Financial Tracker
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/financial', [Admin\FinancialController::class, 'index'])->name('financial.index');
    Route::get('/financial/export', [Admin\FinancialController::class, 'export'])->name('financial.export');

    // User Management
    Route::resource('users', Admin\UserController::class);
    Route::post('users/{user}/toggle-status', [Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('clients/{client}/services', [Admin\ClientController::class, 'services'])->name('clients.services');
    Route::get('clients/{client}/invoices', [Admin\ClientController::class, 'invoices'])->name('clients.invoices');

    // Product Management
    Route::resource('product-groups', Admin\ProductGroupController::class);
    Route::resource('products', Admin\ProductController::class);
    Route::post('products/{product}/toggle-status', [Admin\ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    // Service Management
    Route::resource('services', Admin\ServiceController::class)->only(['index', 'show', 'edit', 'update']);
    Route::post('services/{service}/provision', [Admin\ServiceController::class, 'provision'])->name('services.provision');
    Route::post('services/{service}/suspend', [Admin\ServiceController::class, 'suspend'])->name('services.suspend');
    Route::post('services/{service}/unsuspend', [Admin\ServiceController::class, 'unsuspend'])->name('services.unsuspend');
    Route::post('services/{service}/terminate', [Admin\ServiceController::class, 'terminate'])->name('services.terminate');

    // Extension Management
    Route::get('extensions', [Admin\ExtensionController::class, 'index'])->name('extensions.index');
    Route::post('extensions/{extension}/toggle', [Admin\ExtensionController::class, 'toggle'])->name('extensions.toggle');
    Route::get('extensions/{extension}/configure', [Admin\ExtensionController::class, 'configure'])->name('extensions.configure');
    Route::post('extensions/{extension}/save-config', [Admin\ExtensionController::class, 'saveConfig'])->name('extensions.save-config');
    Route::post('extensions/{extension}/health-check', [Admin\ExtensionController::class, 'healthCheck'])->name('extensions.health-check');

    // Health Check
    Route::get('health', [Admin\HealthCheckController::class, 'index'])->name('health.index');
    Route::post('health/check-all', [Admin\HealthCheckController::class, 'checkAll'])->name('health.check-all');

    // Security Center
    Route::get('security', [Admin\SecurityController::class, 'index'])->name('security.index');
    Route::post('security/settings', [Admin\SecurityController::class, 'updateSettings'])->name('security.update-settings');
    Route::get('security/audit-logs', [Admin\SecurityController::class, 'auditLogs'])->name('security.audit-logs');
    Route::get('security/login-attempts', [Admin\SecurityController::class, 'loginAttempts'])->name('security.login-attempts');

    // General Settings
    Route::get('settings', [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');

    // Theme & Menu Management
    Route::get('theme', [Admin\ThemeController::class, 'index'])->name('theme.index');
    Route::post('theme', [Admin\ThemeController::class, 'update'])->name('theme.update');
    Route::get('menus', [Admin\MenuController::class, 'index'])->name('menus.index');
    Route::post('menus', [Admin\MenuController::class, 'update'])->name('menus.update');
    Route::post('menus/reorder', [Admin\MenuController::class, 'reorder'])->name('menus.reorder');

    // Invoice & Payment Management
    Route::resource('invoices', Admin\InvoiceController::class);
    Route::post('invoices/{invoice}/send', [Admin\InvoiceController::class, 'send'])->name('invoices.send');
    Route::get('payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [Admin\PaymentController::class, 'show'])->name('payments.show');

    // Ticket Management
    Route::resource('tickets', Admin\TicketController::class);
    Route::post('tickets/{ticket}/reply', [Admin\TicketController::class, 'reply'])->name('tickets.reply');
    Route::post('tickets/{ticket}/close', [Admin\TicketController::class, 'close'])->name('tickets.close');
    Route::resource('ticket-departments', Admin\TicketDepartmentController::class);
});

// Client Routes
Route::middleware(['auth', 'client', 'check.2fa'])->group(function () {
    // Dashboard
    Route::get('/', [Client\DashboardController::class, 'index'])->name('client.dashboard');

    // Services
    Route::get('/services', [Client\ServiceController::class, 'index'])->name('client.services.index');
    Route::get('/services/{service}', [Client\ServiceController::class, 'show'])->name('client.services.show');
    Route::post('/services/{service}/action', [Client\ServiceController::class, 'action'])->name('client.services.action');

    // Orders
    Route::get('/order', [Client\OrderController::class, 'index'])->name('client.order.index');
    Route::get('/order/{productGroup}', [Client\OrderController::class, 'group'])->name('client.order.group');
    Route::get('/order/{productGroup}/{product}', [Client\OrderController::class, 'configure'])->name('client.order.configure');
    Route::post('/order/checkout', [Client\OrderController::class, 'checkout'])->name('client.order.checkout');

    // Invoices & Payments
    Route::get('/invoices', [Client\InvoiceController::class, 'index'])->name('client.invoices.index');
    Route::get('/invoices/{invoice}', [Client\InvoiceController::class, 'show'])->name('client.invoices.show');
    Route::post('/invoices/{invoice}/pay', [Client\InvoiceController::class, 'pay'])->name('client.invoices.pay');

    // Tickets
    Route::get('/tickets', [Client\TicketController::class, 'index'])->name('client.tickets.index');
    Route::get('/tickets/create', [Client\TicketController::class, 'create'])->name('client.tickets.create');
    Route::post('/tickets', [Client\TicketController::class, 'store'])->name('client.tickets.store');
    Route::get('/tickets/{ticket}', [Client\TicketController::class, 'show'])->name('client.tickets.show');
    Route::post('/tickets/{ticket}/reply', [Client\TicketController::class, 'reply'])->name('client.tickets.reply');

    // Account & Security
    Route::get('/account', [Client\AccountController::class, 'index'])->name('client.account.index');
    Route::post('/account/profile', [Client\AccountController::class, 'updateProfile'])->name('client.account.update-profile');
    Route::post('/account/password', [Client\AccountController::class, 'updatePassword'])->name('client.account.update-password');
    Route::post('/account/2fa/enable', [Client\AccountController::class, 'enable2FA'])->name('client.account.enable-2fa');
    Route::post('/account/2fa/disable', [Client\AccountController::class, 'disable2FA'])->name('client.account.disable-2fa');
});

// Payment Gateway Callbacks (public)
Route::post('/callbacks/payment/{gateway}', [App\Http\Controllers\PaymentCallbackController::class, 'handle'])->name('payment.callback');
