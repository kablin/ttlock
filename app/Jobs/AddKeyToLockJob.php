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


    public $tries = 24;         // Было 1500: при паузе 3 мин это 75 часов долбёжки TTLock по одному замку
    public $timeout = 120;

    // Ошибки TTLock, которые повтором не лечатся: код нужной длины сам не появится,
    // неверный параметр тоже. Их ретраить бессмысленно — только жжём квоту API.
    private const PERMANENT_ERRORS = [-3006, -3];

    /**
     * Растущая пауза между попытками: 3 мин → 10 мин → 30 мин → 1 час.
     * 24 попытки покрывают ~17 часов (замок успеет ожить за ночь),
     * но стоят 24 вызова к TTLock вместо 480 в сутки при фиксированных 3 минутах.
     */
    public function backoff(): array
    {
        return [180, 180, 180, 600, 600, 600, 1800, 1800, 1800, 3600];
    }

    private $data = [];
    private $parent_job;
    private $current_job;

    private $_code;

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

            Http::withToken($this->current_job->user->realty_key)->withBody(json_encode($this->data), 'application/json')->post('https://test.realtycalendar.ru/v2/integrations/renty_soft/receive_lock_code');
        }
    }

    public function handle(): void
    {


        $this->_code = (string)$this->code;
        if (strlen($this->_code) == 3)  $this->_code = '0' . $this->_code;
        else  if (strlen($this->_code) == 2)  $this->_code = '00' . $this->_code;
        else  if (strlen($this->_code) == 1)  $this->_code = '000' . $this->_code;


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


            $service =  new TTLockService($this->current_job->user);
            $_begin = $this->begin;
            $_end = $this->end;

            if ($this->current_job->user->utc) {
                $_begin = (new DateTime($this->begin))->modify($this->current_job->user->utc . ' hours')->format('Y-m-d H:i');
                $_end = (new DateTime($this->end))->modify($this->current_job->user->utc . ' hours')->format('Y-m-d H:i');
            } else if ($this->utc) {
                $_begin = (new DateTime($this->begin))->modify($this->utc . ' hours')->format('Y-m-d H:i');
                $_end = (new DateTime($this->end))->modify($this->utc . ' hours')->format('Y-m-d H:i');
            }

            // === ПРОВЕРКА: не изменился ли код в процессе? ===
            $this->batchId ?    $finalCode = Cache::get("final_code:{$this->batchId}", $this->_code) : $finalCode = $this->_code;

            $this->data['code'] = $finalCode;
            $key = $service->newKey($finalCode, $lock, $this->code_name, $_begin, $_end);


            //$key = $service->newKey($this->code, $lock,  $this->code_name, $_begin,  $_end);

            if ($key['status']) {
                LockPinCode::create([
                    'pin_code' =>  $finalCode,
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
                $this->data['msg'] = "Ключ в замок " . $lock->lock_alias . " успешно загружен :" . $finalCode . "#";
            } else if ($key['error_code'] != -3007) {
                $this->data['status'] = false;

                // Неустранимая ошибка — сдаёмся сразу, без повторов.
                if (in_array((int) $key['error_code'], self::PERMANENT_ERRORS, true)) {
                    $this->data['msg'] = "Ошибка загрузки ключа в замок {$lock->lock_alias}. " . $key['msg'];
                    $this->callback();
                    $this->sendToRC();
                    $this->fail(new \RuntimeException(
                        "Неустранимая ошибка TTLock ({$key['error_code']}) на замке {$this->lock_id}: {$key['msg']}"
                    ));
                    return;
                }

                $this->data['msg'] = "Ошибка загрузки ключа в замок {$lock->lock_alias}. " . $key['msg'] . '. Следующая попытка загрузки ключа позже';

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
                    "Ключ {$finalCode} уже есть в замке {$this->lock_id}"
                ));
            }

            info('Load key result', $key);


            if ($this->data['status']) $this->data['msg'] = "Код " . $finalCode . ' загружен в замок ' . $lock->lock_alias;

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
