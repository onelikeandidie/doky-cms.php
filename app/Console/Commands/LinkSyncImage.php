<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LinkSyncImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:link-sync-image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link the sync image directory to the public storage directory.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sync = \App\Libraries\Sync\Sync::getInstance();
        $syncDir = $sync->getDriver()->getDirectory();
        $publicDir = public_path('storage/sync/' . $sync->getDriver()->getRelativePath());
        $this->info('Linking ' . $syncDir . '/images to ' . $publicDir);
        if (!File::exists($syncDir . '/images')) {
            $this->error('Directory ' . $syncDir . '/images does not exist.');
            return;
        }
        // Create the parent directory if it doesn't exist
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
        }
        File::link($syncDir . '/images', $publicDir . '/images');
    }
}
