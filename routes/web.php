<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::get('/lockList', [\App\Http\Controllers\LockController::class, 'lockList'])->middleware(['auth', 'verified'])->name('lockList');
//Route::get('/refresh_token', [\App\Http\Controllers\AuthController::class, 'refreshToken'])->middleware(['auth', 'verified'])->name('refreshToken');

//Route::post('/get_tocken', [\App\Http\Controllers\AuthController::class, 'getToken'])->middleware(['auth', 'verified'])->name('getToken');

Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'show'])->middleware(['auth', 'verified'])->name('settings');

Route::post('/refresh_token', [\App\Http\Controllers\SettingsController::class, 'refreshToken'])->middleware(['auth', 'verified'])->name('refreshToken');
Route::post('/save_credential', [\App\Http\Controllers\SettingsController::class, 'saveCredential'])->middleware(['auth', 'verified'])->name('saveCredential');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
