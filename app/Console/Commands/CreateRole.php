<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new role.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('What is the name of the role?');
        $role = \App\Models\Role::create([
            'name' => $name,
            'permissions' => [],
        ]);
        $this->info("Created role with ID {$role->id}.");
        $this->info("Use `app:edit-permissions` to edit the permissions.");
    }
}
