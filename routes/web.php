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

    /*
    Route::get('rents_objects', function () {
        return Inertia::render('RentsObjects');
    })->name('rents_objects');*/


    //RENTS



    Route::get('/objects', [\App\Http\Controllers\RentController::class, 'objects'])->name('objects');
    Route::get('/objects2', [\App\Http\Controllers\RentController::class, 'objects2'])->name('objects2');

    Route::post('rents/create', [\App\Http\Controllers\RentController::class, 'create'])->name('rent_create');
    Route::post('rents/delete', [\App\Http\Controllers\RentController::class, 'delete'])->name('rent_delete');
    Route::post('rents/update', [\App\Http\Controllers\RentController::class, 'update'])->name('rent_update');

    Route::post('rents/attach_lock', [\App\Http\Controllers\RentController::class, 'attach_lock'])->name('rent_attach_lock');
    Route::post('rents/detach_lock', [\App\Http\Controllers\RentController::class, 'detach_lock'])->name('rent_detach_lock');




    Route::post('rents/create2', [\App\Http\Controllers\RentController::class, 'create2'])->name('rent_create2');
    Route::post('rents/delete2', [\App\Http\Controllers\RentController::class, 'delete2'])->name('rent_delete2');
    Route::post('rents/update2', [\App\Http\Controllers\RentController::class, 'update2'])->name('rent_update2');

    Route::post('rents/attach_lock2', [\App\Http\Controllers\RentController::class, 'attach_lock2'])->name('rent_attach_lock2');
    Route::post('rents/dattach_lock2', [\App\Http\Controllers\RentController::class, 'dattach_lock2'])->name('rent_dattach_lock2');
    Route::post('rents/detach_lock2', [\App\Http\Controllers\RentController::class, 'detach_lock2'])->name('rent_detach_lock2');




    Route::post('rents/page', [\App\Http\Controllers\RentController::class, 'page'])->name('rent_rent_page');




    //gropups

    Route::get('/groups', [\App\Http\Controllers\GroupController::class, 'index'])->name('groups');

    Route::post('groups/create', [\App\Http\Controllers\GroupController::class, 'create'])->name('group_create');
    Route::post('groups/delete', [\App\Http\Controllers\GroupController::class, 'delete'])->name('group_delete');
    Route::post('groups/update', [\App\Http\Controllers\GroupController::class, 'update'])->name('group_update');

    Route::post('groups/attach_lock', [\App\Http\Controllers\GroupController::class, 'attach_lock'])->name('group_attach_lock');
    Route::post('groups/detach_lock', [\App\Http\Controllers\GroupController::class, 'detach_lock'])->name('group_detach_lock');
    Route::post('groups/dattach_lock', [\App\Http\Controllers\GroupController::class, 'dattach_lock'])->name('group_dattach_lock');

    Route::post('groups/page', [\App\Http\Controllers\GroupController::class, 'page'])->name('groups_page');








    Route::get('/rents_objects', [\App\Http\Controllers\RentController::class, 'index'])->name('rents_objects');


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
