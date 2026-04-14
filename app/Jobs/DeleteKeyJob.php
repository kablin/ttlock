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
use denis660\Centrifugo\Centrifugo;
use Illuminate\Bus\Batchable;
use Throwable;


class DeleteKeyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;


    public $tries = 1500;       // Лимит попыток (вместо вашего ручного счетчика)
    public $backoff = 180;    // Задержка 3 минуты между попытками
    public $timeout = 120;

    private $parent_job;
    private $current_job;
    private $data = [];

    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id, private int $lock_id, private int  $pwdID, private $rent_id = null)
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
        $centrifugo->publish('api:delete_code_from_lock-' . $this->current_job->user->id, ['msg' => $this->data['msg'], 'method' => $this->data['method'], 'job' =>  $this->data['job'], 'status' => $this->data['status']]);
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

        $this->data['method'] = 'delete_code_from_lock';
        if ($this->current_job) {

            $this->data['tag'] = json_decode($this->current_job->tag);

            $lock = Lock::find($this->lock_id);
            if (!$this->lock_id) {
                $this->data['job'] = $this->current_job->job_id;
                $this->data['data'] = 'Lock not found';
                $this->data['msg'] = 'Неизвестный замок';
                $this->data['status'] = false;

                $this->callback();
                $this->sendToRC();

                return;
            }

            $servise =  new TTLockService($this->current_job->user);

            $rezult = $servise->deleteKey($lock, $this->pwdID);
            if ($rezult['status'] == true)
                LockPinCode::where('pin_code_id', $this->pwdID)->delete();


            $this->data['job'] = $this->current_job->job_id;
            $this->data['method'] = 'delete_code_from_lock';
            $this->data['data'] =  $rezult;
            $this->data['status'] =  $rezult['status'];
            if ($rezult['status'] == true) {
                $this->data['msg'] = "Ключ успешно удален";
            } else {
                $this->data['msg'] = "Ошибка удаления ключа. " . $rezult['msg'] . ' Следеющая попытка удаления ключа чере 3 минуты';
                $this->callback();
                if ($this->attempts() === 1) {
                    $this->sendToRC();
                }
                throw new \Exception("Ошибка удаления ключа. {$rezult['msg']} ");
            }


            if ($rezult['status']) $this->data['msg'] = "Ключ удален";
            else $this->data['msg'] = $rezult['msg'];

            $this->callback();
        }
    }



    public function failed(Throwable $e)
    {
        if ($this->current_job) {
            $this->data['job'] = $this->current_job->job_id;
            $this->data['tag'] = json_decode($this->current_job->tag);
            $this->data['method'] = 'delete_code_from_lock';
            $this->data['status'] = false;
            $this->data['msg'] = "Ошибка удаления ключа." . ' Количество попыток исчерпано. Проверьте подключение замка к сети';
            $this->callback();
            //$this->sendToRC();  // не РР не отправляем. Уйдет одно сообщение с батча
        }
    }
}
