<?php

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
