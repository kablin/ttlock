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
    public function __construct(private int $counter, private int $job_id, private int $lock_id, private int $code, private $begin = null, private $end = null) {}

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



            if (!$job->user->code_packet()->exists()) {
                $data['job'] = $job->job_id;
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Нет оплаченного пакета кодов";

                Http::withBody(json_encode($data), 'application/json')
                    ->post($job->user->callback);
                return;
            }

            if ($job->user->code_packet->end < now()) {
                $data['job'] = $job->job_id;
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Окончилась дата действия пакета кодов";

                Http::withBody(json_encode($data), 'application/json')
                    ->post($job->user->callback);
                return;
            }
            if ($job->user->code_packet->count < 1 &&  $job->user->code_packet->count != -100) {
                $data['job'] = $job->job_id;
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Закончился пакет кодов";

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

                if ($job->user->code_packet->count != -100) {
                    $job->user->code_packet->count = $job->user->code_packet->count - 1;
                    $job->user->code_packet->save();
                }
            }
            $data['job'] = $job->job_id;
            $data['method'] = 'add_code_to_lock';
            $data['data'] =  $key;
            if ($key['status'])
                $data['msg'] = "Ключ успешно загружен";
            else if ($this->counter>=5)
            {
                $data['msg'] = "Ошибка загрузки ключа. ".$key['msg'].' Количество попыток исчерпано';
            }
            else
            {
                $data['msg'] = "Ошибка загрузки ключа. ".$key['msg'].' Следеющая попытка загрузки ключа чере 20 минут';
                AddKeyToLockJob::dispatch($this->counter++, $this->job_id, $this->lock_id,$this->code,  $this->begin, $this->end)->onQueue('default')
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
