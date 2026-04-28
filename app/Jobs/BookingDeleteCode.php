<?php

namespace App\Jobs;



use App\Services\TTLockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\Middleware\RateLimited;
use App\Models\LockApiLog;

class BookingDeleteCode implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $user;
	public $pincode;

	// Сколько раз пробовать
	public $tries = 5;

	public $backoff = 5;

	public function __construct($pincode)
	{
		$this->pincode = $pincode;
	}

	/*public function middleware(): array
	{
		return [new RateLimited('delete_key')];
	}*/


	public function delete()
	{

		LockApiLog::create([
			'is_ttlock_result' => false,
			'api_method' => 'shedule_delete',
			'ip' => '',
			'params' => json_encode(['pin_code_id' => $this->pincode->pin_code_id]),
		]);

		$key = (new TTLockService($this->pincode->lock->user))->deleteKey($this->pincode->lock,  $this->pincode->pin_code_id);
		info('auto delete key result ', $key);
		if ($key['status']) {
			$this->pincode->delete();
		}
	}

	public function handle()
	{
		try {
			if (now()->subHour(5) > $this->pincode->end) {
				$this->delete();
			}
		} catch (\Throwable $exception) {
		}
	}
}
