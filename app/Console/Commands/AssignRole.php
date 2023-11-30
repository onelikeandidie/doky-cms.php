<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class AssignRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        $user = $this->choice('Which user?', $users->pluck('name')->toArray());
        $roles = Role::all();
        $role = $this->choice('Which role?', $roles->pluck('name')->toArray());
        $user = $users->first(fn(User $u) => $u->name === $user);
        $role = $roles->first(fn(Role $r) => $r->name === $role);
        $user->roles()->attach($role);
        $this->info("Assigned role {$role->name} to user {$user->name}.");
    }
}
