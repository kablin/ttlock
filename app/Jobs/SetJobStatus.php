<?php

namespace App\Jobs;



use App\Models\LockJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SetJobStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $id, private bool $status) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($job = LockJob::find($this->id)) {
            $job->status = $this->status;
            $job->save();
            info('status is  Done');
            $data['job'] = $job->job_id;
            $data['data'] = 'status is  Done';

            Http::withBody(json_encode($data), 'application/json')
                //                ->withOptions([
                //                    'headers' => ''
                //                ])
                ->post($job->user->callback);
        }
    }
}
