<?php

use Illuminate\Http\Request;
use App\Services\JobsService;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/lock_create', function (Request $request) {
    (new JobsService(1))->createLock();
});

Route::post('/callback', function (Request $request) {
    info('callback',$request->all());
 });
