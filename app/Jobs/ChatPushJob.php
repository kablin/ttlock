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
use denis660\Centrifugo\Centrifugo;

class ChatPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $job_id, private string $message) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if ($job = LockJob::find($this->job_id)) {

            $token = 'eyJhbGciOiJIUzI1NiJ9.eyJjdXN0b21lcl9pZCI6NTExOTIsImRhdGV0aW1lIjoxNzc2NTc4NDc3fQ.DHJfkrmIX0Q-re_pZdwtsh-tkY-HED5JCnwa478Rhzg';
            //$job->user->callback


            $data['job'] = $job->job_id;
            $data['tag'] = json_decode($job->tag);
            $data['method'] = 'chat_push';

            $req['text'] = $this->message;
            $req['phone'] = $job->user->phone;

            /*
             $req['text'] = 'test from api';
            $req['phone'] = '89119868934';
 Http::withToken($token)->withBody(json_encode($req), 'application/json')->get('https://api.chatpush.ru/api/v1/delivery/339788505');
            */


            Http::withToken($token)->withBody(json_encode($req), 'application/json')->post('https://api.chatpush.ru/api/v1/delivery');
        }
    }
}
