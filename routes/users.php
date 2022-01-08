<?php

use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::put('me', [UsersController::class, 'update'])->name('update');
Route::put('me/email', [UsersController::class, 'updateEmail'])->name('update.email');
Route::post('me/avatar', [UsersController::class, 'avatar'])->name('avatar');
