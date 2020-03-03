<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Management\RehearsalsController;

// auth middleware is applied at route service provider

Route::put('rehearsals/{rehearsal}/status', [RehearsalsController::class, 'update'])
    ->where('rehearsal', '[0-9]+')
    ->name('rehearsal.status.update');

Route::delete('rehearsals/{rehearsal}', [RehearsalsController::class, 'delete'])
    ->where('rehearsal', '[0-9]+')
    ->name('rehearsal.delete');
