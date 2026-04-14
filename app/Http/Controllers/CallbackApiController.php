<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CodePacket;
use App\Models\LockApiLog;
use App\Models\LockPinCode;
use App\Models\LocksCredential;
use App\Services\JobsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;



class CallbackApiController extends Controller
{



    /*
    public function getJobResult($job_id)
    {
        try {

 
            $job = auth()->user()->jobs()->where('job_id', $job_id)->first();

            if ($job && $job->status) {
                return response()->json(['status' => true, 'data' => $job->data], 200);
            }

            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => 'Неизвестная ошибка'], 200);
        }
    }
*/


    public function getLockList(Request $request)
    {
        try {


            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'getLockList',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);

            return (new JobsService(auth()->user()->id))->getLockList(json_decode($request->getContent())->tag ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => 'Неизвестная ошибка', 'msg' => 'Неизвестная ошибка'], 200);
        }
    }


    public function getCodesList(Request $request)
    {
        try {
            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'getCodesList',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);

            $validator = Validator::make($request->all(), [
                'page_number' => 'required|integer',
                // 'page_size' => 'required|integer',
                'lock_id' => 'required|integer',
                'tag' => 'nullable',

            ], [
                'page_number.integer' => 'page_number не число.',
                'page_number.required' => 'Не указан page_number.',
                // 'page_size.integer' => 'page_size не число.',
                //  'page_size.required' => 'Не указан page_size.',
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => Arr::toCssClasses($validator->errors()->all()),
                    'error' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            //$validated = $validator->safe()->only(['lock_id', 'page_number', 'page_size',  'tag']);
            $validated = $validator->safe()->only(['lock_id', 'page_number',   'tag']);
            return (new JobsService(auth()->user()->id))->getCodesList($validated['lock_id'], $validated['page_number'], 100, $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => 'Неизвестная ошибка', 'msg' => 'Неизвестная ошибка'], 200);
        }
    }






    public function createBooking(Request $request)
    {
        try {
            //2025-08-28 15:43

            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'createBooking',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);


            $validator = Validator::make($request->all(), [
                'begin_date'       => 'required|date|date_format:Y-m-d',
                'end_date'         => 'required|date|date_format:Y-m-d|after_or_equal:begin_date',
                'arrival_time'     => 'required|date_format:H:i',
                'departure_time'   => 'required|date_format:H:i',
                'code' => 'nullable|integer',
                'code_name' => 'nullable|string',
                'tag' => 'nullable',
                'utc' => 'nullable|integer',
                'realty_id' => 'required|integer',
                'rent_id' => 'required|integer',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['tag', 'utc', 'code_name', 'code', 'realty_id', 'rent_id', 'begin_date', 'end_date', 'arrival_time', 'departure_time']);
            if (!isset($validated['code'])) $validated['code'] =  random_int(1000, 9999);

            return (new JobsService(auth()->user()->id))->createBooking($validated);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => 'Неизвестная ошибка'], 200);
        }
    }




    public function changeBooking(Request $request, $rent_id)
    {
        try {
            //2025-08-28 15:43

            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'changeBooking',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);


            $validator = Validator::make($request->all(), [
                'begin_date'       => 'required|date|date_format:Y-m-d',
                'end_date'         => 'required|date|date_format:Y-m-d|after_or_equal:begin_date',
                'arrival_time'     => 'required|date_format:H:i',
                'departure_time'   => 'required|date_format:H:i',
                'code' => 'nullable|integer',
                'code_name' => 'nullable|string',
                'tag' => 'nullable',
                'utc' => 'nullable|integer',
                'realty_id' => 'required|integer',
                'rent_id' => 'required|integer',
                'status' => 'required|string',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['status', 'tag', 'utc', 'code_name', 'code', 'realty_id', 'rent_id', 'begin_date', 'end_date', 'arrival_time', 'departure_time']);
            if (!isset($validated['code'])) {
                $pin = LockPinCode::where('rent_id', $validated['rent_id'])->fisrt();
                $validated['code'] = $pin?->pin_code ?? random_int(1000, 9999);
            }

            if ($validated['status'] = 'canceled')
                return (new JobsService(auth()->user()->id))->cancelBooking($validated);

            else return (new JobsService(auth()->user()->id))->changeBooking($validated);


        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => 'Неизвестная ошибка', 'msg' => 'Неизвестная ошибка'], 200);
        }
    }



    public function addCodeToLock(Request $request)
    {
        try {
            //2025-08-28 15:43

            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'addCodeToLock',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);


            $validator = Validator::make($request->all(), [
                'begin' => 'date_format:Y-m-d H:i',
                'end' => 'date_format:Y-m-d H:i',
                'code' => 'nullable|integer',
                'code_name' => 'nullable|string',
                'tag' => 'nullable',
                'utc' => 'nullable|integer',
                'lock_id' => 'required|integer',

            ], [
                'begin.date_format' => 'Не верный формат даты -  "2025-07-23 18:07".',
                'end.date_format' => 'Не верный формат даты -  "2025-07-23 18:07". ',
                'lock_id.integer' => 'lock_id не число.',
                'code.integer' => 'code не число.',
                'lock_id.required' => 'Не указан lock_id.',
                'code.required' => 'Не указан code.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => Arr::toCssClasses($validator->errors()->all()),
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['code', 'lock_id', 'begin', 'end', 'code_name', 'tag', 'utc']);
            if (!$validated['code']) $validated['code'] =  random_int(1000, 9999);

            return (new JobsService(auth()->user()->id))->addKeyToLock($validated['lock_id'], $validated['code'], $validated['code_name'] ??  'Ключ от Renty api', $validated['begin'] ?? null, $validated['end'] ?? null, $validated['tag'] ?? '', $validated['utc'] ?? 0);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => 'Неизвестная ошибка', 'msg' => 'Неизвестная ошибка'], 200);
        }
    }


    public function changeCode(Request $request)
    {
        try {
            //2025-08-28 15:43

            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'changeCode',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);



            $validator = Validator::make($request->all(), [
                'begin' => 'date_format:Y-m-d H:i',
                'end' => 'date_format:Y-m-d H:i',
                'code_id' => 'required|integer',
                'lock_id' => 'required|integer',
                'utc' => 'nullable|integer',
                'tag' => 'nullable',

            ], [
                'begin.date_format' => 'Не верный формат даты -  "2025-07-23 18:07".',
                'end.date_format' => 'Не верный формат даты -  "2025-07-23 18:07". ',
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
                'code_id.required' => 'Не указан code.',
                'code_id.integer' => 'code_id не число.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => Arr::toCssClasses($validator->errors()->all()),
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['code_id', 'lock_id', 'begin', 'end',  'tag', 'utc']);

            return (new JobsService(auth()->user()->id))->changeCode($validated['lock_id'], $validated['code_id'],  $validated['begin'] ?? null, $validated['end'] ?? null, $validated['tag'] ?? '', $validated['utc'] ?? 0);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => 'Неизвестная ошибка', 'msg' => 'Неизвестная ошибка'], 200);
        }
    }




    public function passageModeOn(Request $request)
    {
        try {
            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'passageModeOn',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);


            $validator = Validator::make($request->all(), [
                'lock_id' => 'required|integer',
                'tag' => 'nullable',
            ], [
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => Arr::toCssClasses($validator->errors()->all()),
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['lock_id', 'tag']);

            return (new JobsService(auth()->user()->id))->setPassageModeOn($validated['lock_id'],  $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => 'Неизвестная ошибка', 'msg' => 'Неизвестная ошибка'], 200);
        }
    }


    public function passageModeOff(Request $request)
    {
        try {


            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'passageModeOff',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);


            $validator = Validator::make($request->all(), [
                'lock_id' => 'required|integer',
                'tag' => 'nullable',
            ], [
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => Arr::toCssClasses($validator->errors()->all()),
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['lock_id', 'tag']);

            return (new JobsService(auth()->user()->id))->setPassageModeOff($validated['lock_id'],  $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => 'Неизвестная ошибка', 'msg' => 'Неизвестная ошибка'], 200);
        }
    }



    public function openLock(Request $request)
    {
        try {

            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'openLock',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);

            $validator = Validator::make($request->all(), [
                'lock_id' => 'required|integer',
                'tag' => 'nullable',
            ], [
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => Arr::toCssClasses($validator->errors()->all()),
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['lock_id', 'tag']);

            return (new JobsService(auth()->user()->id))->openLock($validated['lock_id'],  $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false,  'error' => 'Неизвестная ошибка', 'msg' => 'Неизвестная ошибка'], 200);
        }
    }


    public function deleteCode(Request $request)
    {
        try {

            LockApiLog::create([
                'is_ttlock_result' => false,
                'api_method' => 'deleteCode',
                'user_id' => auth()->user()->id,
                'ip' => json_encode($request->ip()),
                'params' => json_encode($request->all()),
            ]);

            $validator = Validator::make($request->all(), [
                'lock_id' => 'required|integer',
                'code_id' => 'required|integer',
                'tag' => 'nullable',
            ], [
                'lock_id.integer' => 'lock_id не число.',
                'lock_id.required' => 'Не указан lock_id.',
                'code_id.integer' => 'code_id не число.',
                'code_id.required' => 'Не указан code_id.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => Arr::toCssClasses($validator->errors()->all()),
                    'msg' => Arr::toCssClasses($validator->errors()->all())
                ], 200);
            }

            $validated = $validator->safe()->only(['lock_id', 'code_id', 'tag']);

            return (new JobsService(auth()->user()->id))->deleteKey($validated['lock_id'], $validated['code_id'],  $validated['tag'] ?? '');
        } catch (\Exception $e) {
            return response()->json(['status' => false,  'error' => 'Неизвестная ошибка', 'msg' => 'Неизвестная ошибка'], 200);
        }
    }
}
