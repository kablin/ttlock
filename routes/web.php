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
Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'show'])->middleware(['auth', 'verified'])->name('settings');

Route::post('/refresh_token', [\App\Http\Controllers\SettingsController::class, 'refreshToken'])->middleware(['auth', 'verified'])->name('refreshToken');
Route::post('/save_credential', [\App\Http\Controllers\SettingsController::class, 'saveCredential'])->middleware(['auth', 'verified'])->name('saveCredential');

Route::post('/v1/get_lock_list', [\App\Http\Controllers\CallbackApiController::class, 'getLockList'])->middleware(['auth', 'verified'])->name('getLockList');


Route::post('/v1/get_job_result/{job_id}', [\App\Http\Controllers\CallbackApiController::class, 'getJobResult'])->middleware(['auth', 'verified'])->name('getJobResult');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
