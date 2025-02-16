<?php

namespace App\Jobs;




use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\LockJob;
use App\Models\Lock;
use App\Services\TTLockService;

class DeleteKeyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id, private int $lock_id,, private int  $pwdID) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if ($job = LockJob::find($this->job_id)) {

            $lock = Lock::find($this->lock_id);
            if (!$this->lock_id) {
                $data['job'] = $job->job_id;
                $data['data'] = 'Lock not found';

                Http::withBody(json_encode($data), 'application/json')
                    //                ->withOptions([
                    //                    'headers' => ''
                    //                ])
                    ->post($job->user->callback);

                return;
            }

            $servise =  new TTLockService($job->user);

            $rezult = $servise->deleteKey( $lock,$pwdID);


            $data['job'] = $job->job_id;
            $data['data'] =  $rezult;

            Http::withBody(json_encode($data), 'application/json')
                //                ->withOptions([
                //                    'headers' => ''
                //                ])
                ->post($job->user->callback);
        }
    }
}
