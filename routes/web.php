<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TTLockWebHook;
use Illuminate\Http\Request;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/tokens/create', function () {
    $token = App\Models\User::find(1)->createToken('test');

    return ['token' => $token->plainTextToken];
});
//token	"1|silpjkiQSCrv3kMTOPrJJZ7UaDS11DsbUIl0XkfU26e44c97"

Route::post('callback/ttlock/{code}', TTLockWebHook::class)->name('webhook.ttlock-code');
