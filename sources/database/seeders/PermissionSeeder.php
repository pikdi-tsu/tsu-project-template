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
            'users:user:view',         // View user
            'users:user:create',       // Create user
            'users:user:edit',         // Edit user
            'users:user:delete',       // Delete user

            'system:role:view',         // View role
            'system:role:create',       // Create role
            'system:role:edit',         // Edit role
            'system:role:delete',       // Delete role

            'system:permission:view',   // View permission
            'system:permission:create', // Create permission
            'system:permission:edit',   // Edit permission
            'system:permission:delete', // Delete permission

            'system:menu:view',         // View menu
            'system:menu:create',       // Create menu
            'system:menu:edit',         // Edit menu
            'system:menu:delete',       // Delete menu
        ];

        $adminPermissions = [
            'users:user:view',         // View user
            'users:user:edit',         // Edit user
            'users:user:delete',       // Delete user
            'system:role:view',         // View role
            'system:role:edit',         // Edit role
            'system:permission:view',   // View permission
            'system:permission:create', // Create permission
            'system:permission:edit',   // Edit permission
            'system:permission:delete', // Delete permission
            'system:menu:view',         // View menu
            'system:menu:edit',         // Edit menu
        ];

        foreach ($systemPermissions as $p) {
            Permission::query()->firstOrCreate(['name' => $p]);
        }

        Role::query()->firstOrCreate([
            'name' => 'super admin ' . config('app.module.name'),
            'guard_name' => 'web'
        ]);

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin ' . config('app.module.name'),
            'guard_name' => 'web'
        ]);

        $adminRole->syncPermissions($adminPermissions);
    }
}
