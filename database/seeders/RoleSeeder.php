<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['guard_name' => 'web', 'name' => 'access-management'],
            ['guard_name' => 'web', 'name' => 'access-management-dashboard'],
            ['guard_name' => 'web', 'name' => 'access-management-pages'],
            ['guard_name' => 'web', 'name' => 'access-management-posts'],
            ['guard_name' => 'web', 'name' => 'access-all-posts'],
            ['guard_name' => 'web', 'name' => 'publish-posts'],
            ['guard_name' => 'web', 'name' => 'delete-posts'],
            ['guard_name' => 'web', 'name' => 'auto-publish-posts'],
        ];

        // Permission::flushQueryCache();
        // Role::flushQueryCache();
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        $roles = [
            ['guard_name' => 'web', 'name' => 'user'],
            ['guard_name' => 'web', 'name' => 'administrator'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        app()['cache']->forget('spatie.permission.cache');

        Role::findByName('user', 'web')->givePermissionTo([
            'access-management-dashboard',
        ]);

        Role::findByName('administrator', 'web')->givePermissionTo(Permission::all());

        // Permission::flushQueryCache();
        // Role::flushQueryCache();
    }
}
