<?php

use Illuminate\Support\Facades\Route;
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index']);
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);
Route::get('/noi-qui-va-dieu-khoan', [\App\Http\Controllers\HomeController::class, 'noiquy']);
Route::get('/du-doan-xo-so-{slug}', [\App\Http\Controllers\HomeController::class, 'page']);
Route::get('/tag/{slug}', [\App\Http\Controllers\HomeController::class, 'tag']);
Route::get('/ket-qua', [\App\Http\Controllers\HomeController::class, 'ketqua']);
Route::get('/bai-viet/{slug1}', [\App\Http\Controllers\HomeController::class, 'detail']);
Route::get('/{slug}/{slug1}', [\App\Http\Controllers\HomeController::class, 'detail']);
Route::get('/{slug}', [\App\Http\Controllers\HomeController::class, 'category']);
Route::post('/send', [\App\Http\Controllers\HomeController::class, 'sendMail']);
