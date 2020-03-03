<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Management\RehearsalsController;

// auth middleware is applied at route service provider

Route::put('/{rehearsal}/status', [RehearsalsController::class, 'update'])
    ->where('rehearsal', '[0-9]+')
    ->name('rehearsal-status-update');

