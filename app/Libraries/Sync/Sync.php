<?php

namespace App\Libraries\Sync;

use App\Jobs\SyncInit;
use App\Libraries\Sync\Drivers\Git;
use App\Libraries\Sync\Exceptions\MissingDriverException;
use Illuminate\Support\Facades\Log;

class Sync {
    public const DRIVERS = [
        'git' => Git::class
    ];

    protected ISyncDriver $driver;

    protected static ?self $instance = null;

    /**
     * @throws MissingDriverException
     */
    public static function getInstance(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        $config_sync_path = config('sync.path');
        if (empty($config_sync_path)) {
            throw new \InvalidArgumentException('Missing config value: SYNC_PATH');
        }
        $config_driver = config('sync.driver');
        $driver = self::DRIVERS[$config_driver] ?? null;
        if ($driver === null) {
            throw new MissingDriverException("Driver $config_driver not found");
        }
        $self = new self();
        $self->driver = new $driver($config_sync_path);
        if (!$self->driver->isInitialized()) {
            Log::debug('Sync driver not initialized, dispatching SyncInit job');
            SyncInit::dispatch($self);
        }
        self::$instance = $self;
        return $self;
    }

    public function getDriver(): ISyncDriver
    {
        return $this->driver;
    }
}
