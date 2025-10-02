<?php


namespace App\Services;




use App\Models\Lock;
use App\Models\LockJob;
use App\Models\User;
use App\Models\CodePacket;
use App\Models\LocksToken;
use Carbon\Carbon;
use App\Jobs\CreateLockJob;
use App\Jobs\GetLockListJob;
use App\Jobs\CreateCredentialJob;
use App\Jobs\SetStatusJob;
use App\Jobs\ChangeCodeJob;
use App\Jobs\SetPassageModeOffJob;
use App\Jobs\SetPassageModeOnJob;
use App\Jobs\RefreshLockTokenJob;
use App\Jobs\AddKeyToLockJob;
use App\Jobs\DeleteKeyJob;
use App\Jobs\OpenLockJob;
use App\Models\LockEvent;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

class JobsService
{
    public function __construct(private int $user_id = 0) {}



    private function startLockJob(string $task,  $tag = null)
    {
        return LockJob::create(['user_id' => $this->user_id, 'task' => $task, 'tag' => $tag ? json_encode($tag) : ""]);
    }


    public function getDelay()
    {
        if ($this->user_id) {
            $user = User::find($this->user_id);
            if ($user) {
                $t1 = new Carbon($user->last_query);
                $now = now();
                if ($t1->getTimestamp() >= $now->getTimestamp()) {
                    $user->last_query = $t1->addSeconds(2);
                    $user->save();
                    return  new Carbon($user->last_query);
                } else {
                    $user->last_query = $now;
                    $user->save();
                }
            }
        }

        return $now;
    }



    public function createLock($tag)
    {
        $uuid = $this->startLockJob('createLock', $tag);
        info($uuid->job_id);

        CreateLockJob::dispatch()->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ]);

        return response()->json(['job_id' => $uuid->job_id], 200);
    }


    public function getLockList($tag)
    {
        $uuid = $this->startLockJob('getLockList', $tag);
        info($uuid->job_id);

        GetLockListJob::dispatch($uuid->id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id], 200);
    }



    public function refreshLockTocken(int $id, $tag)
    {
        $uuid = $this->startLockJob('refreshLockTocken', $tag);

        RefreshLockTokenJob::dispatch($id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ]);
    }


    public function addKeyToLock($lock_id, $code, $code_name, $begin, $end, $tag)
    {
        $uuid = $this->startLockJob('addKeyToLock', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();

        AddKeyToLockJob::dispatch(1, $uuid->id, $lock ? $lock?->id : 0, $code, $code_name, $begin, $end)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id], 200);
    }


    public function changeCode($lock_id, $code_id, $code_name, $begin, $end, $tag)
    {
        $uuid = $this->startLockJob('addKeyToLock', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();

        ChangeCodeJob::dispatch(1, $uuid->id, $lock ? $lock?->id : 0, $code_id, $code_name, $begin, $end)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id], 200);
    }



    public function setPassageModeOn($lock_id, $tag)
    {
        $uuid = $this->startLockJob('setPassageModeOn', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        SetPassageModeOnJob::dispatch($uuid->id, $lock ? $lock?->id : 0)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id], 200);
    }


    public function setPassageModeOff($lock_id, $tag)
    {
        $uuid = $this->startLockJob('setPassageModeOff', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        SetPassageModeOffJob::dispatch($uuid->id, $lock ? $lock?->id : 0)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id], 200);
    }

    public function deleteKey($lock_id, $pwdID, $tag)
    {
        $uuid = $this->startLockJob('deleteKey', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        DeleteKeyJob::dispatch(1, $uuid->id, $lock ? $lock?->id : 0, $pwdID)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id], 200);
    }

    public function createCredential($user, $password, $tag)
    {
        $uuid = $this->startLockJob('createCredential', $tag);
        $user_id = auth()->user()->id;
        CreateCredentialJob::dispatch($uuid->id, $user, $password, $user_id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id], 200);
    }



    public static function getLockEvents($lock_id, $lock_type, $record_type, $personal)
    {

        $evets = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->where('lock_id', $lock_id);
        if ($lock_type) $evets = $evets->where('record_type_from_lock', $lock_type);
        if ($record_type && !$personal) $evets = $evets->where('record_type', $record_type);

        if ($personal) {
            $lock = Lock::where('lock_id', $lock_id)->first();
            $pins = $lock->allpincodes->pluck('pin_code')->unique()->toArray();
            $evets = $evets->where('success', true)->whereNotIn('keyboard_pwd', $pins)->where('record_type', 4);
        }
        return  $evets->orderBy('id', 'DESC')->take(100)->get();
    }


    public static function getCodeEvents($lock_id, $code)
    {

        $evets = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->where('lock_id', $lock_id)
            ->where('keyboard_pwd', $code);


        return  $evets->orderBy('id', 'DESC')->take(100)->get();
    }


    public static function getLockEvents2($lock_ids, $type, $code)
    {

        $locks_ = explode(',', $lock_ids);
        foreach ($locks_ as $l) {
            $lock = auth()->user()->locks->where('lock_id', $l)->first();
            if (!$lock) return ['status' => false, 'msg' => "Неизвестный замок"];
        }


        if ($type == 1) {
            if ($code) {
                $event = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->whereIn('lock_id', $locks_)
                    ->where('keyboard_pwd', $code)->where('success', true)->where('record_type_from_lock', 4)->orderBy('lock_date', 'ASC')->first();

                return $event;
            } else return '';
        } else   if ($type == 2) {

            $allpins = [];
            foreach ($locks_ as $l) {
                $lock = Lock::where('lock_id', $l)->first();
                $pins = $lock->allpincodes->pluck('pin_code')->unique()->toArray();

                $allpins = array_merge($allpins, $pins);
            }

            $events = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->whereIn('lock_id', $locks_)
                ->where('success', true)->whereNotIn('keyboard_pwd', $allpins)->where('record_type', 4)->orderBy('id', 'DESC')->take(100)->get();

            return $events;
        } else {
            $events = LockEvent::select('id', 'lock_id', 'record_type_from_lock', 'record_type', 'success', 'username', 'keyboard_pwd', 'lock_date')->whereIn('lock_id', $locks_)
                ->orderBy('id', 'DESC')->take(100)->get();

            return $events;
        }
    }


    public static function addCodesCount($codes_count, $expired_at)
    {

        $code_packet = CodePacket::firstOrCreate(['user_id' => auth()->user()->id]);
        $code_packet->refresh();
        if ($codes_count == -1) $code_packet->count = -100;
        else $code_packet->count = $code_packet->count + $codes_count;
        $code_packet->end = $expired_at;
        $code_packet->save();
        return ['status' => true, 'msg' => "Пакет кодов успешно добавлен", 'codes_count' => $code_packet->count, 'expired_at' => $code_packet->end];
    }


    public static function SetCodesCount($codes_count)
    {

        $code_packet = CodePacket::where(['user_id' => auth()->user()->id])->first();
        if (!$code_packet)   return ['status' => false,];
        $code_packet->refresh();
        if ($codes_count == -1) $code_packet->count = -100;
        else $code_packet->count = $codes_count;
        $code_packet->save();
        return ['status' => true,  'msg' => "Пакет кодов успешно установлен", 'codes_count' => $code_packet->count, 'expired_at' => $code_packet->end];
    }


    public static function getCodesCount()
    {
        $code_packet = CodePacket::firstOrCreate(['user_id' => auth()->user()->id]);
        $code_packet->refresh();
        return ['status' => true, 'codes_count' => $code_packet->count, 'expired_at' => $code_packet->end];
    }



    public function openLock($lock_id, $tag)
    {
        $uuid = $this->startLockJob('openLock', $tag);
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        OpenLockJob::dispatch($uuid->id, $lock ? $lock?->id : 0)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id], 200);
    }
}
