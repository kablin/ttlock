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

class GetLockListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if ($job = LockJob::find($this->job_id)) {

            $data['job'] = $job->job_id;
            $data['data'] = 'list is Done';

            Http::withBody(json_encode($data), 'application/json')
                //                ->withOptions([
                //                    'headers' => ''
                //                ])
                ->post($job->user->callback);
        }
    }
}
