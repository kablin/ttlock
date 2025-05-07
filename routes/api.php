<?php

use Illuminate\Http\Request;
use App\Services\JobsService;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::get('/lock_create', function (Request $request) {
    return (new JobsService(auth()->user()->id))->createLock();
})->middleware('auth:sanctum');







Route::middleware(['throttle:5,1'])->group(function () {


    Route::post('/v1/create_user', [AuthController::class, 'CreateUser']);



    Route::post('/v1/set_callback', function (Request $request) {
        try {
            $callback = json_decode($request->getContent())->callback;
            if ($callback) {
                $request->user()->callback = $callback;
                $request->user()->save();
                return response()->json(['status' => true], 200);
            } else  return response()->json(['status' => false], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false], 200);
        }
    })->middleware('auth:sanctum');




    Route::post('/v1/get_token', function (Request $request) {
        try {
            $credentials['email'] = json_decode($request->getContent())->email;
            $credentials['password'] = json_decode($request->getContent())->password;

            if (Auth::attempt($credentials)) {
                $token = auth()->user()->createToken('ttlock');
                return response()->json(['token' => $token->plainTextToken, 'status' => true, 'user_id' => auth()->user()->id], 200);
            } else   return response()->json(['status' => false], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false], 200);
        }
    });
});





Route::middleware(['throttle:20,1'])->group(function () {



    Route::post('/v1/get_lock_list', function (Request $request) {
        return (new JobsService(auth()->user()->id))->getLockList();
    })->middleware('auth:sanctum');;



    Route::post('/v1/add_code_to_lock', function (Request $request) {

        isset(json_decode($request->getContent())->begin) ? $begin = json_decode($request->getContent())->begin : $begin = null;
        isset(json_decode($request->getContent())->end) ? $end = json_decode($request->getContent())->end : $end = null;

        if (!isset(json_decode($request->getContent())->code))     return response()->json(['status' => false, 'msg' => "code is required"], 200);
        if (!isset(json_decode($request->getContent())->lock_id))     return response()->json(['status' => false, 'msg' => "lock_id is required"], 200);
        return (new JobsService(auth()->user()->id))->addKeyToLock(json_decode($request->getContent())->lock_id, json_decode($request->getContent())->code, $begin, $end);
    })->middleware('auth:sanctum');


    Route::post('/v1/set_lock_passage_mode_on', function (Request $request) {
        if (!isset(json_decode($request->getContent())->lock_id))     return response()->json(['status' => false, 'msg' => "lock_id is required"], 200);
        return (new JobsService(auth()->user()->id))->setPassageModeOn($request->lock_id);
    })->middleware('auth:sanctum');

    Route::post('/v1/set_lock_passage_mode_off', function (Request $request) {
        if (!isset(json_decode($request->getContent())->lock_id))     return response()->json(['status' => false, 'msg' => "lock_id is required"], 200);
        return (new JobsService(auth()->user()->id))->setPassageModeOff($request->lock_id);
    })->middleware('auth:sanctum');

    Route::post('/v1/delete_code_from_lock', function (Request $request) {

        if (!isset(json_decode($request->getContent())->lock_id))     return response()->json(['status' => false, 'msg' => "lock_id is required"], 200);
        if (!isset(json_decode($request->getContent())->code_id))     return response()->json(['status' => false, 'msg' => "code_id is required"], 200);

        return (new JobsService(auth()->user()->id))->deleteKey($request->lock_id, $request->code_id);
    })->middleware('auth:sanctum');



    Route::post('/v1/create_credential', function (Request $request) {
        return (new JobsService(auth()->user()->id))->createCredential($request->user, $request->password);
    })->middleware('auth:sanctum');


    Route::post('/v1/get_lock_events', function (Request $request) {

        isset(json_decode($request->getContent())->lock_record_type) ? $lock_record_type = json_decode($request->getContent())->lock_record_type : $lock_record_type = null;
        isset(json_decode($request->getContent())->record_type) ? $record_type = json_decode($request->getContent())->record_type : $record_type = null;

        isset(json_decode($request->getContent())->personal) ? $personal = true : $personal = null;

        if (!isset(json_decode($request->getContent())->lock_id))     return response()->json(['status' => false, 'msg' => "lock_id is required"], 200);

        $lock = auth()->user()->locks->where('lock_id', json_decode($request->getContent())->lock_id)->first();

        if (!$lock) return response()->json(['status' => false, 'msg' => "unknown lock"], 200);

        return response()->json(JobsService::getLockEvents(json_decode($request->getContent())->lock_id,  $lock_record_type, $record_type, $personal), 200);
    })->middleware('auth:sanctum');




    Route::post('/v1/get_lock_events2', function (Request $request) {

        if (!isset(json_decode($request->getContent())->lock_ids))     return response()->json(['status' => false, 'msg' => "lock_id is required"], 200);
        if (!isset(json_decode($request->getContent())->type))     return response()->json(['status' => false, 'msg' => "type is required"], 200);

        if (json_decode($request->getContent())->type==1 &&  !isset(json_decode($request->getContent())->code) ) return response()->json(['status' => false, 'msg' => "code is required"], 200);

        isset(json_decode($request->getContent())->code) ? $code = json_decode($request->getContent())->code : $code = null;

        return response()->json(JobsService::getLockEvents2(json_decode($request->getContent())->lock_ids,  json_decode($request->getContent())->type, $code), 200);
    })->middleware('auth:sanctum');


    

    Route::post('/v1/get_events_by_code', function (Request $request) {

      
        if (!isset(json_decode($request->getContent())->code))     return response()->json(['status' => false, 'msg' => "code is required"], 200);
        if (!isset(json_decode($request->getContent())->lock_id))     return response()->json(['status' => false, 'msg' => "lock_id is required"], 200);

        $lock = auth()->user()->locks->where('lock_id', json_decode($request->getContent())->lock_id)->first();

        if (!$lock) return response()->json(['status' => false, 'msg' => "unknown lock"], 200);

        return response()->json(JobsService::getCodeEvents(json_decode($request->getContent())->lock_id,  json_decode($request->getContent())->code), 200);
    })->middleware('auth:sanctum');



    Route::post('/v1/open_lock', function (Request $request) {
        if (!isset(json_decode($request->getContent())->lock_id))     return response()->json(['status' => false, 'msg' => "lock_id is required"], 200);
        return (new JobsService(auth()->user()->id))->openLock($request->lock_id);
    })->middleware('auth:sanctum');



    Route::post('/callback', function (Request $request) {
        info('callback', $request->all());
    });
});
