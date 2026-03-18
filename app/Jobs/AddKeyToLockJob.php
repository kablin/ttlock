<?php

namespace App\Jobs;




use Illuminate\Bus\Queueable;
use DateTime;
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
use denis660\Centrifugo\Centrifugo;
use Throwable;
use Illuminate\Bus\Batchable;

class AddKeyToLockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;


    public $tries = 1500;       // Лимит попыток (вместо вашего ручного счетчика)
    public $backoff = 180;    // Задержка 3 минуты между попытками
    public $timeout = 120;

  protected $dontReport = [\RuntimeException::class, \Exception::class];
    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id, private int $lock_id, private int $code, private string $code_name, private $begin = null, private $end = null, private $utc = null, private $rent_id = null) {}

    /**
     * Execute the job.
     */

    private function callback($data): void
    {
        $job = LockJob::find($this->job_id);
        if ($job->user->callback) {
            Http::withBody(json_encode($data), 'application/json')
                //                ->withOptions([
                //                    'headers' => ''
                //                ])
                ->post($job->user->callback);
        }
        $centrifugo =  resolve(Centrifugo::class);
        $centrifugo->publish('api:add_code_to_lock-' . $job->user->id, ['msg' => $data['msg'], 'method' => $data['method'], 'job' =>  $data['job'], 'status' => $data['status']]);
    }


    private function sendToRC($data): void
    {
        if ($this->batchId) {
            $job = LockJob::find($this->job_id);
            if ($data['status'] == false)   $data['error'] = $data['msg'];
            else $data['message'] = $data['msg'];

            if ($parent_job = LockJob::find($job->parent_job)) {
                $data['job'] = $parent_job->job_id;
            }
            $data['rent_id'] = $this->rent_id;
            info('Batch send', $data);
            Http::withToken('token')->withBody(json_encode($data), 'application/json')->post('https://realtycalendar.ru/v2/integrations/rentysoft/receive_lock_code');
        }
    }

    public function handle(): void
    {

        if ($job = LockJob::find($this->job_id)) {

            $data['job'] = $job->job_id;
            $data['tag'] = json_decode($job->tag);
            $data['method'] = 'add_code_to_lock';

            $lock = Lock::find($this->lock_id);
            if (!$this->lock_id) {

                $data['data'] = 'Lock not found';
                $data['msg'] = 'Неизвестный замок';
                $data['status'] = false;

                $this->callback($data);
                $this->sendToRC($data);
                return;
            }


            if (!$job->user->code_packet()->exists()) {
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Нет оплаченного пакета кодов";
                $this->callback($data);
                $this->sendToRC($data);
                return;
            }

            if ($job->user->code_packet->end < now()) {

                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Окончилась дата действия пакета кодов";
                $this->callback($data);
                $this->sendToRC($data);
                return;
            }
            if ($job->user->code_packet->count < 1 &&  $job->user->code_packet->count != -100) {
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Закончился пакет кодов";
                $this->callback($data);
                $this->sendToRC($data);
                return;
            }


            $servise =  new TTLockService($job->user);
            $_begin = $this->begin;
            $_end = $this->end;
            if ($this->utc) {
                $_begin = (new DateTime($this->begin))->modify($this->utc . ' hours')->format('Y-m-d H:i');
                $_end = (new DateTime($this->end))->modify($this->utc . ' hours')->format('Y-m-d H:i');
            }


            $key = $servise->newKey($this->code, $lock,  $this->code_name, $_begin,  $_end);

            if ($key['status']) {
                LockPinCode::create([
                    'pin_code' => $this->code,
                    'pin_code_id' => $key['data']['keyboardPwdId'],
                    'lock_id' => $lock->id,
                    'start' =>  $_begin,
                    'end' =>  $_end,
                    'start_local' => $this->begin,
                    'end_local' => $this->end,
                    'code_name' => $this->code_name,
                    'rent_id' => $this->rent_id,
                    'is_load' => true,
                ]);

                if ($job->user->code_packet->count != -100) {
                    $job->user->code_packet->count = $job->user->code_packet->count - 1;
                    $job->user->code_packet->save();
                }
            }
            $data['data'] =  $key;
            if ($key['status']) {
                $data['status'] = true;
                $data['msg'] = "Ключ в замок " . $lock->lock_alias . " успешно загружен :" . $this->code . "#";
            } else if ($key['error_code'] != -3007) {
                $data['status'] = false;
                $data['msg'] = "Ошибка загрузки ключа. " . $key['msg'] . ' Следующая попытка загрузки ключа через 3 минуты';

                $this->callback($data);

                if ($this->attempts() === 1) {

                    $this->sendToRC($data);
                }
                throw new \Exception("Lock {$this->lock_id} not ready. Attempt {$this->attempts()}");


                /* AddKeyToLockJob::dispatch(++$this->counter, $this->job_id, $this->lock_id, $this->code, $this->code_name, $this->begin, $this->end, $this->utc)->onQueue('default')
                    ->chain([
                        new SetStatusJob($this->job_id,  $this->lock_id ? true : false)
                    ])
                    ->delay(now()->addMinutes(3));*/
            } else {  // код уже существует
                $data['status'] = false;
                $data['msg'] = "Ошибка загрузки ключа. " . $key['msg'];
                $this->sendToRC($data);
                $this->fail(new \RuntimeException(
                    "Code {$this->code} already exists in lock {$this->lock_id}"
                ));
            }

            info('Load key result', $key);


            if ($data['status']) $data['msg'] = "Код " . $this->code . ' загружен в замок ' . $lock->lock_alias;


            $this->callback($data);
        }
    }


    public function failed(Throwable $e)
    {
        $data['status'] = false;
        $data['msg'] = "Ошибка загрузки ключа. " . ' Количество попыток исчерпано. Проверьте подключение замка к сети';
        $this->callback($data);
    }
}
