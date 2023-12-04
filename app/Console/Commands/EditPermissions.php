<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;

class EditPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:edit-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactively edit permissions for a role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roles = Role::all();
        $role_str = $this->choice(
            'Which role do you want to edit?',
            $roles->pluck('name')->toArray()
        );
        /** @var Role $role */
        $role = Role::where('name', $role_str)->first();
        $this->info("Editing permissions for $role->name");
        // Show the current permissions
        $this->info('Current permissions:');
        $this->info(implode(', ', $role->permissions));
        $this->info('Available permissions:');
        $this->info(implode(', ', Role::PERMISSIONS));
        $role_is_done_selecting = false;
        $role_permissions = [];
        $options = array_merge(Role::PERMISSIONS, ['*', 'Done']);
        while ($role_is_done_selecting === false) {
            $new_permission = $this->choice(
                "What permissions should the role have?",
                $options, 'Done'
            );
            if ($new_permission === 'Done') {
                $role_is_done_selecting = true;
                continue;
            }
            $role_permissions[] = $new_permission;
        }
        // Trim duplicate permissions
        $role_permissions = array_unique($role_permissions);
        // Add the permissions to the role
        $role->syncPermissions($role_permissions);
        $this->info("Edited permissions for $role->name");
    }
}
