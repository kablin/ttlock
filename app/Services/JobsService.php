<?php


namespace App\Services;




use App\Models\Lock;
use App\Models\LockJob;
use App\Models\User;
use App\Models\LocksToken;
use Carbon\Carbon;
use App\Jobs\CreateLockJob;
use App\Jobs\GetLockListJob;
use App\Jobs\CreateCredentialJob;
use App\Jobs\SetStatusJob;
use App\Jobs\SetPassageModeOffJob;
use App\Jobs\SetPassageModeOnJob;
use App\Jobs\RefreshLockTokenJob;
use App\Jobs\AddKeyToLockJob;
use App\Jobs\DeleteKeyJob;
use App\Models\LockEvent;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

class JobsService
{
    public function __construct(private int $user_id = 0) {}



    private function startLockJob(string $task)
    {
        return LockJob::create(['user_id' => $this->user_id, 'task' => $task]);
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



    public function createLock()
    {
        $uuid = $this->startLockJob('createLock');
        info($uuid->job_id);

        CreateLockJob::dispatch()->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ]);

        return response()->json(['job_id' => $uuid->job_id],200);
    }


    public function getLockList()
    {
        $uuid = $this->startLockJob('getLockList');
        info($uuid->job_id);

        GetLockListJob::dispatch($uuid->id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id],200);
    }



    public function refreshLockTocken(int $id)
    {
        $uuid = $this->startLockJob('refreshLockTocken');

        RefreshLockTokenJob::dispatch($id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ]);
    }


    public function addKeyToLock($lock_id, $code, $begin, $end)
    {
        $uuid = $this->startLockJob('addKeyToLock');
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();

        AddKeyToLockJob::dispatch($uuid->id, $lock ? $lock?->id : 0, $code, $begin, $end)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id],200);
    }


    public function setPassageModeOn($lock_id)
    {
        $uuid = $this->startLockJob('setPassageModeOn');
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        SetPassageModeOnJob::dispatch($uuid->id, $lock ? $lock?->id : 0)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id],200);
    }


    public function setPassageModeOff($lock_id)
    {
        $uuid = $this->startLockJob('setPassageModeOff');
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        SetPassageModeOffJob::dispatch($uuid->id, $lock ? $lock?->id : 0)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id],200);
    }

    public function deleteKey($lock_id, $pwdID)
    {
        $uuid = $this->startLockJob('deleteKey');
        $lock = auth()->user()->locks->where('lock_id', $lock_id)->first();
        DeleteKeyJob::dispatch($uuid->id, $lock ? $lock?->id : 0, $pwdID)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true : false)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id],200);
    }

    public function createCredential($user, $password)
    {
        $uuid = $this->startLockJob('createCredential');
        $user_id = auth()->user()->id;
        CreateCredentialJob::dispatch($uuid->id, $user, $password, $user_id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ])->delay($this->getDelay());

        return response()->json(['job_id' => $uuid->job_id],200);
    }



    public static function getLockEvents($lock_id, $lock_type, $record_type)
    {
        
        $evets = LockEvent::select('id','lock_id','record_type_from_lock','record_type','success','username','keyboard_pwd','lock_date')->where('lock_id',$lock_id);
        if ($lock_type) $evets = $evets->where('record_type_from_lock',$lock_type);
        if ($record_type) $evets = $evets->where('record_type',$record_type);
        return  $evets->orderBy('id','DESC')->take(100)->get();
        
    }




}
