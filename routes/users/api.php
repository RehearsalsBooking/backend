<?php

use App\Http\Controllers\Users\BandInvitesController;
use App\Http\Controllers\Users\BandsController;
use App\Http\Controllers\Users\OrganizationsController;
use App\Http\Controllers\Users\OrganizationRehearsalsController;
use App\Http\Controllers\Users\RehearsalsController;

Route::name('organizations.')->prefix('organizations')->group(static function () {
    Route::get('/', [OrganizationsController::class, 'index'])->name('list');

    Route::get('/{organization}', [OrganizationsController::class, 'show'])
        ->where('organization', '[0-9]+')
        ->name('show');

    Route::get('/{organization}/rehearsals', [OrganizationRehearsalsController::class, 'index'])
        ->where('organization', '[0-9]+')
        ->name('rehearsals.list');

    Route::post('/{organization}/rehearsals', [OrganizationRehearsalsController::class, 'create'])
        ->where('organization', '[0-9]+')
        ->name('rehearsals.create')
        ->middleware('auth:api');

    Route::put('/{organization}/rehearsals/{rehearsal}', [OrganizationRehearsalsController::class, 'reschedule'])
        ->where('organization', '[0-9]+')
        ->where('rehearsal', '[0-9]+')
        ->name('rehearsals.reschedule')
        ->middleware('auth:api');
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

    Route::post('/{band}/invites', [BandInvitesController::class, 'create'])
        ->where('band', '[0-9]+')
        ->name('invites.create');

    Route::delete('/{band}/invites', [BandInvitesController::class, 'delete'])
        ->where('band', '[0-9]+')
        ->name('invites.delete');
});
