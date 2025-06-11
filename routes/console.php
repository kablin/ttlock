<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Schedules\GetLockEventsSchedule;
use App\Schedules\GetLockListSchedule;
use App\Schedules\DeleteLockPinCodesSchedule;
use App\Console\Commands\TestLockHook;
use Illuminate\Support\Facades\Schedule;



Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


//  $schedule->job((new UpdateRefreshTokenLocks()))->weekly();
Schedule::job((new GetLockEventsSchedule()))->everySixHours();
Schedule::job((new GetLockListSchedule()))->hourly();
Schedule::job((new DeleteLockPinCodesSchedule()))->daily();
//Schedule::command(TestLockHook::class)->everyFifteenMinutes();
