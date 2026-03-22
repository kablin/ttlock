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
use Illuminate\Support\Facades\Cache;
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

    private $data = [];
    private $parent_job;
    private $current_job;

    protected $dontReport = [\RuntimeException::class, \Exception::class];
    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id, private int $lock_id, private int $code, private string $code_name, private $begin = null, private $end = null, private $utc = null, private $rent_id = null)
    {
        $this->current_job = LockJob::find($this->job_id);
        $this->parent_job = LockJob::find($this->current_job->parent_job);
    }

    /**
     * Execute the job.
     */

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
        $centrifugo->publish('api:add_code_to_lock-' . $this->current_job->user->id, ['msg' => $this->data['msg'], 'method' => $this->data['method'], 'job' =>  $this->data['job'], 'status' => $this->data['status']]);
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

            Http::withToken('token')->withBody(json_encode($this->data), 'application/json')->post('https://realtycalendar.ru/v2/integrations/rentysoft/receive_lock_code');
        }
    }

    public function handle(): void
    {

        if ($this->current_job) {

            $this->data['job'] = $this->current_job->job_id;
            $this->data['tag'] = json_decode($this->current_job->tag);
            $this->data['method'] = 'add_code_to_lock';

            $lock = Lock::find($this->lock_id);
            if (!$this->lock_id) {

                $this->data['data'] = 'Lock not found';
                $this->data['msg'] = 'Неизвестный замок';
                $this->data['status'] = false;

                $this->callback();
                $this->sendToRC();
                return;
            }


            if (!$this->current_job->user->code_packet()->exists()) {
                $this->data['status'] = false;
                $this->data['codes_error'] = true;
                $this->data['msg'] = "Нет оплаченного пакета кодов";
                $this->callback();
                $this->sendToRC();
                return;
            }

            if ($this->current_job->user->code_packet->end < now()) {

                $this->data['status'] = false;
                $this->data['codes_error'] = true;
                $this->data['msg'] = "Окончилась дата действия пакета кодов";
                $this->callback();
                $this->sendToRC();
                return;
            }
            if ($this->current_job->user->code_packet->count < 1 &&  $this->current_job->user->code_packet->count != -100) {
                $this->data['status'] = false;
                $this->data['codes_error'] = true;
                $this->data['msg'] = "Закончился пакет кодов";
                $this->callback();
                $this->sendToRC();
                return;
            }


            $service =  new TTLockService($this->current_job->user);
            $_begin = $this->begin;
            $_end = $this->end;
            if ($this->utc) {
                $_begin = (new DateTime($this->begin))->modify($this->utc . ' hours')->format('Y-m-d H:i');
                $_end = (new DateTime($this->end))->modify($this->utc . ' hours')->format('Y-m-d H:i');
            }

            // === ПРОВЕРКА: не изменился ли код в процессе? ===
            $this->batchId ?    $finalCode = Cache::get("final_code:{$this->batchId}", $this->code) : $finalCode = $this->code;


            $key = $service->newKey($finalCode, $lock, $this->code_name, $_begin, $_end);


            //$key = $service->newKey($this->code, $lock,  $this->code_name, $_begin,  $_end);

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

                if ($this->current_job->user->code_packet->count != -100) {
                    $this->current_job->user->code_packet->count = $this->current_job->user->code_packet->count - 1;
                    $this->current_job->user->code_packet->save();
                }
            }
            $this->data['data'] =  $key;
            if ($key['status']) {
                $this->data['status'] = true;
                $this->data['msg'] = "Ключ в замок " . $lock->lock_alias . " успешно загружен :" . $this->code . "#";
            } else if ($key['error_code'] != -3007) {
                $this->data['status'] = false;
                $this->data['msg'] = "Ошибка загрузки ключа в замок {$lock->lock_alias}. " . $key['msg'] . '. Следующая попытка загрузки ключа через 3 минуты';

                $this->callback();

                if ($this->attempts() === 1) {

                    $this->sendToRC();
                }
                throw new \Exception("Ошибка загрузки ключа в замок {$lock->lock_alias}. {$key['msg']} ");


                /* AddKeyToLockJob::dispatch(++$this->counter, $this->job_id, $this->lock_id, $this->code, $this->code_name, $this->begin, $this->end, $this->utc)->onQueue('default')
                    ->chain([
                        new SetStatusJob($this->job_id,  $this->lock_id ? true : false)
                    ])
                    ->delay(now()->addMinutes(3));*/
            } else {  // код уже существует
                $this->data['status'] = false;
                $this->data['msg'] = "Ошибка загрузки ключа. " . $key['msg'];

                if ($this->batchId) { //если это пакет, то пытаемся обновить код и завершаем
                    $this->sendToRC();
                    $this->handleCodeCollision($finalCode, $lock->id);
                    return;
                }


                $this->fail(new \RuntimeException(
                    "Ключ {$this->code} уже есть в замке {$this->lock_id}"
                ));
            }

            info('Load key result', $key);


            if ($this->data['status']) $this->data['msg'] = "Код " . $this->code . ' загружен в замок ' . $lock->lock_alias;

            $this->callback();
        }
    }


    private function handleCodeCollision(string $code): void
    {
        $lockKey = "regenerate_lock:{$this->batchId}";
        $id = $this->batchId;

        Cache::lock($lockKey, 30)->get(function () use ($code, $id) {

            $existingCode = Cache::get("final_code:{$id}");
            if ($existingCode) {
                return; // Код уже сгенерирован другим процессом
            }
            // для всех используем ид батча, а для кол-ва коллизий ид родительской джобы, так как во всех батчах оно должно быть сквозное
            $currentCount = (int) Cache::get("collision_count:{$this->parent_job->job_id}", 0);
            Cache::put("collision_count:{$this->parent_job->job_id}", $currentCount + 1, 3600 * 100);

            $newCode =  random_int(1000, 9999);
            // Сохраняем под ОБЩИМ ключом
            Cache::put("final_code:{$id}", $newCode, 3600 * 100);
            Cache::put("batch_needs_restart:{$id}", true, 3600 * 100);

            info("Code collision, new code generated", [
                'operation_id' => $id,
                'old_code' => $code,
                'new_code' => $newCode,
            ]);
        });
    }

    public function failed(Throwable $e)
    {
        if ($this->current_job) {

            $this->data['job'] = $this->current_job->job_id;
            $this->data['tag'] = json_decode($this->current_job->tag);
            $this->data['method'] = 'add_code_to_lock';
            $this->data['status'] = false;
            $this->data['msg'] = "Ошибка загрузки ключа." . ' Количество попыток исчерпано. Проверьте подключение замка к сети';
            $this->callback();
            //$this->sendToRC();  // не РР не отправляем. Уйдет одно сообщение с батча
        }
    }
}
