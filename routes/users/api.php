<?php

use App\Http\Controllers\Users\BandInvitesController;
use App\Http\Controllers\Users\BandsController;
use App\Http\Controllers\Users\InvitesController;
use App\Http\Controllers\Users\OrganizationsController;
use App\Http\Controllers\Users\OrganizationRehearsalsController;
use App\Http\Controllers\Users\RehearsalsController;

Route::name('organizations.')->prefix('organizations')->group(static function () {

    Route::get('/', [OrganizationsController::class, 'index'])->name('list');

    Route::get('/{organization}', [OrganizationsController::class, 'show'])
        ->where('organization', '[0-9]+')
        ->name('show');

    Route::name('rehearsals.')->prefix('/{organization}/rehearsals')->group(static function () {

        Route::get('/', [OrganizationRehearsalsController::class, 'index'])
            ->where('organization', '[0-9]+')
            ->name('list');

        Route::post('/', [OrganizationRehearsalsController::class, 'create'])
            ->where('organization', '[0-9]+')
            ->name('create')
            ->middleware('auth:api');

        Route::put('/{rehearsal}', [OrganizationRehearsalsController::class, 'reschedule'])
            ->where('organization', '[0-9]+')
            ->where('rehearsal', '[0-9]+')
            ->name('reschedule')
            ->middleware('auth:api');
    });

});

Route::name('rehearsals.')->prefix('rehearsals')->middleware('auth:api')->group(static function () {

    Route::delete('{rehearsal}', [RehearsalsController::class, 'delete'])
        ->where('rehearsal', '[0-9]+')
        ->middleware('can:delete,rehearsal')
        ->name('delete');
});

Route::name('bands.')->prefix('bands')->middleware('auth:api')->group(static function () {

    Route::post('/', [BandsController::class, 'create'])
        ->name('create');

    Route::put('/{band}', [BandsController::class, 'update'])
        ->where('band', '[0-9]+')
        ->middleware('can:update,band')
        ->name('update');

    Route::name('invites.')->prefix('/{band}/invites')->group(static function () {

        Route::post('/', [BandInvitesController::class, 'create'])
            ->where('band', '[0-9]+')
            ->name('create');

        Route::delete('/{invite}', [BandInvitesController::class, 'delete'])
            ->where('band', '[0-9]+')
            ->where('invite', '[0-9]+')
            ->name('delete');
    });

});

Route::name('invites.')->prefix('invites')->middleware('auth:api')->group(static function () {

    Route::post('/{invite}/accept', [InvitesController::class, 'accept'])
        ->where('invite', '[0-9]+')
        ->name('accept');

    Route::post('/{invite}/decline', [InvitesController::class, 'decline'])
        ->where('invite', '[0-9]+')
        ->name('decline');
});
