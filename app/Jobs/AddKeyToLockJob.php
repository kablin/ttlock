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

class AddKeyToLockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id, private int $lock_id, private int $code, private $begin = null, private $end = null) {}

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


  


            if (!$job->user->code_packet()->exists())
            {
                $data['job'] = $job->job_id;
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "no codes";

                Http::withBody(json_encode($data), 'application/json')
                ->post($job->user->callback);
                return;
            }

            if ($job->user->code_packet->end < now())
            {
                $data['job'] = $job->job_id;
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "codes expired";

                Http::withBody(json_encode($data), 'application/json')
                ->post($job->user->callback);
                return;
            }
            if ($job->user->code_packet->count < 1 )
            {
                $data['job'] = $job->job_id;
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "no codes";

                Http::withBody(json_encode($data), 'application/json')
                ->post($job->user->callback);
                return;
            }




            $servise =  new TTLockService($job->user);

            $key = $servise->newKey($this->code, $lock, $this->begin, $this->end);

            if ($key['status']) {
                LockPinCode::create([
                    'pin_code' => $this->code,
                    'pin_code_id' => $key['data']['keyboardPwdId'],
                    'lock_id' => $lock->id,
                    'start' =>  $this->begin,
                    'end' => $this->end,
                ]);

                $job->user->code_packet->count = $job->user->code_packet->count - 1;
                $job->user->code_packet->save();
            }
            $data['job'] = $job->job_id;
            $data['method'] = 'add_code_to_lock';
            $data['data'] =  $key;

            Http::withBody(json_encode($data), 'application/json')
                //                ->withOptions([
                //                    'headers' => ''
                //                ])
                ->post($job->user->callback);
        }
    }
}
