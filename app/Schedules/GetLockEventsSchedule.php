<?php


namespace App\Schedules;

use App\Models\Lock;
use App\Services\TTLockService;
use Illuminate\Support\Facades\Http;

class GetLockEventsSchedule
{
		public function __invoke()
		{
				Lock::query()->get()->map(function ($lock) {
						info($lock->events()->where('is_webhook',true)->latest()->first());
						if (now()->diffInMinutes($lock->events()->where('is_webhook',true)->latest()->first()?->created_at) > 30) {
								/** @var Lock $lock */
								$service = (new TTLockService($lock->lock_id));
								$events = $service->getLockEvents($lock,10);

								if (isset($events['data']['list'])) {
										foreach ($events['data']['list'] as $event) {
												if (!$lock->events()->where('lock_date',$event['lockDate'])->exists()) {
														$lock->events()->create([
															'record_id' => $event['recordId'],
															'record_type_from_lock' => $event['recordTypeFromLock'],
															'record_type' => $event['recordType'],
															'success' => $event['success'],
															'username' => $event['username'],
															'keyboard_pwd' => $event['keyboardPwd'],
															'lock_date' => $event['lockDate'],
															'server_date' => $event['serverDate'],
														]);


														Http::withBody(json_encode($event), 'application/json')
														//                ->withOptions([
														//                    'headers' => ''
														//                ])
														->post($lock->user->callback);


												}
										}
								}
						}
				});
		}
}
