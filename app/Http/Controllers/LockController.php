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
use denis660\Centrifugo\Centrifugo;
use App\Services\TTLockService;
use App\Models\Lock;


class LockController extends Controller
{
    public function lockList(Request $request)
    {
        $locks = auth()->user()->locks;

        $centrifugo =  resolve(Centrifugo::class);
        $token = $centrifugo->generateConnectionToken((string)Auth::id(), 0, [
            'name' => Auth::user()->name,
        ], ['api:get_lock_list-' . (string)Auth::id()]);


        return Inertia::render('Locks', ['locks' => $locks,  'token' => $token]);
    }


    public function lockList_refresh(Request $request)
    {
        $locks = auth()->user()->locks;
        return response()->json($locks);
    }


    public function mapping(Request $request)
    {
        $locks = auth()->user()->locks()->get();
        $rents = auth()->user()->rents()->get();
        return Inertia::render('Mapping', ['all_locks' => $locks, 'rents' => $rents]);
    }

    public function getLockList(Request $request)
    {
        $servise =  new TTLockService(auth()->user());
        $locks_data = $servise->getLockList();

        $mylocks = [];
        if ($locks_data['status'] == true) {
            foreach ($locks_data['data']['list'] as $data) {
                $lock = [];
                $lock['user_id'] =  auth()->user()->id;
                $lock['lock_id'] = $data['lockId'];
                $lock['lock_name'] = $data['lockName'];
                $lock['lock_alias'] = $data['lockAlias'];
                $lock['status'] = true;
                $lock['electric_quantity'] = $data['electricQuantity'];
                $lock['no_key_pwd'] = $data['noKeyPwd'];

                $model = Lock::query()->updateOrCreate(['lock_id' => $data['lockId']], $lock);

                $mylocks[] =  $model->id;

                $model->saveOptionValueByName('error', null);
                $model->saveOptionValueByName('electricQuantity', $data['electricQuantity'] ?? 0);
                $model->saveOptionValueByName('lockAlias', $data['lockAlias']);
                $model->saveOptionValueByName('noKeyPwd', $data['noKeyPwd']);
                $model->saveOptionValueByName('timezoneRawOffset', $data['timezoneRawOffset']);
            }
            Lock::where('user_id', auth()->user()->id)->whereNotIn('id', $mylocks)->delete();

            return response()->json([
                'status' => true,
                'msg' => 'Успешно. Замков получено: ' . count($locks_data['data']['list'])
            ], 200);
        } else
            return response()->json([
                'status' => false,
                'error' => $locks_data['msg'],
                'msg' => $locks_data['msg']
            ], 200);
    }



    public function test(Request $request)
    {
        $code =   random_int(1000, 9999);
        $service =  new TTLockService(User::find(11));
        $key = $service->newKey($code, Lock::find(5), $code);
        return $key;
    }


        public function testdel(Request $request)
    {
        $code =   $request->input('id');
        $service =  new TTLockService(User::find(11));
        $key = $service->deleteKey( Lock::find(5), $code);
        return $key;
    }
}
