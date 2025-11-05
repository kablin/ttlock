<?php

use Illuminate\Http\Request;
use App\Services\JobsService;
use App\Http\Middleware\CodesCounter;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SimpleApiController;
use App\Http\Controllers\CallbackApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/lock_create', function (Request $request) {
    return (new JobsService(auth()->user()->id))->createLock( json_decode($request->getContent())->tag ?? '');
})->middleware('auth:sanctum');

*/




Route::middleware(['throttle:500,1'])->group(function () {

    Route::post('/v1/create_user', [AuthController::class, 'CreateUser']);

    Route::post('/v1/set_callback', [SimpleApiController::class, 'setCallback'])->middleware('auth:sanctum');;

    Route::post('/v1/get_token', [SimpleApiController::class, 'getToken']);
});




Route::middleware(['throttle:500,1'])->group(function () {

    /*Route::post('/v1/create_credential', function (Request $request) {
        return (new JobsService(auth()->user()->id))->createCredential($request->user, $request->password, json_decode($request->getContent())->tag ?? '');
    })->middleware('auth:sanctum');*/

    Route::post('/v1/get_lock_list', [CallbackApiController::class, 'getLockList'])->middleware('auth:sanctum');


    Route::middleware([CodesCounter::class])->group(function () {

        Route::post('/v1/add_code_to_lock', [CallbackApiController::class, 'addCodeToLock'])->middleware('auth:sanctum');

        Route::post('/v1/change_code', [CallbackApiController::class, 'changeCode'])->middleware('auth:sanctum');

        Route::post('/v1/set_lock_passage_mode_on', [CallbackApiController::class, 'passageModeOn'])->middleware('auth:sanctum');

        Route::post('/v1/set_lock_passage_mode_off', [CallbackApiController::class, 'passageModeOff'])->middleware('auth:sanctum');

        Route::post('/v1/delete_code_from_lock', [CallbackApiController::class, 'deleteCode'])->middleware('auth:sanctum');

        Route::post('/v1/open_lock', [CallbackApiController::class, 'openLock'])->middleware('auth:sanctum');

        Route::post('/v1/get_lock_events', [SimpleApiController::class, 'getLockEvents'])->middleware('auth:sanctum');

        Route::post('/v1/get_lock_events2', [SimpleApiController::class, 'getLockEvents2'])->middleware('auth:sanctum');

        Route::post('/v1/get_events_by_code', [SimpleApiController::class, 'getEventsByCode'])->middleware('auth:sanctum');
    });


    Route::post('/v1/add_code_packet', [SimpleApiController::class, 'addCodePacket'])->middleware('auth:sanctum');

    Route::post('/v1/set_code_packet', [SimpleApiController::class, 'setCodePacket'])->middleware('auth:sanctum');

    Route::post('/v1/get_codes_count', [SimpleApiController::class, 'getCodesCount'])->middleware('auth:sanctum');



    Route::post('/callback', function (Request $request) {
        info('callback', $request->all());
    });
});
