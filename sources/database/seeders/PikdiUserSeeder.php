<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PikdiUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $name = config('app.pikdi.name', 'PIKDI TSU');
        $email = config('app.pikdi.email', 'pikdi@tsu.ac.id');
        $password = config('app.pikdi.password', 'pikdiTSU@25') . '@TSU25';

        $pikdiUser = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'unit' => 'Pusat Informasi, Komunikasi dan Digital',
                'tsu_homebase_id' => null, // Penanda akun lokal
                'email_verified_at' => now(),
            ]
        );

        $roleSuperAdmin = Role::query()->firstOrCreate(['name' => 'Super Admin']); // Buat Dev/PIKDI
        $roleAdmin      = Role::query()->firstOrCreate(['name' => 'admin']);       // Buat Admin Unit/Fakultas
        $roleUser       = Role::query()->firstOrCreate(['name' => 'user']);

        $roleSuperAdmin->syncPermissions(Permission::all());

        $pikdiUser->assignRole($roleSuperAdmin);

        $this->command->info('✅ Akun Backdoor PIKDI berhasil ditanam!');
        $this->command->info("📧 Email: $email");
    }
}
