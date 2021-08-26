<?php

use App\Http\Controllers\Users\BandInvitesController;
use App\Http\Controllers\Users\BandMembershipsController;
use App\Http\Controllers\Users\BandsController;
use App\Http\Controllers\Users\FavoriteOrganizationsController;
use App\Http\Controllers\Users\GenresController;
use App\Http\Controllers\Users\OrganizationPricesController;
use App\Http\Controllers\Users\OrganizationsController;
use App\Http\Controllers\Users\RehearsalsController;
use App\Http\Controllers\Users\UserInvitesController;
use App\Http\Controllers\Users\UserRehearsalsController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\Users\UserStatisticsController;
use Illuminate\Support\Facades\Route;

Route::name('organizations.')->prefix('organizations')->group(static function () {
    Route::get('/', [OrganizationsController::class, 'index'])->name('list');

    Route::get('/{organization}', [OrganizationsController::class, 'show'])
        ->where('organization', '[0-9]+')
        ->name('show');

    Route::get('/{organization}/price', [OrganizationPricesController::class, 'index'])
        ->where('organization', '[0-9]+')
        ->name('price');
});

Route::name('favorite-organizations.')
    ->prefix('favorite-organizations/{organization}')
    ->where(['organization' => '[0-9]+'])
    ->middleware('auth:sanctum')
    ->group(static function () {
        Route::post('/', [FavoriteOrganizationsController::class, 'create'])->name('create');
        Route::delete('/', [FavoriteOrganizationsController::class, 'delete'])->name('delete');
    });

Route::name('rehearsals.')->prefix('rehearsals')->group(static function () {
    Route::get('/', [RehearsalsController::class, 'index'])
        ->name('list');

    Route::post('/', [RehearsalsController::class, 'create'])
        ->name('create')
        ->middleware('auth:sanctum');

    Route::get('/{rehearsal}', [RehearsalsController::class, 'show'])
        ->middleware('auth:sanctum')
        ->where('rehearsal', '[0-9]+')
        ->name('show');

    Route::put('/{rehearsal}', [RehearsalsController::class, 'reschedule'])
        ->name('reschedule')
        ->where('rehearsal', '[0-9]+')
        ->middleware('auth:sanctum');

    Route::delete('{rehearsal}', [RehearsalsController::class, 'delete'])
        ->where('rehearsal', '[0-9]+')
        ->middleware('auth:sanctum')
        ->name('delete');
});

Route::name('bands.')->prefix('bands')->middleware('auth:sanctum')->group(static function () {
    Route::get('/', [BandsController::class, 'index'])
        ->name('list')
        ->withoutMiddleware('auth:sanctum');

    Route::post('/', [BandsController::class, 'create'])
        ->name('create');

    Route::put('/{band}', [BandsController::class, 'update'])
        ->where('band', '[0-9]+')
        ->name('update');

    Route::post('/{band}/avatar', [BandsController::class, 'avatar'])
        ->where('band', '[0-9]+')
        ->name('avatar');

    Route::get('/{band}', [BandsController::class, 'show'])
        ->where('band', '[0-9]+')
        ->name('show')
        ->withoutMiddleware('auth:sanctum');

    Route::delete('/{band}', [BandsController::class, 'delete'])
        ->where('band', '[0-9]+')
        ->name('delete');

    Route::name('invites.')->prefix('/{band}/invites/')->where(['band' => '[0-9]+'])->group(static function () {
        Route::get('/', [BandInvitesController::class, 'index'])
            ->name('index');

        Route::post('/', [BandInvitesController::class, 'create'])
            ->name('create');

        Route::delete('/{invite}', [BandInvitesController::class, 'delete'])
            ->where('invite', '[0-9]+')
            ->name('delete');
    });
});

Route::name('users.invites.')->prefix('invites')->middleware('auth:sanctum')->group(static function () {
    Route::get('/', [UserInvitesController::class, 'index'])
        ->name('index');

    Route::post('/{invite}/accept', [UserInvitesController::class, 'accept'])
        ->where('invite', '[0-9]+')
        ->name('accept');

    Route::post('/{invite}/decline', [UserInvitesController::class, 'decline'])
        ->where('invite', '[0-9]+')
        ->name('decline');
});

Route::name('bands.members.')->prefix('bands/{band}/members')->group(static function () {
    Route::get('/', [BandMembershipsController::class, 'index'])
        ->where('band', '[0-9]+')
        ->name('index');

    Route::delete('/{membership}', [BandMembershipsController::class, 'delete'])
        ->middleware('auth:sanctum')
        ->where('band', '[0-9]+')
        ->name('delete');

    Route::patch('/{membership}', [BandMembershipsController::class, 'update'])
        ->middleware('auth:sanctum')
        ->where('band', '[0-9]+')
        ->name('update');
});

Route::get('genres', [GenresController::class, 'index'])->name('genres.index');

Route::name('users.')->prefix('/users')->group(function () {
    Route::get('/{user}', [UsersController::class, 'show'])->name('show');
    Route::get('/{user}/statistics', [UserStatisticsController::class, 'index'])->name('statistics');
    Route::get('/{user}/rehearsals', [UserRehearsalsController::class, 'index'])->name('rehearsals');
});
