<?php

use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::put('me', [UsersController::class, 'update'])->name('update');
