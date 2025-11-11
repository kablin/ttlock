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

class ChangeCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;



    /**
     * Create a new job instance.
     */
    public function __construct(private int $counter, private int $job_id, private int $lock_id, private int $code_id, private $begin = null, private $end = null) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        if ($job = LockJob::find($this->job_id)) {

            $data['job'] = $job->job_id;
            $data['tag'] = json_decode($job->tag);
            $data['method'] = 'change_code';

            $lock = Lock::find($this->lock_id);
            if (!$this->lock_id) {

                $data['data'] = 'Lock not found';
                $data['msg'] = 'Неизвестный замок';
                $data['status'] = false;

                Http::withBody(json_encode($data), 'application/json')
                    //                ->withOptions([
                    //                    'headers' => ''
                    //                ])
                    ->post($job->user->callback);

                return;
            }


            $pincode =  $lock->pincodes()->where('pin_code_id',$this->code_id)->first();
            if (!$pincode) {

                $data['data'] = 'Pincode not found';
                $data['msg'] = 'У замка отсутствует данный ключ';
                $data['status'] = false;

                Http::withBody(json_encode($data), 'application/json')
                    ->post($job->user->callback);
                return;
            }



            if (!$job->user->code_packet()->exists()) {
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Нет оплаченного пакета кодов";

                Http::withBody(json_encode($data), 'application/json')
                    ->post($job->user->callback);
                return;
            }

            if ($job->user->code_packet->end < now()) {

                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Окончилась дата действия пакета кодов";

                Http::withBody(json_encode($data), 'application/json')
                    ->post($job->user->callback);
                return;
            }
            if ($job->user->code_packet->count < 1 &&  $job->user->code_packet->count != -100) {
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Закончился пакет кодов";

                Http::withBody(json_encode($data), 'application/json')
                    ->post($job->user->callback);
                return;
            }




            $servise =  new TTLockService($job->user);

            $key = $servise->updateKey($this->code_id, $lock,$this->begin, $this->end);

            if ($key['status']) {
                $pincode->start= $this->begin;
                $pincode->end= $this->end;
                $pincode->save();

            }
            $data['data'] =  $key;
            if ($key['status']) {
                $data['status'] = true;
                $data['msg'] = "Ключ успешно обновлен";
            }
            else if ($this->counter>=5)
            {
                $data['status'] = false;
                $data['msg'] = "Ошибка обновления ключа. ".$key['msg'].' Количество попыток исчерпано.';
            }
            else
            {
                 $data['status'] = false;
                $data['msg'] = "Ошибка обновления ключа. ".$key['msg'].' Следеющая попытка загрузки ключа чере 20 минут';
                ChangeCodeJob::dispatch(++$this->counter, $this->job_id, $this->lock_id,$this->code_id, $this->begin, $this->end)->onQueue('default')
                ->chain([
                    new SetStatusJob($this->job_id,  $this->lock_id ? true : false)
                ])
                ->delay(now()->addMinutes(20));
            }

            info('Update key result', $key);

            Http::withBody(json_encode($data), 'application/json')
                ->post($job->user->callback);
        }
    }
}
