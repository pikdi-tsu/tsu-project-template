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
    public function handle(array $userData, ?string $accessToken = null, bool $onlyUpdateExisting = false)
    {
        // LOGIC FILTER ALLOWED ROLE
        $allowedRoles = config('app.roles.allowed', []);
        if (!empty($allowedRoles)) {
            $incomingRoles = [];
            if (!empty($userData['roles']) && is_array($userData['roles'])) {
                foreach ($userData['roles'] as $role) {
                    $rName = is_string($role) ? $role : ($role['name'] ?? null);
                    if ($rName) {
                        $incomingRoles[] = strtolower($rName);
                    }
                }
            }
            $allowedRoles = array_map('strtolower', $allowedRoles);
            $hasAccess = !empty(array_intersect($incomingRoles, $allowedRoles));
            $isSuperAdminRole = in_array('super admin', $incomingRoles, true);

            if (!$hasAccess && !$isSuperAdminRole) {
                throw new \RuntimeException('AKSES DITOLAK: Role Anda ' . implode(', ', $incomingRoles) . ' tidak diizinkan.');
            }
        }

        // LOGIC UPDATE / CREATE USER
        try {
            return DB::transaction(function () use ($userData, $accessToken, $onlyUpdateExisting) {
                $user = User::query()
                    ->where('sso_id', $userData['id'])
                    ->orWhere('username', $userData['username'])
                    ->orWhere('email', $userData['email'])
                    ->first();

                if ($onlyUpdateExisting && !$user) {
                    throw new \RuntimeException('SKIP_SYNC: User template tidak ditemukan.');
                }

                if (!$user) {
                    $user = new User();
                    $user->password = null; // Default null karena SSO
                }

                $user->sso_id           = $userData['id'] ?? $userData['sso_id'];
                $user->name             = $userData['name'];
                $user->email            = $userData['email'];
                $user->username         = $userData['username'] ?? $user->username;
                $user->avatar_url       = $userData['profile_photo_url'] ?? null;
                $user->isactive         = $userData['isactive'] ?? true;
                $user->last_login_at    = now();

                if ($accessToken) {
                    $user->sso_access_token = $accessToken;
                }

                $user->save();

                // LOGIC SYNC ROLE
                $incomingRoleNames = [];

                // Normalisasi Data Role dari API
                if (!empty($userData['roles']) && is_array($userData['roles'])) {
                    foreach ($userData['roles'] as $r) {
                        $rName = is_string($r) ? $r : ($r['name'] ?? '');
                        if ($rName) {
                            $incomingRoleNames[] = strtolower($rName);
                        }
                    }
                }

                // Validasi master role lokal
                $validLocalRoles = Role::query()
                    ->where('guard_name', 'web')
                    ->whereIn('name', $incomingRoleNames)
                    ->pluck('name')
                    ->toArray();

                // Pengaman email pikdi
                if ($user->email === config('app.pikdi.email')) {
                    Role::query()->firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
                    if (!in_array('super admin', $validLocalRoles, true)) {
                        $validLocalRoles[] = 'super admin';
                    }
                }

                // Preserve local roles
                $moduleName = strtolower(config('app.module.name', 'template'));
                $protectedLocalRoles = [
                    "super admin {$moduleName}",
                    "admin {$moduleName}"
                ];
                $currentUserRoles = $user->getRoleNames()->toArray();
                $rolesToRescue = array_intersect($currentUserRoles, $protectedLocalRoles);
                $finalRoles = array_unique(array_merge($validLocalRoles, $rolesToRescue));

                // Sync Role
                $user->syncRoles($finalRoles);

                // Logika Profil User
                // Prioritas: Dosen > Tendik > Mahasiswa
                if (in_array('dosen', $incomingRoleNames, true)) {
                    $this->syncDosenTendik($user, $userData);
                } elseif (in_array('tendik', $incomingRoleNames, true)) {
                    $this->syncDosenTendik($user, $userData);
                } elseif (in_array('super admin', $incomingRoleNames, true) || in_array('admin', $incomingRoleNames, true)) {
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
