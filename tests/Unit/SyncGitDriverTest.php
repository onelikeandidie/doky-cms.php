<?php

namespace Tests\Unit;

use App\Libraries\Sync\Sync;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class SyncGitDriverTest extends TestCase
{
    private string $temp_repo = '';
    private string $temp_sync = '';
    private bool $initialized = false;

    protected $app;

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->app = $this->createApplication();
    }

    public function init()
    {
        if ($this->initialized) {
            return;
        }
        // To test the git driver, we have to set up a local git repository
        // to clone from. This is done by creating a temporary directory
        // and initializing a git repository in it.
        $this->temp_repo = Storage::path('tests/sync-git-driver-test-repo');
        $this->temp_sync = Storage::path('tests/sync-git-driver-test-sync');

        // Clean up the temp directory
        if (file_exists($this->temp_repo)) {
            File::deleteDirectory($this->temp_repo);
        }
        File::makeDirectory($this->temp_repo, 0755, true);
        // Clean up the sync directory
        if (file_exists($this->temp_sync)) {
            File::deleteDirectory($this->temp_sync);
        }

        // Initialize the git repository
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'init',
            ])->run();

        // Configure the git repository
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'config',
                'user.name',
                'Test',
            ])->run();
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'config',
                'user.email',
                'test@example.com'
            ])->run();
        // Disable signing commits, as we don't have a GPG key
        // and it breaks testing on some systems
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'config',
                'commit.gpgsign',
                'false',
            ])->run();

        // Checkout the master branch
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'checkout',
                '-b',
                'master',
            ])->run();

        // Set up the local temp repo to accept pushes
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'config',
                'receive.denyCurrentBranch',
                'ignore',
            ])->run();

        // Create a test file
        $test_contents = <<<'EOT'
---
title: Test
---
# Test
EOT;
        File::put($this->temp_repo . '/index.md', $test_contents);

        // Add the test file to the git repository
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'add',
                'index.md',
            ])->run();

        // Commit the test file
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'commit',
                '-m',
                'Initial commit',
            ])->run();

        $this->initialized = true;
    }

    public function test_init()
    {
        $this->init();

        // Set up the config to use the git driver
        config([
            'sync.driver' => 'git',
            'sync.path' => $this->temp_sync,
            'sync.drivers.git.repo' => 'file://' . $this->temp_repo,
            'sync.drivers.git.path' => '*', // Sync the whole repository
            'sync.drivers.git.branch' => 'master',
        ]);
        $instance = Sync::getInstance();
        $driver = $instance->getDriver();
        $driver->init();

        // Check if the sync directory exists
        $this->assertTrue(file_exists($this->temp_sync));
        // Check if the last sync file exists
        $this->assertTrue($driver->lastSync()->isOk());
        // Check if the last sync file is a valid timestamp
        $last_sync = $driver->lastSync()->getOk();
        $this->assertTrue(is_int($last_sync) && $last_sync > 0);
        // Check if the test file exists
        $this->assertTrue(file_exists($this->temp_sync . '/index.md'));
    }

    public function test_download()
    {
        $this->init();

        // Set up the config to use the git driver
        config([
            'sync.driver' => 'git',
            'sync.path' => $this->temp_sync,
            'sync.drivers.git.repo' => 'file://' . $this->temp_repo,
            'sync.drivers.git.path' => '*', // Sync the whole repository
            'sync.drivers.git.branch' => 'master',
        ]);
        $instance = Sync::getInstance();
        $driver = $instance->getDriver();
        $driver->init();

        // Update the test file
        $test_contents = <<<'EOT'
---
title: Test
---
# Test
This is a test file.
EOT;
        File::put($this->temp_repo . '/index.md', $test_contents);
        // Commit the test file
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'add',
                'index.md',
            ])->run();
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'commit',
                '-m',
                'Update test file',
            ])->run();
        // Get the last sync timestamp
        $last_sync = $driver->lastSync()->unwrap();
        // Sleep for 1 second to ensure the timestamp is different
        Sleep::sleep(1);
        // Pull the changes
        $driver->download();
        // Check if the test file has been updated
        $this->assertEquals($test_contents, File::get($this->temp_sync . '/index.md'));
        // Check if the last sync file has been updated
        $this->assertGreaterThan($last_sync, $driver->lastSync()->unwrap());
    }

    public function test_upload()
    {
        $this->init();

        // Set up the config to use the git driver
        config([
            'sync.driver' => 'git',
            'sync.path' => $this->temp_sync,
            'sync.drivers.git.repo' => 'file://' . $this->temp_repo,
            'sync.drivers.git.path' => '*', // Sync the whole repository
            'sync.drivers.git.branch' => 'master',
        ]);
        $instance = Sync::getInstance();
        $driver = $instance->getDriver();
        $driver->init();

        // Update the test file
        $test_contents = <<<'EOT'
---
title: Test
---
# Test
This is a test file.
EOT;
        File::put($this->temp_sync . '/index.md', $test_contents);
        $result = $driver->upload();

        // This should work
        $this->assertTrue($result->isOk());

        // If it works, we need to run a command to update the temp git
        // repository. This is because our temp repo is a bare repository,
        // and doesn't have a working tree.
        $process = Process::path($this->temp_repo)
            ->command([
                'git',
                'reset',
                '--hard',
                'HEAD',
            ])->run();

        // Check if the test file has been updated
        $this->assertEquals($test_contents, File::get($this->temp_repo . '/index.md'));
    }
}
