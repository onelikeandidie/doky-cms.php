<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class MakeBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-build {--skip-composer} {--stamped}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the app for production (use --verbose for more info)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get composer project version
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $version = $composer['version'];

        // If dist/ exists, delete it and create a new one
        $dist_dir = storage_path('app/dist');
        if (file_exists($dist_dir)) {
            // Force delete the dist dir
            File::deleteDirectory($dist_dir);
        }
        // Create the dist dir
        File::makeDirectory($dist_dir);

        // List of files to be copied
        $files = [
            "README.md",
            "LICENSE",
            ".env.example",
            // Laravel dependencies
            "composer.json",
            "composer.lock",
            "package.json",
            "package-lock.json",
            // Composer dependencies
            "vendor/**/*",
            // Laravel files
            ".env.example",
            "artisan",
            "bootstrap/app.php",
            // Config files
            "config/**/*",
            // Database files
            "database/factories/**/*",
            "database/migrations/**/*",
            "database/seeders/**/*",
            // Public files
            "public/**/*",
            // Resources files
            "resources/**/*",
            // Routes files
            "routes/**/*",
            // App files
            "app/**/*",
            // Cached files
            "bootstrap/cache/**/*",
            "storage/framework/views/**/*",
        ];

        // List of dirs to be created
        $empty_dirs = [
            "storage/app/public",
            "storage/framework/cache",
            "storage/framework/sessions",
            "storage/framework/testing",
            "storage/logs",
        ];

        // Delete files
        $delete_files = [
            "public/hot"
        ];

        // Run composer install --optimize-autoloader --no-dev
        if ($this->option('skip-composer') === false) {
            $this->info("Running composer install --optimize-autoloader --no-dev...");
            Process::command(['composer', 'install', '--optimize-autoloader', '--no-dev'])->run()->output();
        }

        // Vite build
        $this->info("Running npm run build...");
        $npm = config('node.path') . 'bin/npm';
        $command = Process::command([
            $npm,
            'run',
            'build',
            '--',
            '--mode=production',
            '--force'
        ]);
        $command->tty = true;
        $command->run();

        // Run Laravel optimization commands
        $this->info("Optimizing Laravel...");
        $this->info("Running php artisan optimize:clear...");
        $this->call('optimize:clear');
        $this->info("Running php artisan event:cache...");
        $this->call('event:cache');
        $this->info("Running php artisan view:cache...");
        $this->call('view:cache');

        // Copy files
        $this->info("Copying files...");
        $this->copy_files($files);

        // Create empty dirs
        $this->info("Creating empty dirs...");
        foreach ($empty_dirs as $dir) {
            $dir = storage_path('app/dist/' . $dir);
            File::makeDirectory($dir, 0755, true);
        }

        // Delete files
        $this->info("Deleting files...");
        foreach ($delete_files as $file) {
            $file = storage_path('app/dist/' . $file);
            File::delete($file);
        }

        // Make a tarball of the dist dir
        $this->info("Making a tarball of the $dist_dir dir...");
        $disk = Storage::disk('local');
        $disk->makeDirectory('build');
        $tarball = "build/app-$version";
        if ($this->option('stamped')) {
            // Add a timestamp to the tarball name
            $tarball .= "-" . date('Y-m-d_H-i-s');
        }
        $tarball .= ".tar.gz";
        $tarball = $disk->path($tarball);
        $disk->delete($tarball);

        // Save the name of the latest build into build/latest
        $disk->put('build/latest', $tarball);

        // Get all files in the dist dir
        $files = glob($dist_dir . '/*', GLOB_MARK);
        // Files without the absolute path
        $files = array_map(function ($file) use ($dist_dir) {
            return str_replace($dist_dir . '/', '', $file);
        }, $files);
        $command = Process::path($dist_dir)->command([
            'tar',
            '-czf',
            $tarball,
            ...$files,
            // Do not preserve permissions or owner since this might fuck up
            // the deployment server
            '--no-same-owner',
            '--no-same-permissions',
        ]);
        $this->info("Running '" . implode(' ', $command->command) . "'");
        $command->run();

        // Delete the dist dir
        $this->info("Deleting the dist dir...");
        File::deleteDirectory($dist_dir);
    }

    function copy_files($files): void
    {
        static $dist_dir = null;
        if ($dist_dir === null) {
            $dist_dir = storage_path('app/dist');
        }
        foreach ($files as $file) {
            // Ignore `.` and  `..` since they are the current and parent dirs
            if ($file === '.' || $file === '..') {
                continue;
            }
            // Trim last slash
            $file = rtrim($file, '/');
            if (str_contains($file, '**')) {
                // Split this glob
                $parts = explode('**', $file, 2);
                $dir = $parts[0];
                $rest = $parts[1];
                // Get all files in this dir
                $pattern = $dir . '{,.}*';
                $files = glob($pattern, GLOB_MARK | GLOB_BRACE);
                // Add /**/* to dirs
                $files = array_map(function ($file) {
                    // Trim last slash
                    $file = rtrim($file, '/');
                    // If `.` or `..`, skip it
                    if (str_ends_with($file, '.')) {
                        return false;
                    }
                    // If symlink, skip it
                    if (is_link($file)) {
                        $this->warn("Warning: Skipping $file since it is a symlink");
                        return false;
                    }
                    if (is_dir($file)) {
                        return $file . '/**/*';
                    }
                    return $file;
                }, $files);
                // Remove false values
                $files = array_filter($files);
                // Recursively copy files
                $this->copy_files($files);
                continue;
            }
            if (str_ends_with($file, '*')) {
                // Copy all files in the directory even .dotfiles
                $file = rtrim($file, '*');
                $file .= '{,.}*';
                $files = glob($file, GLOB_MARK | GLOB_BRACE);
                $this->copy_files($files);
                continue;
            }
            // If the file is a directory, show an error
            if (is_dir($file)) {
                $this->error("Error: $file is a directory");
                continue;
            }
            // If the file is a symlink, skip it
            if (is_link($file)) {
                $this->warn("Warning: Skipping $file since it is a symlink");
                continue;
            }
            // If the file doesn't exist, show an error
            if (!file_exists($file)) {
                $this->error("Error: $file doesn't exist");
                continue;
            }
            // Skip .gitignore files
            if (basename($file) === '.gitignore') {
                if ($this->option('verbose')) {
                    $this->info("Skipping $file");
                }
                continue;
            }
            // If -v is passed, show the file being copied
            if ($this->option('verbose')) {
                $this->info("Copying $file");
            }
            // Ensure the directory exists
            $dir = dirname($dist_dir . '/' . $file);
            if (!file_exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            // Copy the file
            File::copy($file, $dist_dir . '/' . $file);
        }
    }
}
