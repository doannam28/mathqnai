<?php

use Illuminate\Support\Facades\Route;
Route::get('/dang-ky', [\App\Http\Controllers\UsersController::class, 'register']);
Route::get('/dang-nhap', [\App\Http\Controllers\UsersController::class, 'login'])
    ->name('login');
Route::get('/quen-mat-khau', [\App\Http\Controllers\UsersController::class, 'forgotPasswordForm'])->name('password.request');
Route::post('/quen-mat-khau', [\App\Http\Controllers\UsersController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/dat-lai-mat-khau/{token}', [\App\Http\Controllers\UsersController::class, 'resetPasswordForm'])->name('password.reset');
Route::post('/dat-lai-mat-khau', [\App\Http\Controllers\UsersController::class, 'resetPassword'])->name('password.update');
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
    Route::get('/tai-khoan', [\App\Http\Controllers\UsersController::class, 'index']);
    Route::post('/tai-khoan', [\App\Http\Controllers\UsersController::class, 'updateAccount']);
    Route::post('/tai-khoan/muc-tieu-diem-so', [\App\Http\Controllers\UsersController::class, 'updateTargetScore'])->name('tai-khoan.target-score');
    Route::get('/kiem-tra-dau-vao', [\App\Http\Controllers\HomeController::class, 'kiem_tra_dau_vao']);
    Route::get('/thi-thu', [\App\Http\Controllers\HomeController::class, 'thi_thu']);
    Route::get('/luyen-tap/tiep-tuc/{attemptId}', [\App\Http\Controllers\HomeController::class, 'resumeLuyenTap'])->name('luyen-tap.resume');
    Route::get('/luyen-tap/{taxonomyItemId?}/{level?}', [\App\Http\Controllers\HomeController::class, 'luyen_tap']);
    Route::get('/lich-su-lam-bai', [\App\Http\Controllers\HomeController::class, 'lich_su_lam_bai']);
    Route::post('/kiem-tra-dau-vao/nop-bai', [\App\Http\Controllers\HomeController::class, 'submitKiemTraDauVao'])->name('kiem-tra-dau-vao.submit');
    Route::post('/luyen-tap/nop-bai', [\App\Http\Controllers\HomeController::class, 'submitLuyenTap'])->name('luyen-tap.submit');
    Route::post('/exam/reset-session', [\App\Http\Controllers\HomeController::class, 'resetExamSession'])->name('exam.reset-session');
});
