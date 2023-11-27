<?php

namespace App\Libraries\Sync\Drivers;

use App\Libraries\Result\Result;
use App\Libraries\Sync\ISyncDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class Git implements ISyncDriver
{
    protected string $bin;

    protected string $repo;
    protected string $branch;
    protected string $path;

    protected bool $debug = true;

    public function __construct(
        protected string $sync_path
    )
    {
        $repo = config('sync.drivers.git.repo');
        $branch = config('sync.drivers.git.branch');
        $path = config('sync.drivers.git.path');

        // Check if any of the required config values are missing
        if (empty($repo)) {
            throw new \InvalidArgumentException('Missing config value: SYNC_GIT_REPOSITORY');
        }
        if (empty($branch)) {
            throw new \InvalidArgumentException('Missing config value: SYNC_GIT_BRANCH');
        }
        if (empty($path)) {
            throw new \InvalidArgumentException('Missing config value: SYNC_GIT_PATH');
        }

        $this->repo = $repo;
        $this->branch = $branch;
        $this->path = $path;

        $bin = config('sync.drivers.git.bin', 'git');
        $this->bin = $bin;

        $this->debug = config('app.debug', false);
    }

    public function isInitialized(): bool
    {
        return file_exists($this->sync_path);
    }

    /**
     * Clones the repo into the specified path.
     */
    public function init(): Result
    {
        // Check if the repo is already cloned
        if (file_exists($this->sync_path)) {
            return Result::ok();
        }
        // Make sure the sync path exists
        if (!file_exists($this->sync_path)) {
            $mkdir = File::makeDirectory($this->sync_path, 0755, true);
            if ($mkdir === false) {
                return Result::err('Failed to create sync path');
            }
        }
        // Clone the repo
        if ($this->path === "*") {
            $result = $this->fullClone();
        } else {
            $result = $this->sparseClone();
        }
        if ($result->isErr()) {
            return $result;
        }
        // Pull the repo
        return $this->download();
    }

    public function fullClone(): Result
    {
        // Clone the repo using git
        // git clone --depth 1 --branch <branch> <repo> <path>
        $process = Process::command([
            $this->bin,
            'clone',
            '--depth',
            '1',
            '--branch',
            $this->branch,
            $this->repo,
            $this->sync_path,
        ])->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        return Result::ok();
    }

    public function sparseClone(): Result
    {
        // Clone the repo using git
        // This is actually complicated since we need to clone a specific branch and directory
        // https://askubuntu.com/questions/460885/how-to-clone-only-some-directories-from-a-git-repository
        // That stackoverflow answer shows this process:
        // git init <repo>
        // cd <repo>
        // git remote add -f origin <url>
        // git config core.sparseCheckout true
        // echo "sync/dir/" >> .git/info/sparse-checkout
        // git pull origin master
        $process = Process::command([
            $this->bin,
            'init',
            $this->sync_path,
        ]);
        $process = $process->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        // Add the remote to the repo
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'remote',
                'add',
                '-f',
                'origin',
                $this->repo,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        // Configure sparse checkout for this repo
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'config',
                'core.sparseCheckout',
                'true',
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        // Add the path to the sparse checkout
        $append = File::append($this->sync_path . '/.git/info/sparse-checkout', $this->path . "\n");
        if ($append == 0) {
            return Result::err('Failed to append path to sparse checkout');
        }
        return Result::ok();
    }

    public function download(): Result
    {
        // Check if the repo is initialized
        if (!$this->isInitialized()) {
            return Result::err('Sync driver not initialized');
        }
        // Pull the repo
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'pull',
                'origin',
                $this->branch,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        // Create the last sync file
        $touch = $this->updateLastSync();
        if ($touch->isErr()) {
            return $touch;
        }
        return Result::ok();
    }

    public function lastSync(): Result
    {
        // The last sync timestamp is stored in a file called .last_sync
        $last_sync_file = $this->sync_path . '/.git/.last_sync';
        if (!file_exists($last_sync_file)) {
            return Result::ok(0);
        }
        $last_sync = File::get($last_sync_file);
        return Result::ok((int)$last_sync);
    }

    /**
     * @return Result<int, string>
     */
    public function updateLastSync(): Result
    {
        $last_sync_file = $this->sync_path . '/.git/.last_sync';
        $now_timestamp = time();
        $touch = File::put($last_sync_file, $now_timestamp);
        if ($touch === false) {
            return Result::err('Failed to create last sync file');
        }
        return Result::ok($now_timestamp);
    }
}
