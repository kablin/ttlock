<?php


namespace App\Schedules;

use App\Jobs\BookingDeleteCode;
use App\Models\LockPinCode;

class DeleteLockPinCodesSchedule
{
	public function __invoke()
	{
		info('start delete shedule');

		$items = LockPinCode::query()->where('end', '<', now())->get();
		$index = 1;

		foreach ($items as $l) {
			BookingDeleteCode::dispatch($l)
				->delay(now()->addSeconds($index * 10)); // 10s, 20s, 30s...
			$index++;
		}
	}
}
