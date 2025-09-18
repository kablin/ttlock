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





Route::middleware(['throttle:5,1'])->group(function () {


    Route::post('/v1/create_user', [AuthController::class, 'CreateUser']);

    Route::post('/v1/set_callback', [SimpleApiController::class, 'setCallback'])->middleware('auth:sanctum');;

    Route::post('/v1/get_token', [SimpleApiController::class, 'getToken']);
});





Route::middleware(['throttle:20,1'])->group(function () {



    Route::post('/v1/get_lock_list', [CallbackApiController::class, 'getLockList'])->middleware('auth:sanctum');


    /*Route::post('/v1/create_credential', function (Request $request) {
        return (new JobsService(auth()->user()->id))->createCredential($request->user, $request->password, json_decode($request->getContent())->tag ?? '');
    })->middleware('auth:sanctum');*/




    Route::middleware([CodesCounter::class])->group(function () {


        Route::post('/v1/add_code_to_lock', [CallbackApiController::class, 'addCodeToLock'])->middleware('auth:sanctum');

        Route::post('/v1/set_lock_passage_mode_on', [CallbackApiController::class, 'passageModeOn'])->middleware('auth:sanctum');

        Route::post('/v1/set_lock_passage_mode_off', [CallbackApiController::class, 'passageModeOff'])->middleware('auth:sanctum');

        Route::post('/v1/delete_code_from_lock', [CallbackApiController::class, 'deleteCode'])->middleware('auth:sanctum');

        Route::post('/v1/open_lock', [CallbackApiController::class, 'openLock'])->middleware('auth:sanctum');

        
        
        
        
        
        
        
        
        
        Route::post('/v1/get_lock_events', [SimpleApiController::class, 'getLockEvents'])->middleware('auth:sanctum');





        Route::post('/v1/get_lock_events2', function (Request $request) {

            if (!isset(json_decode($request->getContent())->lock_ids))     return response()->json(['status' => false, 'msg' => "Не указан lock_id"], 200);
            if (!isset(json_decode($request->getContent())->type))     return response()->json(['status' => false, 'msg' => "Не указан type"], 200);

            if (json_decode($request->getContent())->type == 1 &&  !isset(json_decode($request->getContent())->code)) return response()->json(['status' => false, 'msg' => "Не указан code"], 200);

            isset(json_decode($request->getContent())->code) ? $code = json_decode($request->getContent())->code : $code = null;

            return response()->json(JobsService::getLockEvents2(json_decode($request->getContent())->lock_ids,  json_decode($request->getContent())->type, $code), 200);
        })->middleware('auth:sanctum');




        Route::post('/v1/get_events_by_code', function (Request $request) {


            if (!isset(json_decode($request->getContent())->code))     return response()->json(['status' => false, 'msg' => "Не указан code"], 200);
            if (!isset(json_decode($request->getContent())->lock_id))     return response()->json(['status' => false, 'msg' => "Не указан lock_id"], 200);

            $lock = auth()->user()->locks->where('lock_id', json_decode($request->getContent())->lock_id)->first();

            if (!$lock) return response()->json(['status' => false, 'msg' => "Неизвестный замок"], 200);

            return response()->json(JobsService::getCodeEvents(json_decode($request->getContent())->lock_id,  json_decode($request->getContent())->code), 200);
        })->middleware('auth:sanctum');



    });





     Route::post('/v1/add_code_packet', [SimpleApiController::class, 'addCodePacket'])->middleware('auth:sanctum');


     

    Route::post('/v1/set_code_packet', function (Request $request) {

        if (!isset(json_decode($request->getContent())->codes_count))     return response()->json(['status' => false, 'msg' => "Не указан codes_count"], 200);

        return response()->json(JobsService::setCodesCount(json_decode($request->getContent())->codes_count), 200);
    })->middleware('auth:sanctum');





    Route::post('/v1/get_codes_count', function (Request $request) {
        return response()->json(JobsService::getCodesCount(), 200);
    })->middleware('auth:sanctum');



    Route::post('/callback', function (Request $request) {
        info('callback', $request->all());
    });
});
