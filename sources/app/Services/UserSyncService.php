<?php

namespace App\Services;

use App\Models\DataDosenTendik;
use App\Models\DataMahasiswa;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class UserSyncService
{
    /**
     * Menangani sinkronisasi user (Create/Update & Sync Roles)
     * @param array $userData Data user mentah (format array dari JSON Homebase)
     * @param string|null $accessToken Token OAuth (Opsional, null jika dari Emergency Login)
     * @return User
     * @throws Exception Jika role tidak diizinkan
     */
    public function handle(array $userData, ?string $accessToken = null)
    {
        // LOGIC FILTER ALLOWED ROLE
        $allowedRoles = config('app.roles.allowed', []);

        if (!empty($allowedRoles)) {
            $incomingRoles = [];
            // Normalisasi data role (antisipasi format beda)
            if (!empty($userData['roles']) && is_array($userData['roles'])) {
                foreach ($userData['roles'] as $role) {
                    // Cek apakah formatnya string langsung atau array object
                    $rName = is_string($role) ? $role : ($role['name'] ?? null);
                    if ($rName) {
                        $incomingRoles[] = strtolower($rName);
                    }
                }
            }

            $allowedRoles = array_map('strtolower', $allowedRoles);

            // Cek intersection
            $hasAccess = !empty(array_intersect($incomingRoles, $allowedRoles));
            $isSuperAdminRole = in_array('super admin', $incomingRoles, true);

            if (!$hasAccess && !$isSuperAdminRole) {
                // Kita lempar Exception biar Controller yang nangkep
                throw new \RuntimeException('AKSES DITOLAK: Role Anda ' . implode(', ', $incomingRoles) . ' tidak diizinkan.');
            }
        }

        // LOGIC UPDATE / CREATE USER
        try {
            return DB::transaction(static function () use ($userData, $accessToken) {
                $user = User::query()->updateOrCreate(
                    ['sso_id' => $userData['id'] ?? $userData['sso_id']], // Kunci pakai ID Homebase
                    [
                        'name' => $userData['name'],
                        'username' => $userData['username'] ?? null,
                        'email' => $userData['email'],
                        'password' => null,
                        'avatar_url' => $userData['profile_photo_url'] ?? null,
                        'isactive' => $userData['isactive'] ?? true,
                        'sso_access_token' => $accessToken, // Bisa null kalau emergency
                    ]
                );

                // LOGIC SYNC ROLE
                $rolesToSync = [];
                $primaryRole = 'mahasiswa';

                if (isset($userData['roles']) && is_array($userData['roles'])) {
                    foreach ($userData['roles'] as $rolePayload) {
                        // Handle jika payload roles hanya array string ['dosen', 'admin']
                        $roleName = is_string($rolePayload) ? $rolePayload : $rolePayload['name'];

                        // Auto-create Role di lokal
                        Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                        $rolesToSync[] = $roleName;

                        // PRIMARY ROLE UNTUK PROFIL
                        // Prioritas Dosen/Tendik > baru Mahasiswa
                        if (in_array(strtolower($roleName), ['dosen', 'tendik', 'super admin', 'admin'])) {
                            $primaryRole = strtolower($roleName);
                        }
                    }
                }

                // Logika Pengaman Super Admin (Email PIKDI)
                if ($user->email === config('app.pikdi.email')) {
                    $rolesToSync[] = 'super admin';
                    Role::query()->firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
                }

                // Logika Preservation
                if ($user->hasRole('super admin')) {
                    $rolesToSync[] = 'super admin';
                }

                // Eksekusi Sync
                $user->syncRoles(array_unique($rolesToSync));

                // LOGIC SPOKE (ISI PROFIL MAHASISWA / DOSEN)
                if (in_array($primaryRole, ['dosen', 'tendik', 'staf', 'admin prodi', 'super admin', 'admin'])) {
                    $this->syncDosenTendik($user, $userData);
                } else {
                    $this->syncMahasiswa($user, $userData);
                }

                return $user;
            });
        } catch (\Throwable $e) {
            throw new \RuntimeException('Terjadi Kesalahan Login di '. config('app.module.name') .' .Silahkan hubungi PIKDI!');
        }
    }

    // --- LOGIC PROFIL DOSEN / TENDIK ---
    private function syncDosenTendik(User $user, array $data): void
    {
        DataDosenTendik::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nik'                => $data['nik'] ?? $data['username'],
                'nidn'               => $data['nidn'] ?? null,
                'nip'                => $data['nip'] ?? null,
                'gelar_depan'        => $data['gelar_depan'] ?? null,
                'gelar_belakang'     => $data['gelar_belakang'] ?? null,
                'jabatan_fungsional' => $data['jabatan_fungsional'] ?? null,
                'status_pegawai'     => $data['status_pegawai'] ?? 'TETAP',
                'nik_ktp'            => $data['nik_ktp'] ?? null,
                'tempat_lahir'       => $data['tempat_lahir'] ?? null,
                'tgl_lahir'          => $data['tgl_lahir'] ?? null,
                'jenis_kelamin'      => $data['jk'] ?? 'L',
                'no_hp'              => $data['no_hp'] ?? null,
                'alamat_domisili'    => $data['alamat'] ?? null,
            ]
        );
    }

    // --- LOGIC PROFIL MAHASISWA ---
    private function syncMahasiswa(User $user, array $data): void
    {
        DataMahasiswa::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nim'               => $data['nim'] ?? $data['username'],
                'nik_ktp'           => $data['nik_ktp'] ?? null,
                'tempat_lahir'      => $data['tempat_lahir'] ?? null,
                'tgl_lahir'         => $data['tgl_lahir'] ?? null,
                'jenis_kelamin'     => $data['jk'] ?? 'L',
                'agama'             => $data['agama'] ?? null,
                'no_hp'             => $data['no_hp'] ?? null,
                'email_pribadi'     => $data['email_pribadi'] ?? $user->email,
                'alamat_lengkap'    => $data['alamat'] ?? null,
                'nama_ayah'         => $data['nama_ayah'] ?? null,
                'nama_ibu'          => $data['nama_ibu'] ?? null,
                'no_hp_ortu'        => $data['no_hp_ortu'] ?? null,
            ]
        );
    }
}
