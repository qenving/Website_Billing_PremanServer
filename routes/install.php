<?php

use App\Http\Controllers\Install\InstallController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installation Routes
|--------------------------------------------------------------------------
|
| These routes handle the initial installation wizard.
| They are protected by CheckNotInstalled middleware.
|
*/

Route::prefix('install')->name('install.')->middleware('install.not-installed')->group(function () {

    // Step 1: Requirements Check
    Route::get('/', [InstallController::class, 'requirements'])->name('requirements');

    // Step 2: Database Mode Selection (Local vs Remote)
    Route::get('/database-mode', [InstallController::class, 'databaseMode'])->name('database.mode');
    Route::post('/database-mode', [InstallController::class, 'storeDatabaseMode'])->name('database.mode.store');

    // Step 3: Database Configuration
    Route::get('/database-config', [InstallController::class, 'databaseConfig'])->name('database.config');
    Route::post('/database-test', [InstallController::class, 'testDatabase'])->name('database.test');
    Route::post('/database-install', [InstallController::class, 'installDatabase'])->name('database.install');

    // Step 4: Owner Account Creation
    Route::get('/owner', [InstallController::class, 'owner'])->name('owner');
    Route::post('/owner', [InstallController::class, 'createOwner'])->name('owner.store');

    // Step 5: Finish
    Route::get('/finish', [InstallController::class, 'finish'])->name('finish');
});
