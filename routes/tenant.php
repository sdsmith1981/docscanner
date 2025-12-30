<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return inertia('Dashboard');
    })->middleware(['auth', 'verified'])->name('tenant.dashboard');

    Route::resource('documents', \App\Http\Controllers\DocumentController::class)
        ->middleware(['auth', 'verified']);

    Route::resource('integrations', \App\Http\Controllers\IntegrationController::class)
        ->middleware(['auth', 'verified']);

        Route::prefix('processing')->as('processing.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ProcessingController::class, 'index'])
                ->name('index');
            Route::post('/retry/{document}', [\App\Http\Controllers\ProcessingController::class, 'retry'])
                ->name('retry');
            Route::get('/failed', [\App\Http\Controllers\ProcessingController::class, 'failed'])
                ->name('failed');
        })->middleware(['auth', 'verified']);

        Route::prefix('email-settings')->as('email-settings.')->group(function () {
            Route::get('/edit', [\App\Http\Controllers\EmailSettingsController::class, 'edit'])
                ->name('edit');
            Route::put('/update', [\App\Http\Controllers\EmailSettingsController::class, 'update'])
                ->name('update');
            Route::get('/generate-email', [\App\Http\Controllers\EmailSettingsController::class, 'generateEmail'])
                ->name('generate-email');
        })->middleware(['auth', 'verified']);

        require __DIR__.'/settings.php';
});
