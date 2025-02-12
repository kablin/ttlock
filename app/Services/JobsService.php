<?php


namespace App\Services;




use App\Models\Lock;
use App\Models\LockJob;
use App\Models\LocksToken;
use Carbon\Carbon;
use App\Jobs\CreateLockJob;
use App\Jobs\GetLockListJob;
use App\Jobs\SetJobStatus;
use App\Jobs\RefreshLockToken;
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
            new SetJobStatus($uuid->id, true)
        ]);

    }


    public function getLockList()
    {
        $uuid = $this->startLockJob('getLockList');
        info($uuid->job_id);
        
        GetLockListJob::dispatch()->onQueue('default')->chain([
            new SetJobStatus($uuid->id, true)
        ]);

    }
    


    public function refreshLockTocken(int $id)
    {
        $uuid = $this->startLockJob('startLockJob');
        
        RefreshLockToken::dispatch($id)->onQueue('default')->chain([
            new SetJobStatus($uuid->id, true)
        ]);

    }


}