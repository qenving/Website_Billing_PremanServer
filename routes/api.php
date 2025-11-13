<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Client API
    Route::get('/services', [Api\ServiceApiController::class, 'index']);
    Route::get('/services/{service}', [Api\ServiceApiController::class, 'show']);
    Route::post('/services/{service}/action', [Api\ServiceApiController::class, 'action']);

    Route::get('/invoices', [Api\InvoiceApiController::class, 'index']);
    Route::get('/invoices/{invoice}', [Api\InvoiceApiController::class, 'show']);

    Route::get('/tickets', [Api\TicketApiController::class, 'index']);
    Route::post('/tickets', [Api\TicketApiController::class, 'store']);
    Route::get('/tickets/{ticket}', [Api\TicketApiController::class, 'show']);
    Route::post('/tickets/{ticket}/reply', [Api\TicketApiController::class, 'reply']);

    // Admin API
    Route::middleware('admin')->group(function () {
        Route::get('/admin/stats', [Api\AdminApiController::class, 'stats']);
        Route::get('/admin/financial', [Api\AdminApiController::class, 'financial']);
    });
});
