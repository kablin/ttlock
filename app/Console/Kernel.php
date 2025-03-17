<?php

namespace App\Console;

use App\Schedules\GetLockEventsSchedule;
use App\Schedules\GetLockListSchedule;
use App\Schedules\UpdateRefreshTokenLocks;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
		  //  $schedule->job((new UpdateRefreshTokenLocks()))->weekly();
            $schedule->job((new GetLockEventsSchedule()))->everySixHours();
            $schedule->job((new GetLockListSchedule()))->hourly();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
