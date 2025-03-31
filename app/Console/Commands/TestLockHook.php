<?php

namespace App\Console\Commands;

use App\Services\TTLockService;
use App\Services\JobsService;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Lock;

class TestLockHook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    const USER_ID = 1;
    protected $signature = 'command:testhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        $user = User::find(env('HOOK_USER_ID', 0));
        $lock = Lock::find(env('HOOK_LOCK_ID', 0));


        if ($user && $lock) {


            info('WebHook succsees in '.abs(now()->diffInMinutes($lock->events()->where('is_webhook',true)->latest()->first()?->created_at)));
            if (abs(now()->diffInMinutes($lock->events()->where('is_webhook',true)->latest()->first()?->created_at)) > 10) {
              info('WEBHOOK IS DOWN IN '.abs(now()->diffInMinutes($lock->events()->where('is_webhook',true)->latest()->first()?->created_at)));
            }


            $servise =  new TTLockService($user);
            $servise->openLock($lock);


            return Command::SUCCESS;
        }
    }
}
