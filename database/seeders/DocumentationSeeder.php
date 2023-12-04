<?php

namespace Database\Seeders;

use App\Jobs\RefreshDatabase;
use App\Libraries\Sync\Sync;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        // Here's the thing, we can load the documentation from the docs dir
        // using the syncer but if there's no docs dir then we can't do that
        // so this will make some docs for us to start with
        if (Sync::getInstance()->getDriver()->isInitialized()) {
            Log::info('Sync driver is initialized, skipping documentation seeder');
            // Load the real docs
            RefreshDatabase::dispatchSync();
            return;
        }

        Log::info('Sync driver is not initialized, creating some base documentation');
        $this->call(InitialDocumentationSeeder::class);
    }
}
