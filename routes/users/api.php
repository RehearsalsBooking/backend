<?php

use App\Http\Controllers\Users\BandMembersController;
use App\Http\Controllers\Users\BandsController;
use App\Http\Controllers\Users\InvitesController;
use App\Http\Controllers\Users\OrganizationsController;
use App\Http\Controllers\Users\RehearsalsController;
use Illuminate\Support\Facades\Route;

Route::name('organizations.')->prefix('organizations')->group(static function () {
    Route::get('/', [OrganizationsController::class, 'index'])->name('list');

    Route::get('/{organization}', [OrganizationsController::class, 'show'])
        ->where('organization', '[0-9]+')
        ->name('show');
});

Route::name('rehearsals.')->prefix('rehearsals')->group(static function () {
    Route::get('/', [RehearsalsController::class, 'index'])
        ->name('list');

    Route::post('/', [RehearsalsController::class, 'create'])
        ->name('create')
        ->middleware('auth:sanctum');

    Route::put('/{rehearsal}', [RehearsalsController::class, 'reschedule'])
        ->where('rehearsal', '[0-9]+')
        ->name('reschedule')
        ->middleware('auth:sanctum');

    Route::delete('{rehearsal}', [RehearsalsController::class, 'delete'])
        ->where('rehearsal', '[0-9]+')
        ->middleware('auth:sanctum')
        ->name('delete');
});

Route::name('bands.')->prefix('bands')->middleware('auth:sanctum')->group(static function () {
    Route::post('/', [BandsController::class, 'create'])
        ->name('create');

    Route::put('/{band}', [BandsController::class, 'update'])
        ->where('band', '[0-9]+')
        ->name('update');

    Route::delete('/{band}', [BandsController::class, 'delete'])
        ->where('band', '[0-9]+')
        ->name('delete');
});

Route::name('invites.')->prefix('invites')->middleware('auth:sanctum')->group(static function () {
    Route::post('/', [InvitesController::class, 'create'])
        ->name('create');

    Route::delete('/{invite}', [InvitesController::class, 'delete'])
        ->where('invite', '[0-9]+')
        ->name('delete');

    Route::post('/{invite}/accept', [InvitesController::class, 'accept'])
        ->where('invite', '[0-9]+')
        ->name('accept');

    Route::post('/{invite}/decline', [InvitesController::class, 'decline'])
        ->where('invite', '[0-9]+')
        ->name('decline');
});

Route::name('bands.members.')->prefix('bands/{band}/members')->middleware('auth:sanctum')->group(static function () {
    Route::delete('/{memberId}', [BandMembersController::class, 'delete'])
        ->where('band', '[0-9]+')
        ->name('delete');
});
