<?php

use Illuminate\Http\Request;
use App\Services\JobsService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::get('/lock_create', function (Request $request) {
    return (new JobsService(1))->createLock();
});





Route::post('/v1/set_callback', function (Request $request) {
    try {
        $callback = json_decode($request->getContent())->callback;
        if ($callback) {
            $request->user()->callback = $callback;
            $request->user()->save();
            return json_encode(['status' => true]);
        } else   return json_encode(['status' => false]);
    } catch (Exception $e) {
        return json_encode(['status' => false]);
    }
})->middleware('auth:sanctum');




Route::post('/v1/get_token', function (Request $request) {
    try {
        $credentials['email'] = json_decode($request->getContent())->email;
        $credentials['password'] = json_decode($request->getContent())->password;

        if (Auth::attempt($credentials)) {
            $token = auth()->user()->createToken('ttlock');
            return json_encode(['token'=>$token->plainTextToken,'status' => true, 'user_id'=> auth()->user()->id]);
        }
        else   return json_encode(['status' => false]);

    } catch (Exception $e) {
        return json_encode(['status' => false]);
    }
});



Route::post('/v1/get_lock_list', function (Request $request) {
  return  (new JobsService(1))->getLockList();
})->middleware('auth:sanctum');;



Route::post('/v1/add_code_to_lock', function (Request $request) {

    isset(json_decode($request->getContent())->begin) ? $begin= json_decode($request->getContent())->begin : $begin = null;
    isset(json_decode($request->getContent())->end) ? $end= json_decode($request->getContent())->end : $end = null;
    return (new JobsService(1))->addKeyToLock( json_decode($request->getContent())->lock_id, json_decode($request->getContent())->code, $begin,$end);
})->middleware('auth:sanctum');;


Route::post('/v1/set_lock_passage_mode_on', function (Request $request) {
    return  (new JobsService(1))->setPassageModeOn($request->lock_id);
})->middleware('auth:sanctum');;

Route::post('/v1/set_lock_passage_mode_off', function (Request $request) {
    return (new JobsService(1))->setPassageModeOff($request->lock_id);
})->middleware('auth:sanctum');;

Route::post('/v1/delete_code_from_lock', function (Request $request) {
    return (new JobsService(1))->deleteKey($request->lock_id, $request->code_id);
})->middleware('auth:sanctum');;



Route::post('/v1/create_credential', function (Request $request) {
    return (new JobsService(1))->createCredential($request->user, $request->password);
})->middleware('auth:sanctum');;







Route::post('/callback', function (Request $request) {
    info('callback', $request->all());
});
