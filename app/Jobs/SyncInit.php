<?php

namespace App\Jobs;

use App\Libraries\Sync\Sync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncInit implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Sync $sync)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $result = $this->sync->getDriver()->init();
        $result = $result->unwrap();
        dump($result);
    }

    /**
     * This makes sure that only one instance of this job is running at a time.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'sync-init';
    }
}
