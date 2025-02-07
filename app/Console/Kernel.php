<?php

namespace App\Console;

use App\Schedules\CheckChargeLocksSchedule;
use App\Schedules\CheckGatewayStatusLocksSchedule;
use App\Schedules\DeleteLockPinCodesSchedule;
use App\Schedules\EndBookingNotifySchedule;
use App\Schedules\GetLockDetailsSchedule;
use App\Schedules\GetLockEventsSchedule;
use App\Schedules\GetOkiDokiDocsSchedule;
use App\Schedules\LoadTodayExternalKeyInLock;
use App\Schedules\LoadTodayKeyInLock;
use App\Schedules\PreventionEndBookingSchedule;
use App\Schedules\SetCloseStatusBookingSchedule;
use App\Schedules\SetWaitGuestStatusBookingSchedule;
use App\Schedules\TravellineCancelBookingsSchedule;
use App\Schedules\TravellineGetBookingsSchedule;
use App\Schedules\TravellineUpdateClientsSchedule;
use App\Schedules\UpdateAmoCRMTokensSchedule;
use App\Schedules\UpdatePeriodLockPinCodesWithError;
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
		    $schedule->job((new UpdateRefreshTokenLocks()))->weekly();

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
