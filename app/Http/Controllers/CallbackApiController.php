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



class CallbackApiController extends Controller
{


    public function getLockList(Request $request)
    {
        try {
            return (new JobsService(auth()->user()->id))->getLockList(json_decode($request->getContent())->tag ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }



    public function addCodeToLock(Request $request)
    {
        try {
            //2025-08-28 15:43
            $validator = Validator::make($request->all(), [
                'begin' => 'date_format:Y-m-d H:i:s',
                'end' => 'date_format:Y-m-d H:i:S',
                'code' => 'required',
                'lock_id' => 'required|integer',

            ], [
                'begin.date_format' => 'Не верный формат даты -  "2025-07-23 18:07:00".',
                'end.date_format' => 'Не верный формат даты -  "2025-07-23 18:07:00". ',
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
                'code.required' => 'Не указан code.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['code', 'lock_id', 'begin', 'end', 'code_name', 'tag']);

            return (new JobsService(auth()->user()->id))->addKeyToLock($validated['lock_id'], $validated['code'], $validated['code_name'] ??  'Ключ от Renty api', $validated['begin'] ?? null, $validated['end'] ?? null, $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }


     public function changeCode(Request $request)
    {
        try {
            //2025-08-28 15:43
            $validator = Validator::make($request->all(), [
                'begin' => 'date_format:Y-m-d H:i:s',
                'end' => 'date_format:Y-m-d H:i:S',
                'code_id' => 'required|integer',
                'lock_id' => 'required|integer',

            ], [
                'begin.date_format' => 'Не верный формат даты -  "2025-07-23 18:07:00".',
                'end.date_format' => 'Не верный формат даты -  "2025-07-23 18:07:00". ',
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
                'code_id.required' => 'Не указан code.',
                'code_id.integer' => 'code_id не число.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['code_id', 'lock_id', 'begin', 'end',  'tag']);

            return (new JobsService(auth()->user()->id))->changeCode($validated['lock_id'], $validated['code_id'],  $validated['begin'] ?? null, $validated['end'] ?? null, $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }

    


    public function passageModeOn(Request $request)
    {
        try {
            //2025-08-28 15:43
            $validator = Validator::make($request->all(), [
                'lock_id' => 'required|integer',
            ], [
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['lock_id', 'tag']);

            return (new JobsService(auth()->user()->id))->setPassageModeOn($validated['lock_id'],  $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }


    public function passageModeOff(Request $request)
    {
        try {
            //2025-08-28 15:43
            $validator = Validator::make($request->all(), [
                'lock_id' => 'required|integer',
            ], [
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['lock_id', 'tag']);

            return (new JobsService(auth()->user()->id))->setPassageModeOff($validated['lock_id'],  $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }



     public function openLock(Request $request)
    {
        try {
            //2025-08-28 15:43
            $validator = Validator::make($request->all(), [
                'lock_id' => 'required|integer',
            ], [
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['lock_id', 'tag']);

            return (new JobsService(auth()->user()->id))->openLock($validated['lock_id'],  $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }

    }


    public function deleteCode(Request $request)
    {
        try {
            //2025-08-28 15:43
            $validator = Validator::make($request->all(), [
                'lock_id' => 'required|integer',
                'code_id' => 'required|integer',
            ], [
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
                'code_id.integer' => 'code_id не число.',
                'code_id.required' => 'Не указан code_id.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['lock_id','code_id', 'tag']);

            return (new JobsService(auth()->user()->id))->deleteKey($validated['lock_id'], $validated['code_id'],  $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
      
    }
}
