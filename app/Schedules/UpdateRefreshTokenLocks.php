<?php


namespace App\Schedules;

use App\Services\JobsService;
use App\Models\Lock;

class UpdateRefreshTokenLocks
{
    public function __invoke()
    {

        \App\Models\LocksCredential::query()->get()->map(function ($l) {
            (new JobsService())->refreshLockTocken($l->id);
        });
    }
}
