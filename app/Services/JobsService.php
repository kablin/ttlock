<?php


namespace App\Services;




use App\Models\Lock;
use App\Models\LockJob;
use App\Models\LocksToken;
use Carbon\Carbon;
use App\Jobs\CreateLockJob;
use App\Jobs\SetJobStatus;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

class JobsService
{
    public function __construct( private int $user_id ) {}



    private function startLockJob() 
    {
       return LockJob::create(['user_id'=>$this->user_id]);

    }


    public function createLock()
    {
        $uuid = $this->startLockJob();
        info($uuid->job_id);

        CreateLockJob::dispatch()->onQueue('default')->chain([
            new SetJobStatus($uuid->id, true)
        ]);

    }


}