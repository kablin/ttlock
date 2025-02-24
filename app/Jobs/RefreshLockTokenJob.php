<?php

namespace App\Jobs;




use Illuminate\Bus\Queueable;
use App\Models\LocksCredential;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshLockTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    /**
     * Create a new job instance.
     */
    public function __construct(private int $id) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($credential = LocksCredential::find($this->id))
            updateRefreshToken($credential);
    }
}
