<?php


namespace App\Schedules;

use App\Jobs\BookingDeleteCode;
use App\Models\LockPinCode;

class DeleteLockPinCodesSchedule
{
	public function __invoke()
	{
		info('start delete shedule');
		LockPinCode::query()->get()->each(function ($l) {
			if ($l->end) {
				BookingDeleteCode::dispatch($l);
			}
		});
	}
}
