<?php

use App\Http\Controllers\Users\OrganizationsController;
use App\Http\Controllers\Users\RehearsalsController;

Route::name('organizations.')->prefix('organizations')->group(function () {
    Route::get('/', [OrganizationsController::class, 'index'])->name('list');
    Route::get('/{organization}', [OrganizationsController::class, 'show'])->name('show');
    Route::get('/{organization}/rehearsals', [RehearsalsController::class, 'index'])->name('rehearsals.list');

    Route::post('/{organization}/rehearsals', [RehearsalsController::class, 'create'])
        ->name('rehearsals.create')
        ->middleware('auth:api');
});
