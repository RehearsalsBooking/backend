<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialiteLoginController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
Route::get('me', [AuthController::class, 'me'])->name('me');

Route::post('/login/social', [SocialiteLoginController::class, 'callback'])->name('socialite.login');
