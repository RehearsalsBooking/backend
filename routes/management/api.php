<?php

use App\Http\Controllers\Management\OrganizationBansController;
use App\Http\Controllers\Management\RoomPricesController;
use App\Http\Controllers\Management\OrganizationsController;
use App\Http\Controllers\Management\OrganizationStatisticsController;
use App\Http\Controllers\Management\RehearsalsController;
use App\Http\Controllers\Management\RoomsController;
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

            Route::post('avatar', [OrganizationsController::class, 'avatar'])->name('avatar');

            Route::prefix('bans')->name('bans.')->group(static function () {
                Route::post('', [OrganizationBansController::class, 'create'])->name('create');
                Route::delete('{ban}', [OrganizationBansController::class, 'delete'])
                    ->name('delete')
                    ->where('ban', '[0-9]+');
                Route::get('', [OrganizationBansController::class, 'index'])->name('list');
            });

            Route::get('/rehearsals', [RehearsalsController::class, 'index'])
                ->name('rehearsals');

            Route::prefix('rooms')->name('rooms.')->group(static function () {
                Route::post('', [RoomsController::class, 'create'])->name('create');
                Route::put('{room:id}', [RoomsController::class, 'update'])
                    ->name('update')
                    ->where('room', '[0-9]+');
                Route::delete('{room:id}', [RoomsController::class, 'delete'])
                    ->name('delete')
                    ->where('room', '[0-9]+');
            });
        });
});

Route::prefix('rooms')->name('rooms.')->group(static function () {
    Route::prefix('{room}')
        ->where(['room' => '[0-9]+'])
        ->group(static function () {
            Route::prefix('prices')->name('prices.')->group(static function () {
                Route::get('/', [RoomPricesController::class, 'index'])
                    ->name('list');

                Route::post('/', [RoomPricesController::class, 'create'])
                    ->name('create');

                Route::delete('{price:id}', [RoomPricesController::class, 'delete'])
                    ->where('price', '[0-9]+')
                    ->name('delete');

                Route::put('{price:id}', [RoomPricesController::class, 'update'])
                    ->where('price', '[0-9]+')
                    ->name('update');
            });
        });
});
