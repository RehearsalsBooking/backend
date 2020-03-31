<?php

use App\Http\Controllers\Management\OrganizationBansController;
use App\Http\Controllers\Management\OrganizationPricesController;
use App\Http\Controllers\Management\RehearsalsController;
use Illuminate\Support\Facades\Route;

// auth middleware is applied at route service provider

Route::middleware('check.rehearsal.ownership')->group(static function () {
    Route::put('rehearsals/{rehearsal}/status', [RehearsalsController::class, 'update'])
        ->where('rehearsal', '[0-9]+')
        ->name('rehearsal.status.update');

    Route::delete('rehearsals/{rehearsal}', [RehearsalsController::class, 'delete'])
        ->where('rehearsal', '[0-9]+')
        ->name('rehearsal.delete');
});

Route::get('rehearsals', [RehearsalsController::class, 'index'])
    ->name('rehearsals.list');

Route::prefix('organizations/{organization}')
    ->name('organization.')
    ->where(['organization' => '[0-9]+'])
    ->group(static function () {

        Route::prefix('prices')->name('prices.')->group(static function () {

            Route::get('/', [OrganizationPricesController::class, 'index'])
                ->name('list');

            Route::post('/', [OrganizationPricesController::class, 'create'])
                ->name('create');

            Route::delete('{price}', [OrganizationPricesController::class, 'delete'])
                ->where('price', '[0-9]+')
                ->name('delete');
        });

        Route::post('ban', [OrganizationBansController::class, 'create'])->name('ban.create');

    });
