<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes (No auth required)
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [Api\AuthController::class, 'login'])->name('api.auth.login');
});

// Authenticated Routes
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Authentication Management
    Route::post('/auth/logout', [Api\AuthController::class, 'logout'])->name('api.auth.logout');
    Route::post('/auth/logout-all', [Api\AuthController::class, 'logoutAll'])->name('api.auth.logout-all');
    Route::get('/auth/me', [Api\AuthController::class, 'me'])->name('api.auth.me');

    // Client API Endpoints
    // Services
    Route::get('/services', [Api\ServiceApiController::class, 'index']);
    Route::get('/services/{service}', [Api\ServiceApiController::class, 'show']);
    Route::post('/services/{service}/action', [Api\ServiceApiController::class, 'action']);

    // Invoices
    Route::get('/invoices', [Api\InvoiceApiController::class, 'index']);
    Route::get('/invoices/{invoice}', [Api\InvoiceApiController::class, 'show']);

    // Tickets
    Route::get('/tickets', [Api\TicketApiController::class, 'index']);
    Route::post('/tickets', [Api\TicketApiController::class, 'store']);
    Route::get('/tickets/{ticket}', [Api\TicketApiController::class, 'show']);
    Route::post('/tickets/{ticket}/reply', [Api\TicketApiController::class, 'reply']);

    // Admin API (Requires admin role)
    Route::middleware('admin')->prefix('admin')->name('api.admin.')->group(function () {
        Route::get('/stats', [Api\AdminApiController::class, 'stats']);
        Route::get('/financial', [Api\AdminApiController::class, 'financial']);

        // Additional admin endpoints can be added here
    });
});
