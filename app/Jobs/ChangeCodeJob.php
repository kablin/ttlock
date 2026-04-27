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
use Throwable;
use DateTime;
use Illuminate\Bus\Batchable;
use denis660\Centrifugo\Centrifugo;

class ChangeCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    public $tries = 1500;       // Лимит попыток (вместо вашего ручного счетчика)
    public $backoff = 180;    // Задержка 3 минуты между попытками
    public $timeout = 120;

    private $data = [];
    private $parent_job;
    private $current_job;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id, private int $lock_id, private int $code_id, private $begin = null, private $end = null, private $utc = null, private $rent_id = null)
    {
        $this->current_job = LockJob::find($this->job_id);
        $this->parent_job = LockJob::find($this->current_job->parent_job);
    }



    private function callback(): void
    {
        if ($this->current_job->user->callback) {
            Http::withBody(json_encode($this->data), 'application/json')
                //                ->withOptions([
                //                    'headers' => ''
                //                ])
                ->post($this->current_job->user->callback);
        }
        $centrifugo =  resolve(Centrifugo::class);
        $centrifugo->publish('api:change_code-' . $this->current_job->user->id, ['msg' => $this->data['msg'], 'method' => $this->data['method'], 'job' =>  $this->data['job'], 'status' => $this->data['status']]);
    }


    private function sendToRC(): void
    {
        if ($this->batchId) {

            if ($this->data['status'] == false)   $this->data['error'] = $this->data['msg'];
            else $this->data['message'] = $this->data['msg'];

            if ($this->parent_job) {
                $this->data['job'] = $this->parent_job->job_id;
            }
            $this->data['rent_id'] = $this->rent_id;
            info('sendToRC send', $this->data);

            Http::withToken($this->current_job->user->realty_key)->withBody(json_encode($this->data), 'application/json')->post('https://test.realtycalendar.ru/v2/integrations/renty_soft/receive_lock_code');
        }
    }



    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if ($this->current_job) {

            $this->data['job'] = $this->current_job->job_id;
            $this->data['tag'] = json_decode($this->current_job->tag);
            $this->data['method'] = 'change_code';

            $lock = Lock::find($this->lock_id);
            if (!$this->lock_id) {

                $this->data['data'] = 'Lock not found';
                $this->data['msg'] = 'Неизвестный замок';
                $this->data['status'] = false;

                $this->callback();
                $this->sendToRC();

                return;
            }


            $pincode =  $lock->pincodes()->where('pin_code_id', $this->code_id)->first();
            if (!$pincode) {

                $this->data['data'] = 'Pincode not found';
                $this->data['msg'] = 'У замка отсутствует данный ключ';
                $this->data['status'] = false;

                $this->callback();
                $this->sendToRC();
                return;
            }



            if (!$this->current_job->user->code_packet()->exists()) {
                $this->data['status'] = false;
                $this->data['codes_error'] = true;
                $this->data['msg'] = "Нет оплаченного пакета ключей";

                $this->callback();
                $this->sendToRC();
                return;
            }

            if ($this->current_job->user->code_packet->end < now()) {

                $this->data['status'] = false;
                $this->data['codes_error'] = true;
                $this->data['msg'] = "Окончилась дата действия пакета ключей";

                $this->callback();
                $this->sendToRC();
                return;
            }
            if ($this->current_job->user->code_packet->count < 1 &&  $this->current_job->user->code_packet->count != -100) {
                $this->data['status'] = false;
                $this->data['codes_error'] = true;
                $this->data['msg'] = "Закончился пакет ключей";
                $this->callback();
                $this->sendToRC();
                return;
            }




            $servise =  new TTLockService($this->current_job->user);

            $_begin = $this->begin;
            $_end = $this->end;
            if ($this->utc) {
                $_begin = (new DateTime($this->begin))->modify($this->utc . ' hours')->format('Y-m-d H:i');
                $_end = (new DateTime($this->end))->modify($this->utc . ' hours')->format('Y-m-d H:i');
            }


            $key = $servise->updateKey($this->code_id, $lock,  $_begin, $_end);

            if ($key['status']) {
                $pincode->start = $_begin;
                $pincode->end = $_end;
                $pincode->start_local = $this->begin;
                $pincode->end_local = $this->end;
                $pincode->save();
            }
            $this->data['data'] =  $key;
            if ($key['status']) {
                $this->data['status'] = true;
                $this->data['msg'] = "Ключ успешно обновлен";
            }
            /* else if ($this->counter >= 1500) {
                $this->data['status'] = false;
                $this->data['msg'] = "Ошибка обновления ключа. " . $key['msg'] . ' Количество попыток исчерпано. Проверьте подключение замка к сети';
            } */ else {
                $this->data['status'] = false;
                $this->data['msg'] = "Ошибка обновления ключа. " . $key['msg'] . ' Следующая попытка обновления ключа через 3 минуты';

                $this->callback();

                if ($this->attempts() === 1) {

                    $this->sendToRC();
                }
                throw new \Exception($this->data['msg']);


                /* ChangeCodeJob::dispatch(++$this->counter, $this->job_id, $this->lock_id, $this->code_id, $this->begin, $this->end)->onQueue('default')
                    ->chain([
                        new SetStatusJob($this->job_id,  $this->lock_id ? true : false)
                    ])
                    ->delay(now()->addMinutes(3));*/
            }

            info('Update key result', $key);

            $this->callback();
        }
    }


    public function failed(Throwable $e)
    {
        if ($this->current_job) {

            $this->data['job'] = $this->current_job->job_id;
            $this->data['tag'] = json_decode($this->current_job->tag);
            $this->data['method'] = 'add_code_to_lock';
            $this->data['status'] = false;
            $this->data['msg'] = "Ошибка обновления ключа." . ' Количество попыток исчерпано. Проверьте подключение замка к сети';
            $this->callback();
            //$this->sendToRC();  // не РР не отправляем. Уйдет одно сообщение с батча
        }
    }
}
