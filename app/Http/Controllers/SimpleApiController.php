<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CodePacket;
use App\Models\LocksCredential;
use App\Services\JobsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;



class SimpleApiController extends Controller
{


    public function setCallback(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'callback' => 'required|string',

            ], [
                'callback.required' => 'Не указан callback.',
                'callback.string' => 'callback не строка.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['callback']);

            $request->user()->callback = $validated['callback'];
            $request->user()->save();
            return response()->json(['status' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }



    public function getToken(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required',

            ], [
                'email.required' => 'Не указан email.',
                'email.email' => 'email не верный формат.',
                'email.string' => 'email не строка.',
                'password.required' => 'Не указан password.',
            ]);



            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['email', 'password']);


            if (Auth::attempt($validated)) {
                $token = auth()->user()->createToken('ttlock');
                return response()->json(['token' => $token->plainTextToken, 'status' => true, 'user_id' => auth()->user()->id], 200);
            } else   return response()->json(['status' => false,  'msg' => 'Неизвестная ошибка'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }




    public function getLockEvents(Request $request)
    {
        try {
            //2025-08-28 15:43
            $validator = Validator::make($request->all(), [
                'personal' => 'boolean',
                'lock_id' => 'required|integer',
            ], [
                'personal.boolean' => 'personal не boolean.',
                'lock_id.required' => 'Не указан lock_id.',
                'lock_id.integer' => 'lock_id не число.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['lock_record_type', 'record_type', 'personal', 'lock_id']);

            $lock = auth()->user()->locks->where('lock_id', $validated['lock_id'])->first();

            if (!$lock) return response()->json(['status' => false, 'msg' => "Неизвестный замок"], 200);

            return response()->json(JobsService::getLockEvents($validated['lock_id'],   $validated['lock_record_type'] ?? null, $validated['record_type'] ?? null, $validated['personal'] ?? false), 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }






    public function addCodePacket(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'expired_at' => 'required|date_format:Y-m-d H:i:s',
                'codes_count' => 'required|integer',
            ], [
                'expired_at.date_format' => 'Не верный формат даты -  "2025-07-23 18:07:00".',
                'expired_at.required' => 'Не указан expired_at.',
                'codes_count.required' => 'Не указан codes_count.',
                'codes_count.integer' => 'codes_count не число.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['codes_count', 'expired_at']);

            return response()->json(JobsService::addCodesCount($validated['codes_count'],  $validated['expired_at']), 200);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }
}
