<?php

namespace App\Libraries\Sync;

use App\Libraries\Result\Result;
use App\Libraries\Sync\Drivers\Git;

interface ISyncDriver
{
    public function __construct(string $sync_path);

    /**
     * Returns true if the driver has been initialized.
     */
    public function isInitialized(): bool;

    /**
     * The init method should initialize the driver. Consult each driver's
     * documentation for more information on what this means.
     *
     * @return Result<Result::EMPTY, string>
     * @see Git::init()
     *
     */
    public function init(): Result;

    /**
     * The download method should download the latest version of the
     * documentation files from the remote. The result's ok value should
     * be an array of the files that were downloaded, and the err value
     * should be a string describing the error that occurred.
     *
     * ```
     * $driver = Sync::getInstance()->getDriver();
     * $result = $driver->download();
     * if ($result->isOk()) {
     *    $files = $result->getOk();
     *    // ...
     * }
     * ```
     *
     * @return Result<array, string>
     */
    public function download(): Result;

    /**
     * Returns the last timestamp that the documentation was synced.
     *
     * @return Result<int, string>
     */
    public function lastSync(): Result;
}
