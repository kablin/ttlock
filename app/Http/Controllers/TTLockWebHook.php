<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Lock;
//use App\Models\LockPinCode;

use Illuminate\Http\Request;


class TTLockWebHook extends Controller
{
  public function __invoke(Request $request)
  {

    try {
      if (isset($request->notifyType) && isset($request->admin)) {
        $lock = Lock::where([
          'lock_id' => $request->lockId,

        ])->first();

        /* @var Lock $lock **/
        if ($lock) {
          $info = json_decode($request->records)[0] ?? null;

          $lock->events()->create([
            'record_id' => $info->recordId ?? null,
            'record_type_from_lock' => $info->recordTypeFromLock ?? null,
            'record_type' => $info->recordType ?? null,
            'success' => $info->success ?? null,
            'username' => $info->username ?? null,
            'keyboard_pwd' => $info->keyboardPwd ?? null,
            'lock_date' => $info->lockDate ?? null,
            'server_date' => $info->serverDate ?? null,
            'is_webhook' => true
          ]);

          //Тут надо найти с таким статусом бронь и изменить
     /*     $lockPinCode = LockPinCode::where([
            'pin_code' => $info?->keyboardPwd,
            'apartment_id' => $lock->apartment_id
          ])->first();
*/
        }
      }
//					echo '"success"';
      //return response('success', 200);
    } catch (\Exception $exception) {
   //   logChannel('ttlock-webhook', $exception->getMessage());

    }

    return response('success')->setStatusCode(200);


  }
}
