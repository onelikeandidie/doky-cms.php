<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $config_password = config('app.admin_password');
        if (empty($config_password)) {
            throw new \Exception('Admin password is not set in config/app.php');
        }
        if (strlen($config_password) < 8 && app()->environment('production')) {
            throw new \Exception('Admin password must be at least 8 characters long');
        }
        $admin = new User([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt($config_password),
        ]);
        $admin->save();

        // Create a role for the admin
        $admin_role = new Role([
            'name' => 'Admin',
            // Give the admin role all permissions
            'permissions' => Role::PERMISSIONS,
        ]);

        // Give the admin role to the admin user
        $admin->roles()->save($admin_role);

        // Create some base documentation articles
        $this->call(DocumentationSeeder::class);
    }
}
