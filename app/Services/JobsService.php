<?php


namespace App\Services;




use App\Models\Lock;
use App\Models\LockJob;
use App\Models\LocksToken;
use Carbon\Carbon;
use App\Jobs\CreateLockJob;
use App\Jobs\GetLockListJob;
use App\Jobs\SetStatusJob;
use App\Jobs\SetPassageModeOffJob;
use App\Jobs\SetPassageModeOnJob;
use App\Jobs\RefreshLockTokenJob;
use App\Jobs\AddKeyToLockJob;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

class JobsService
{
    public function __construct( private int $user_id = 0 ) {}



    private function startLockJob(string $task) 
    {
       return LockJob::create(['user_id'=>$this->user_id,'task'=>$task]);

    }


    public function createLock()
    {
        $uuid = $this->startLockJob('createLock');
        info($uuid->job_id);
        
        CreateLockJob::dispatch()->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ]);

    }


    public function getLockList()
    {
        $uuid = $this->startLockJob('getLockList');
        info($uuid->job_id);
        
        GetLockListJob::dispatch($uuid->id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ]);

    }
    


    public function refreshLockTocken(int $id)
    {
        $uuid = $this->startLockJob('refreshLockTocken');
        
        RefreshLockTokenJob::dispatch($id)->onQueue('default')->chain([
            new SetStatusJob($uuid->id, true)
        ]);

    }


    public function addKeyToLock($lock_id,$code,$begin,$end)
    {
        $uuid = $this->startLockJob('addKeyToLock');
        $lock = auth()->user->locks->find($lock_id);
        AddKeyToLockJob::dispatch($uuid->id, $lock ? $lock?->id : 0 ,$code, $begin,$end)->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true: false)
        ]);

    }


    public function setPassageModeOn($lock_id)
    {
        $uuid = $this->startLockJob('setPassageModeOn');
        $lock = auth()->user->locks->find($lock_id);
        SetPassageModeOnJob::dispatch($uuid->id, $lock ? $lock?->id : 0 )->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true: false)
        ]);

    }


    public function setPassageModeOff($lock_id)
    {
        $uuid = $this->startLockJob('setPassageModeOff');
        $lock = auth()->user->locks->find($lock_id);
        SetPassageModeOffJob::dispatch($uuid->id, $lock ? $lock?->id : 0 )->onQueue('default')->chain([
            new SetStatusJob($uuid->id,  $lock ? true: false)
        ]);

    }

   


}