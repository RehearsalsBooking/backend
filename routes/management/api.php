<?php

use App\Http\Controllers\Management\OrganizationBansController;
use App\Http\Controllers\Management\OrganizationPricesController;
use App\Http\Controllers\Management\OrganizationsController;
use App\Http\Controllers\Management\OrganizationStatisticsController;
use App\Http\Controllers\Management\RehearsalsController;
use Illuminate\Support\Facades\Route;

// auth middleware is applied at route service provider

Route::prefix('rehearsals')->name('rehearsals.')->group(function () {
    Route::put('/{rehearsal}/status', [RehearsalsController::class, 'update'])
        ->where('rehearsal', '[0-9]+')
        ->name('status.update');

    Route::delete('/{rehearsal}', [RehearsalsController::class, 'delete'])
        ->where('rehearsal', '[0-9]+')
        ->name('delete');
});

Route::prefix('organizations/')->name('organizations.')->group(static function () {
    Route::get('/', [OrganizationsController::class, 'index'])->name('list');

    Route::prefix('{organization}')
        ->where(['organization' => '[0-9]+'])
        ->group(static function () {
            Route::put('/', [OrganizationsController::class, 'update'])->name('update');

            Route::get('/', [OrganizationsController::class, 'show'])->name('show');

            Route::get(
                '/statistics/total',
                [OrganizationStatisticsController::class, 'total'],
            )->name('statistics.total');

            Route::get(
                '/statistics/grouped',
                [OrganizationStatisticsController::class, 'grouped'],
            )->name('statistics.grouped');

            Route::prefix('prices')->name('prices.')->group(static function () {
                Route::get('/', [OrganizationPricesController::class, 'index'])
                    ->name('list');

                Route::post('/', [OrganizationPricesController::class, 'create'])
                    ->name('create');

                Route::delete('{price}', [OrganizationPricesController::class, 'delete'])
                    ->where('price', '[0-9]+')
                    ->name('delete');
            });

            Route::prefix('bans')->name('bans.')->group(static function () {
                Route::post('', [OrganizationBansController::class, 'create'])->name('create');
                Route::delete('{ban}', [OrganizationBansController::class, 'delete'])
                    ->name('delete')
                    ->where('ban', '[0-9]+');
                Route::get('', [OrganizationBansController::class, 'index'])->name('list');
            });

            Route::get('/rehearsals', [RehearsalsController::class, 'index'])
                ->name('rehearsals');
        });
});
