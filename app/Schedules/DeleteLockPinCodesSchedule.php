<?php


namespace App\Schedules;

use App\Jobs\BookingDeleteCode;
use App\Models\LockPinCode;

class DeleteLockPinCodesSchedule
{
	public function __invoke()
	{
		info('start delete shedule');
		$index = 1;
		LockPinCode::query()->get()->each(function ($l) use ($index) {
			if ($l->end) {
				BookingDeleteCode::dispatch($l)->delay(now()->addSeconds($index * 10));
				$index++;
			}
		});
	}
}
