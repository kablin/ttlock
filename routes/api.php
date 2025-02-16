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





Route::post('/v1/get_lock_list', function (Request $request) {
    (new JobsService(1))->getLockList();
});


Route::post('/v1/add_code_to_lock', function (Request $request) {
    (new JobsService(1))->addKeyToLock($request->lock_id,$request->code, $request->begin,$request->end);
});


Route::post('/v1/set_lock_passage_mode_on', function (Request $request) {
    (new JobsService(1))->setPassageModeOn($request->lock_id);
});

Route::post('/v1/set_lock_passage_mode_off', function (Request $request) {
    (new JobsService(1))->setPassageModeOff($request->lock_id);
});

Route::post('/v1/delete_code_from_lock', function (Request $request) {
    (new JobsService(1))->deleteKey($request->lock_id,$request->code_id);
});


Route::post('/callback', function (Request $request) {
    info('callback',$request->all());
 });
