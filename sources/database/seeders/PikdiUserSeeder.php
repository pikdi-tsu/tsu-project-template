<?php

namespace Database\Seeders;

use App\Models\DataDosenTendik;
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
        $username = 'pikdi';
        $email = config('app.pikdi.email', 'pikdi@tsu.ac.id');
        $password = config('app.pikdi.password', 'pikdiTSU@25') . '@TSU25';

        $pikdiUser = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'username'          => $username,
                'password'          => Hash::make($password),
                'email_verified_at' => now(),
                'isactive'          => 1,
                'last_login_at'     => now(),
                'sso_id'            => null, // Null karena akun lokal
            ]
        );

        $roleSuperAdmin = Role::query()->firstOrCreate(['name' => 'super admin template']);

        $pikdiUser->assignRole($roleSuperAdmin);

        DataDosenTendik::query()->firstOrCreate(
            ['user_id' => $pikdiUser->id],
            [
                'nik'                => '999999', // Dummy NIK
                'status_pegawai'     => 'TETAP',
                'gelar_depan'        => '',
                'gelar_belakang'     => '',
                'jabatan_fungsional' => 'Super Admin IT',
                // Field lain biarkan null/default sesuai migrasi
            ]
        );

        $this->command->info('Akun Backdoor PIKDI berhasil ditanam & Profil dibuat!');
        $this->command->info("Email: $email");
        $this->command->info("Username: $username");
        $this->command->info("Password: (Sesuai Config)");
    }
}
