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
use App\Services\TTLockService;

class GetLockListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if ($job = LockJob::find($this->job_id)) {


            $servise =  new TTLockService($job->user);
            $locks_data = $servise->getLockList();

            $mylocks = [];
            if ($locks_data['status'] == true) {
                foreach ($locks_data['data']['list'] as $data) {
                    $lock = [];
                    $lock['user_id'] = $job->user->id;
                    $lock['lock_id'] = $data['lockId'];
                    $lock['lock_name'] = $data['lockName'];
                    $lock['lock_alias'] = $data['lockAlias'];
                    $lock['status'] = true;
                    $lock['electric_quantity'] = $data['electricQuantity'];
                    $lock['no_key_pwd'] = $data['noKeyPwd'];

                    $model = Lock::query()->updateOrCreate(['lock_id' => $data['lockId']], $lock);

                    $mylocks[] =  $model->id;

                    $model->saveOptionValueByName('error', null);
                    $model->saveOptionValueByName('electricQuantity', $data['electricQuantity'] ?? 0);
                    $model->saveOptionValueByName('lockAlias', $data['lockAlias']);
                    $model->saveOptionValueByName('noKeyPwd', $data['noKeyPwd']);
                    $model->saveOptionValueByName('timezoneRawOffset', $data['timezoneRawOffset']);
                }
                Lock::where('user_id', $job->user->id)->whereNotIn('id', $mylocks)->delete();
            }


            /* foreach ($job->user->locks as $lock)
            {
                $details = $servise->getLockDetails($lock);
                $lock_data['lock_alias'] = $details['data']['lockAlias'] ?? null;
                $lock_data['no_key_pwd'] = $details['data']['noKeyPwd'] ?? null;
                $lock_data['electric_quantity'] = $details['data']['electricQuantity'] ?? 0;
                $lock_data['lock_data'] = $details['data']['lockData'] ?? null;
                $lock->update($lock_data);

                $lock->saveOptionValueByName('error',null);
                $lock->saveOptionValueByName('electricQuantity',$details['data']['electricQuantity'] ?? 0);
                $lock->saveOptionValueByName('lockAlias', $details['data']['lockAlias']);
                $lock->saveOptionValueByName('noKeyPwd',$details['data']['noKeyPwd']);
                $lock->saveOptionValueByName('timezoneRawOffset',$details['data']['timezoneRawOffset']);								

            }*/




            $result['job'] = $job->job_id;
            $data['method'] = 'get_lock_list';
            $result['data'] = $locks_data;

            Http::withBody(json_encode($result), 'application/json')
                //                ->withOptions([
                //                    'headers' => ''
                //                ])
                ->post($job->user->callback);
        }
    }
}
