<?php


namespace App\Schedules;

use App\Models\Lock;

class UpdateRefreshTokenLocks
{
    public function __invoke()
    {
        \App\Models\Lock::query()->get()->map(function ($l) {
            /** @var Lock $l */
		        if ($l?->token?->credential) {
				        updateRefreshToken( $l);
		        }
        });
    }
}
