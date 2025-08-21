<?php

namespace App\Jobs;




use Illuminate\Bus\Queueable;
use App\Models\LocksCredential;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\LockJob;
use Illuminate\Support\Facades\Http;


class CreateCredentialJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id, private string $user, private string $password, private int $user_id) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($job = LockJob::find($this->job_id)) {

            $credential = LocksCredential::updateOrCreate(['user_id' => $this->user_id], ['login' => $this->user, 'password' => $this->password]);
            $status = updateRefreshToken($credential);


            $result['job'] = $job->job_id;
            $result['method'] = 'create_credential';
            $result['data'] = ['status'=>$status];
            Http::withBody(json_encode($result), 'application/json')
                //                ->withOptions([
                //                    'headers' => ''
                //                ])
                ->post($job->user->callback);
        }
    }
}
