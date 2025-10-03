<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CodePacket;
use App\Models\LocksCredential;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Arr;


class SettingsController extends Controller
{


    public function show(Request $request)
    {
        $credential = auth()->user()->credential;
        return Inertia::render('Settings', [
            'credential' => $credential
        ]);
    }




    public function refreshToken(Request $request)
    {
        $token = auth()->user()->createToken('ttlock');
        return response()->json(['token' => $token->plainTextToken, 'status' => true, 'user_id' => auth()->user()->id], 200);
    }






    public function saveCredential(Request $request)
    {
       try {
      
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',

        ], [
            'email.required' => 'Не указана учетная запись.',
            'email.email' => 'Не верный формат email.',
            'password.required' => 'Не указан пароль.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => Arr::toCssClasses($validator->errors()->all())
            ], 200);
        }

        $validated = $validator->safe()->only(['email', 'password']);

        if (!testCredential($validated['email'], $validated['password'])) {

            return response()->json([
                'status' => false,
                'error' => 1,
                'message' => 'Error login to TTLock',
                'msg' => 'Не удалось залогиниться в облако TTLock',
            ], 200);
        }

        $credential = LocksCredential::updateOrCreate(['user_id' => auth()->user()->id], ['login' => $validated['email'], 'password' => $validated['password']]);
        if (updateRefreshToken($credential)) {
            return response()->json([
                'status' => true,
                'error' => 0,
                'message' => 'User created successfully',
                'msg' => 'Учетная запись ttlock сорхранена',
            ], 200);
        } else

            return response()->json([
                'status' => false,
                'error' => 2,
                'message' => 'Failed to set TTLock credential',
                'msg' => 'Не получилось запистать данные для авторизации в TTLOCK',
            ], 200);
          } catch (\Exception $e) {
         
            return  response()->json(['status' => false, 'error' => 3, 'msg' => 'Неизвестная ошибка', 'message' => 'Something wrong',], 200);
        }
    }
}
