<?php

namespace Modules\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\System\Models\MenuSidebar;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dashboard (Menu Utama)
        MenuSidebar::query()->create([
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'order' => 1,
            'permission_name' => '',
        ]);

        // System Management (Parent Menu)
        $system = MenuSidebar::query()->create([
            'name' => 'System Management',
            'route' => '#',
            'icon' => 'fas fa-cogs',
            'order' => 99,
            'permission_name' => '',
        ]);

        // Submenu: Users
        MenuSidebar::query()->create([
            'name' => 'Users',
            'route' => 'system.user.index',
            'parent_id' => $system->id,
            'permission_name' => 'system:user:view',
            'icon' => 'fas fa-users',
        ]);

        // Submenu: Roles
        MenuSidebar::query()->create([
            'name' => 'Roles',
            'route' => 'system.role.index',
            'parent_id' => $system->id,
            'icon' => 'fas fa-user-shield',
            'permission_name' => 'system:role:view',
        ]);

        // Submenu: Role Permissions
        MenuSidebar::query()->create([
            'name' => 'Role Permissions',
            'route' => 'system.permission.index',
            'parent_id' => $system->id,
            'icon' => 'fas fa-file-shield',
            'permission_name' => 'system:permission:view',
        ]);

        // Submenu: Menus
        MenuSidebar::query()->create([
            'name' => 'Menus Management',
            'route' => 'system.menu.index',
            'parent_id' => $system->id,
            'permission_name' => 'system:menu:view',
            'icon' => 'fas fa-list',
        ]);
    }
}
