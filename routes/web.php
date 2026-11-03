<?php

use Illuminate\Support\Facades\Route;
Route::get('/dang-ky', [\App\Http\Controllers\UsersController::class, 'register']);
Route::get('/dang-nhap', [\App\Http\Controllers\UsersController::class, 'login']);
Route::get('/', [\App\Http\Controllers\UsersController::class, 'login']);
Route::post('/submit-login', [\App\Http\Controllers\UsersController::class, 'submit_login']);
Route::post('/submit-register', [\App\Http\Controllers\UsersController::class, 'submit_register']);
Route::get('/auth/google', [
    \App\Http\Controllers\UsersController::class,
    'redirectToGoogle'
])->name('google.login');

Route::get('/auth/google/callback', [
    \App\Http\Controllers\UsersController::class,
    'handleGoogleCallback'
])->name('google.callback');
Route::get('/logout', [
    \App\Http\Controllers\UsersController::class,
    'logout'
])->middleware('auth:web')->name('logout');
Route::middleware('auth:web')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\HomeController::class, 'index']);
});
