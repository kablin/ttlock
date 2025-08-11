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

class DeleteKeyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $counter,private int $job_id, private int $lock_id, private int  $pwdID) {}

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
                $data['msg'] = 'Замок не найден';

                Http::withBody(json_encode($data), 'application/json')
                    //                ->withOptions([
                    //                    'headers' => ''
                    //                ])
                    ->post($job->user->callback);

                return;
            }

            $servise =  new TTLockService($job->user);

            $rezult = $servise->deleteKey( $lock,$this->pwdID);
            if($rezult['status']==true) 
            LockPinCode::where('pin_code_id',$this->pwdID)->delete();


            $data['job'] = $job->job_id;
            $data['method'] = 'delete_code_from_lock';
            $data['data'] =  $rezult;
            if($rezult['status']==true) 
            {
                $data['msg'] = "Ключ успешно удален";

            }
            else if ($this->counter>=5)
            {
                $data['msg'] = "Ошибка удаления ключа. ".$rezult['msg'].' Количество попыток исчерпано';

            }

            else
            {
                $data['msg'] = "Ошибка удаления ключа. ".$rezult['msg'].' Следеющая попытка удаления ключа чере 20 минут';
                DeleteKeyJob::dispatch(++$this->counter, $this->job_id, $this->lock_id,  $this->pwdID)->onQueue('default')
                ->chain([
                    new SetStatusJob($this->job_id,  $this->lock_id ? true : false)
                ])
                ->delay(now()->addMinutes(20));
            }


            Http::withBody(json_encode($data), 'application/json')
                //                ->withOptions([
                //                    'headers' => ''
                //                ])
                ->post($job->user->callback);
        }
    }
}
