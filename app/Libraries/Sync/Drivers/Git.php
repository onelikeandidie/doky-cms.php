<?php

namespace App\Libraries\Sync\Drivers;

use App\Libraries\Result\Result;
use App\Libraries\Sync\Drivers\Exceptions\LastSyncUpdateFailedException;
use App\Libraries\Sync\Drivers\Exceptions\NoChangesException;
use App\Libraries\Sync\Exceptions\ConflictingChangesException;
use App\Libraries\Sync\Exceptions\DriverNotInitializedException;
use App\Libraries\Sync\ISyncDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Nette\Utils\Random;

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
    public function init(array $options = []): Result
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
        // Set up repo user info
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'config',
                'user.name',
                'Doky Sync',
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'config',
                'user.email',
                'doky@example.com',
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        // Set up the repo to not gpg sign commits
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'config',
                'commit.gpgsign',
                'false',
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
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
        // Checkout the branch
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'checkout',
                $this->branch,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            Log::debug('Sparse checkout failed', [
                'err' => $err,
            ]);
            return Result::err($err);
        }
        return Result::ok();
    }

    public function download(): Result
    {
        // Check if the repo is initialized
        if (!$this->isInitialized()) {
            return Result::err('Sync driver not initialized');
        }
        // Get the current commit hash of the repo
        $current_hash = $this->getCurrentHash();
        if ($current_hash->isErr()) {
            return $current_hash;
        }
        $current_hash = $current_hash->getOk();
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
        // Get the new commit hash of the repo
        $new_hash = $this->getCurrentHash();
        if ($new_hash->isErr()) {
            return $new_hash;
        }
        $new_hash = $new_hash->getOk();
        // Check if the commit hashes are the same
        if ($current_hash === $new_hash) {
            // No files were changed
            return Result::ok([]);
        }
        // Get the changed files
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'diff',
                '--name-only',
                $current_hash,
                $new_hash,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        // Get the changed files
        $changed_files = $process->output();
        $changed_files = explode("\n", $changed_files);
        // Remove any empty values
        $changed_files = array_filter($changed_files);
        return Result::ok($changed_files);
    }

    /**
     * @return Result<string, string>
     */
    public function getCurrentHash(): Result
    {
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'rev-parse',
                'HEAD',
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        return Result::ok(trim($process->output()));
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
            return Result::err(new LastSyncUpdateFailedException());
        }
        return Result::ok($now_timestamp);
    }

    public function upload(): Result
    {
        // Check if the repo is initialized
        if (!$this->isInitialized()) {
            return Result::err(new DriverNotInitializedException());
        }
        // So, upload is kind of a weird thing for git. We can't just push the changes
        // because we don't want to overwrite any changes that were made to the repo.
        // Instead, we branch off of the current branch, commit the changes, and then
        // try to merge the changes back into the main branch. If there are any conflicts
        // then we abort the merge and return an error.

        // Get the current commit hash of the repo
        $current_hash = $this->getCurrentHash();
        if ($current_hash->isErr()) {
            return $current_hash;
        }
        $current_hash = $current_hash->getOk();

        // Check if there are any changes to the repo
        $files_changed = $this->getChangedFiles($this->sync_path);
        if ($files_changed->isErr()) {
            return $files_changed;
        }
        $files_changed = $files_changed->unwrap();

        // Create a new branch
        $temp_sync_branch = 'sync-' . Random::generate(8);
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'checkout',
                '-b',
                $temp_sync_branch,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        // Add all files to the commit
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'add',
                ...$files_changed,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }

        $message = 'Sync commit ' . date('Y-m-d H:i:s');
        // Add each file to the commit to the message
        foreach ($files_changed as $file) {
            $message .= "\n\n" . $file;
        }

        // Commit the changes
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'commit',
                '-m',
                $message
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            // Check if the error is that there are no changes
            if (str_contains($err, 'nothing to commit')) {
                return Result::err(new NoChangesException());
            }
            return Result::err($err);
        }

        // Checkout to the main branch
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'checkout',
                $this->branch,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }

        // Pull the latest changes
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'pull',
                'origin',
                $this->branch,
            ])
            ->run();

        // Merge the changes back into the main branch
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'merge',
                $temp_sync_branch,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            // Check if the error is that there are no changes
            $err = $process->errorOutput();
            if (str_contains($err, 'Already up to date')) {
                return Result::err(new NoChangesException());
            }
            // Check if the error is that there are merge conflicts
            if (str_contains($err, 'Automatic merge failed')) {
                return Result::err(new ConflictingChangesException());
            }
            return Result::err($err);
        }

        // Push the changes
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'push',
                'origin',
                $this->branch,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            // Check if the error is that there are no changes
            $err = $process->errorOutput();
            if (str_contains($err, 'Everything up-to-date')) {
                return Result::err(new NoChangesException());
            }
            return Result::err($err);
        }

        // Delete the sync branch
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'branch',
                '-D',
                $temp_sync_branch,
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            // Check if the error is that there are no changes
            $err = $process->errorOutput();
            if (str_contains($err, 'not found')) {
                return Result::err(new NoChangesException());
            }
            return Result::err($err);
        }
        // Also update the last sync file
        $touch = $this->updateLastSync();
        if ($touch->isErr()) {
            return $touch;
        }
        return Result::ok($files_changed);
    }

    protected function getChangedFiles($dir)
    {
        // Check if there are any changes to the repo
        $process = Process::path($this->sync_path)
            ->command([
                $this->bin,
                'status',
                '--porcelain',
            ])
            ->run();
        // Check if the process failed
        if ($process->exitCode() !== 0) {
            $err = $process->errorOutput();
            return Result::err($err);
        }
        // Get the output
        $files_changed = $process->output();
        // Check if there are any changes
        if (empty(trim($files_changed))) {
            // No changes
            return Result::err(new NoChangesException());
        }

        // Get list of all files changed
        // The output of this command looks like this:
        // $ git status --porcelain
        //  M index.md
        // AM test.md
        // ?? test2/
        $files_changed = collect(explode("\n", $files_changed));
        // If there is a directory, then we need to recursively get all files in that directory
        $dirs = $files_changed->filter(function ($line) {
            return Str::endsWith($line, '/');
        })->map(function ($line) {
            // Remove the ?? from the beginning of the line
            $line = trim(substr($line, 2));
            // Get the full path to the directory
            return $line;
        });
        // Get the files in each directory
        foreach ($dirs as $dir) {
            $files = File::files($dir);
            // Add ?? to the beginning of each file
            $files = collect($files)->map(function ($file) {
                return '?? ' . $file;
            });
            // Add the files to the list of changed files
            $files_changed = $files_changed->merge($files);
        }
        // Remove any directories from the list
        $files_changed = $files_changed->filter(function ($line) {
            return !Str::endsWith($line, '/');
        });


        // Remove any empty values
        $files_changed = $files_changed
            ->map(function ($line) {
                // Remove the first two characters
                return trim(substr($line, 2));
            })
            ->filter(function ($line) {
                // Remove any empty lines
                return !empty($line);
            })
            ->filter(function ($line) {
                // Remove any lines that are not markdown files
                return Str::endsWith($line, '.md');
            })
            ->toArray();
        return Result::ok($files_changed);
    }

    public function getDirectory(): string
    {
        return $this->sync_path . '/' . $this->path;
    }
}
