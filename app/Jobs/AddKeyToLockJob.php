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

class AddKeyToLockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $counter, private int $job_id, private int $lock_id, private int $code, private string $code_name, private $begin = null, private $end = null, private $utc = null) {}

    /**
     * Execute the job.
     */
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
                if ($job->user->callback) {
                    Http::withBody(json_encode($data), 'application/json')
                        //                ->withOptions([
                        //                    'headers' => ''
                        //                ])
                        ->post($job->user->callback);
                } else $msg = $data['msg'];

                $centrifugo =  resolve(Centrifugo::class);
                $centrifugo->publish('api:add_code_to_lock-' . $job->user->id, ['msg' => $data['msg'], 'method' => $data['method'], 'job' =>  $data['job'], 'status' => $data['status']]);

                return;
            }



            if (!$job->user->code_packet()->exists()) {
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Нет оплаченного пакета кодов";
                if ($job->user->callback) {
                    Http::withBody(json_encode($data), 'application/json')
                        ->post($job->user->callback);
                }
                $centrifugo =  resolve(Centrifugo::class);
                $centrifugo->publish('api:add_code_to_lock-' . $job->user->id, ['msg' => $data['msg'], 'method' => $data['method'], 'job' =>  $data['job'], 'status' => $data['status']]);

                return;
            }

            if ($job->user->code_packet->end < now()) {

                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Окончилась дата действия пакета кодов";
                if ($job->user->callback) {
                    Http::withBody(json_encode($data), 'application/json')
                        ->post($job->user->callback);
                }
                $centrifugo =  resolve(Centrifugo::class);
                $centrifugo->publish('api:add_code_to_lock-' . $job->user->id, ['msg' => $data['msg'], 'method' => $data['method'], 'job' =>  $data['job'], 'status' => $data['status']]);

                return;
            }
            if ($job->user->code_packet->count < 1 &&  $job->user->code_packet->count != -100) {
                $data['status'] = false;
                $data['codes_error'] = true;
                $data['msg'] = "Закончился пакет кодов";
                if ($job->user->callback) {
                    Http::withBody(json_encode($data), 'application/json')
                        ->post($job->user->callback);
                }
                $centrifugo =  resolve(Centrifugo::class);
                $centrifugo->publish('api:add_code_to_lock-' . $job->user->id, ['msg' => $data['msg'], 'method' => $data['method'], 'job' =>  $data['job'], 'status' => $data['status']]);

                return;
            }


            $servise =  new TTLockService($job->user);
            $_begin = $this->begin;
            $_end = $this->end;
            if ($this->utc) {
                $_begin = (new DateTime($this->begin))->modify($this->utc . ' hours')->format('Y-m-d H:i');
                $_end = (new DateTime($this->end))->modify($this->utc . ' hours')->format('Y-m-d H:i');
            }

          
            $key = $servise->newKey($this->code, $lock, $this->code_name, $_begin,  $_end);

            if ($key['status']) {
                LockPinCode::create([
                    'pin_code' => $this->code,
                    'pin_code_id' => $key['data']['keyboardPwdId'],
                    'lock_id' => $lock->id,
                    'start' =>  $_begin,
                    'end' =>  $_end,
                    'start_local' =>$this->begin,
                    'end_local' =>$this->end,
                    'code_name' => $this->code_name,
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
                $data['msg'] = "Ключ успешно загружен";
            } else if ($this->counter >= 5) {
                $data['status'] = false;
                $data['msg'] = "Ошибка загрузки ключа. " . $key['msg'] . ' Количество попыток исчерпано.';
            } else if ($key['error_code'] != -3007) {
                $data['status'] = false;
                $data['msg'] = "Ошибка загрузки ключа. " . $key['msg'] . ' Следеющая попытка загрузки ключа чере 20 минут';
                AddKeyToLockJob::dispatch(++$this->counter, $this->job_id, $this->lock_id, $this->code, $this->code_name, $this->begin, $this->end, $this->utc)->onQueue('default')
                    ->chain([
                        new SetStatusJob($this->job_id,  $this->lock_id ? true : false)
                    ])
                    ->delay(now()->addMinutes(20));
            } else {
                $data['status'] = false;
                $data['msg'] = "Ошибка загрузки ключа. " . $key['msg'];
            }

            info('Load key result', $key);


            if ($data['status']) $msg = "Код " . $this->code . ' загружен в замок ' . $lock->lock_alias;
            else $msg = $data['msg'];

            $centrifugo =  resolve(Centrifugo::class);
            $centrifugo->publish('api:add_code_to_lock-' . $job->user->id, ['msg' => $msg, 'method' => $data['method'], 'job' =>  $data['job'], 'status' => $data['status']]);

            if ($job->user->callback) {
                Http::withBody(json_encode($data), 'application/json')
                    //                ->withOptions([
                    //                    'headers' => ''
                    //                ])
                    ->post($job->user->callback);
            }
        }
    }
}
