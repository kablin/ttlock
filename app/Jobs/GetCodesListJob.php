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
use App\Models\LockPinCode;
use App\Services\TTLockService;

class GetCodesListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $counter, private int $job_id, private int $lock_id, private int $page_number, private int $page_size) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        if ($job = LockJob::find($this->job_id)) {

            $data['job'] = $job->job_id;
            $data['tag'] = json_decode($job->tag);
            $data['method'] = 'get_codes_list';

            $lock = Lock::find($this->lock_id);
            if (!$this->lock_id) {

                $data['data'] = 'Lock not found';
                $data['msg'] = 'Неизвестный замок';
                $data['status'] = false;

                Http::withBody(json_encode($data), 'application/json')
                    ->post($job->user->callback);

                return;
            }

            $servise =  new TTLockService($job->user);

            $rezult = $servise->getKeyList( $lock, $this->page_number,$this->page_size);

            $data['status'] = $rezult['status'];
            $data['data'] =  $rezult;

            Http::withBody(json_encode($data), 'application/json')
                ->post($job->user->callback);

        }
    }
}
