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
use Carbon\Carbon;
use App\Models\LockPinCode;
use App\Services\TTLockService;
use denis660\Centrifugo\Centrifugo;

class GetCodesListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $counter, private int $job_id, private int $lock_id, private int $page_number, private int $page_size) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if ($job = LockJob::find($this->job_id)) {

            $data['job'] = $job->job_id;
            $data['tag'] = json_decode($job->tag);
            $data['method'] = 'get_codes_list';

            $lock = Lock::find($this->lock_id);
            if (!$this->lock_id) {

                $data['data'] = 'Lock not found';
                $data['msg'] = 'Неизвестный замок';
                $data['error'] =  $data['msg'];
                $data['status'] = false;

                Http::withBody(json_encode($data), 'application/json')
                    ->post($job->user->callback);

                return;
            }

            $servise =  new TTLockService($job->user);

            $rezult = $servise->getKeyList($lock, $this->page_number, $this->page_size);

            $data['status'] = $rezult['status'];
            $data['data'] =  $rezult;


            if ($rezult['status']) {

                LockPinCode::where(['lock_id' => $lock->id,])->update(['is_load' => false]);
                foreach ($rezult['data']['list'] as $key) {
                    LockPinCode::updateOrCreate(
                        ['pin_code_id' => $key['keyboardPwdId'], 'lock_id' => $lock->id],
                        [
                            'is_load' => true,
                            'pin_code' => $key['keyboardPwd'],
                            'start' => $key['startDate'] ? Carbon::createFromTimestamp(round($key['startDate']/1000))->toDateTimeString() : null,
                            'end' => $key['endDate'] ? Carbon::createFromTimestamp(round($key['endDate']/1000))->toDateTimeString() : null,

                            'start_local' => $key['startDate'] ? Carbon::createFromTimestamp(round($key['startDate']/1000))->addHours(3)->toDateTimeString() : null,
                            'end_local' => $key['endDate'] ? Carbon::createFromTimestamp(round($key['endDate']/1000))->addHours(3)->toDateTimeString() : null,

                            'code_name' => $key['keyboardPwdName'] ?? 'Отсутствует'
                        ]
                    );
                }

                $msg = "Ключи замка " . $lock->lock_alias . " синхронизированы";
            } else $msg = $rezult['msg'];

            $centrifugo =  resolve(Centrifugo::class);
            $centrifugo->publish('api:get_codes_list-' . $job->user->id, ['msg' => $msg, 'method' => $data['method'], 'job' =>  $data['job'], 'status' => $data['status']]);

            //info('get_codes_list',$rezult);
            if ($job->user->callback) {

                Http::withBody(json_encode($data), 'application/json')
                    ->post($job->user->callback);
            }
        }
    }
}
