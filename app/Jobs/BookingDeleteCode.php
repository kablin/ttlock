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

class BookingDeleteCode implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $user;
	public $pincode;

	public function __construct($pincode)
	{
		$this->pincode = $pincode;
	}

	public function middleware(): array
	{
		return [new RateLimited('delete_key')];
	}


	public function delete()
	{
		$key = (new TTLockService())->deleteKey($this->pincode->lock,  $this->pincode->pin_code_id);

		if ($key['status']) {
			$this->pincode->delete();
		}
	}

	public function handle()
	{
		try {
			if (now() > $this->pincode->end) {
				$this->delete();
			}
		} catch (\Throwable $exception) {
		}
	}
}
