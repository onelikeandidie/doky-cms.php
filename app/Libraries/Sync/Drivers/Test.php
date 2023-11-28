<?php

namespace App\Libraries\Sync\Drivers;

use App\Libraries\Result\Result;
use App\Libraries\Sync\Exceptions\ConflictingChangesException;
use App\Libraries\Sync\Exceptions\DriverNotInitializedException;
use App\Libraries\Sync\ISyncDriver;
use Illuminate\Support\Facades\File;

class Test implements ISyncDriver
{
    private const TEST_FILE = 'index.md';
    private const TEST_FILE_CONTENT = <<<EOT
---
title: Test
---
# Test

This is a test file.
EOT;


    public function __construct(public string $sync_path)
    {
    }

    public function isInitialized(): bool
    {
        // Check if the sync path exists
        return file_exists($this->sync_path);
    }

    public function init(array $options = []): Result
    {
        if ($this->isInitialized()) {
            return Result::ok();
        }

        // Ensure the sync path exists
        File::makeDirectory($this->sync_path, 0755, true);

        // Insert a test file
        File::put($this->sync_path . '/' . self::TEST_FILE, self::TEST_FILE_CONTENT);

        // Update the last sync time
        File::put($this->sync_path . '/.last_sync', time());

        return Result::ok();
    }

    public function download(): Result
    {
        if (!$this->isInitialized()) {
            return Result::err(new DriverNotInitializedException());
        }

        // Check if there is changes to the local files
        $testContents = File::get($this->sync_path . '/' . self::TEST_FILE);
        $local_hash = md5($testContents);
        $test_hash = md5(self::TEST_FILE_CONTENT);
        if ($local_hash !== $test_hash) {
            return Result::err(new ConflictingChangesException());
        }

        // Update the last sync time
        File::put($this->sync_path . '/.last_sync', time());

        return Result::ok();
    }

    public function lastSync(): Result
    {
        if (!$this->isInitialized()) {
            return Result::err(new DriverNotInitializedException());
        }

        $lastSyncContents = File::get($this->sync_path . '/.last_sync');
        $lastSync = intval($lastSyncContents);
        return Result::ok($lastSync);
    }
}
