<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SocialiteLoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('me', [AuthController::class, 'me'])->name('me');
});

Route::get('/auth/{provider}', [SocialiteLoginController::class, 'redirect'])->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [SocialiteLoginController::class, 'callback'])->name('socialite.callback');

Route::middleware('throttle:registration')
    ->post('registration', [AuthController::class, 'registration'])
    ->name('registration');

Route::middleware('throttle:login')->post('login', [AuthController::class, 'login'])->name('login');

Route::post('/login/test', [AuthController::class, 'test'])->name('login.test');

Route::post('/password-recovery-link', [PasswordResetController::class, 'sendRecoverPasswordLink'])
    ->name('password.send-recovery-link');
Route::post('/password-recovery', [PasswordResetController::class, 'recoverPassword'])->name('password.recover');

Route::middleware('throttle:email.verification')
    ->post('/email-verification', [AuthController::class, 'emailVerification'])
    ->name('email.verification');