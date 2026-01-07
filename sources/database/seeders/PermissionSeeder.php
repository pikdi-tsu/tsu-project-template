<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $systemPermissions = [
            'system:dashboard:view', // View dashboard
            'system:user:view',      // View list user
            'system:user:create',    // Create user
            'system:user:edit',      // Edit user
            'system:user:delete',    // Delete user
            'system:role:manage',    // Manage role
            'system:settings:view',  // View setting
            'system:menu:manage',  // View setting
        ];

        $projectPermissions = [
            'template:data:view',
            'template:data:create',
        ];

        $allPermissions = array_merge($systemPermissions, $projectPermissions);

        foreach ($allPermissions as $p) {
            Permission::query()->firstOrCreate(['name' => $p]);
        }

        $superAdminRole = Role::query()->firstOrCreate([
            'name' => 'super admin',
            'guard_name' => 'web'
        ]);

        $superAdminRole->syncPermissions(Permission::all());
    }
}
