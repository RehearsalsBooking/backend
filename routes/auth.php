<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialiteLoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (){
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('me', [AuthController::class, 'me'])->name('me');
});
Route::get('/auth/{provider}', [SocialiteLoginController::class, 'redirect'])->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [SocialiteLoginController::class, 'callback'])->name('socialite.callback');
Route::post('registration', [AuthController::class, 'registration'])->name('registration');
Route::post('/login/test', [AuthController::class, 'test'])->name('login.test');

