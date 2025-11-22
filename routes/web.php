<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');







Route::middleware(['auth', 'verified'])->group(function () {



    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');


    Route::get('wizard', function () {
        return Inertia::render('Wizard');
    })->name('wizard');


    Route::get('rents_objects', function () {
        return Inertia::render('RentsObjects');
    })->name('rents_objects');





    Route::get('/lockList', [\App\Http\Controllers\LockController::class, 'lockList'])->name('lockList');
    Route::post('/lockList_refresh', [\App\Http\Controllers\LockController::class, 'lockList_refresh'])->name('lockList_refresh');



    Route::get('/credential', [\App\Http\Controllers\SettingsController::class, 'show'])->name('settings');
    Route::post('/refresh_token', [\App\Http\Controllers\SettingsController::class, 'refreshToken'])->name('refreshToken');
    Route::post('/save_credential', [\App\Http\Controllers\SettingsController::class, 'saveCredential'])->name('saveCredential');



    Route::post('/v1/get_lock_list', [\App\Http\Controllers\CallbackApiController::class, 'getLockList'])->name('getLockList');
    Route::post('/v1/get_job_result/{job_id}', [\App\Http\Controllers\CallbackApiController::class, 'getJobResult'])->name('getJobResult');
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
