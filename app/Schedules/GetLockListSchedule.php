<?php


namespace App\Schedules;

use App\Services\JobsService;
use App\Models\Lock;

class GetLockListSchedule
{
    public function __invoke()
    {

        \App\Models\LocksCredential::query()->get()->map(function ($l) {
            (new JobsService($l->user->id))->getLockList();
        });
    }
}
