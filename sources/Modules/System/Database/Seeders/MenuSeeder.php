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
            'icon' => 'fas fa-cogs',
            'order' => 99,
            'permission_name' => 'system:settings:view',
        ]);

        // Submenu: Users
        MenuSidebar::query()->create([
            'name' => 'Users',
            'route' => 'users.index',
            'parent_id' => $system->id, // Anaknya System Management
            'permission_name' => 'system:user:manage',
            'icon' => 'fas fa-users',
        ]);

        // Submenu: Roles & Permissions
        MenuSidebar::query()->create([
            'name' => 'Roles & Permissions',
            'route' => 'roles.index',
            'parent_id' => $system->id,
            'icon' => 'fas fa-user-shield',
            'permission_name' => 'system:role:manage', // <--- PAKE YANG INI
        ]);

        // Submenu: Menus
        MenuSidebar::query()->create([
            'name' => 'Menu Management',
            'route' => 'menus.index',
            'parent_id' => $system->id,
            'permission_name' => 'system:menu:manage',
            'icon' => 'fas fa-list',
        ]);
    }
}
