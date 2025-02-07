<?php

namespace App\Console\Commands;

use App\Models\Lock;
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
	    \App\Models\Lock::query()->get()->map(function ($l) {
		    /** @var Lock $l */
		    if ($l?->token?->credential) {
			    updateRefreshToken($l);
		    }
	    });
        return Command::SUCCESS;
    }
}
