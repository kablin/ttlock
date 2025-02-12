<?php

namespace App\Console\Commands;

use App\Models\LocksCredential;
use App\Services\JobsService;
use Illuminate\Console\Command;

class UpdateRefreshTokenLocksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update-refresh-token-all-locks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
	    \App\Models\LocksCredential::query()->get()->map(function ($l) {
                (new JobsService())->refreshLockTocken($l->id);
	    });
        return Command::SUCCESS;
    }
}
