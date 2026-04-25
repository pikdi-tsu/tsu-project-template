<?php

namespace App\Services;

use App\Models\DataDosenTendik;
use App\Models\DataMahasiswa;
use App\Models\User;
use Exception;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class UserSyncService
{
    /**
     * Menangani sinkronisasi user (Create/Update & Sync Roles)
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
                Log::warning("[TSU_DENIED_ACCESS] Akses ditolak untuk user: " . ($userData['email'] ?? 'unknown'), [
                    'incoming' => $incomingRoles,
                    'allowed' => $allowedRoles
                ]);
                throw new \Exception('[TSU_DENIED_ACCESS] AKSES DITOLAK: Role Anda ' . implode(', ', $incomingRoles) . ' tidak diizinkan.');
            }
        }

        // LOGIC UPDATE / CREATE USER
        try {
            return User::query()->getConnection()->transaction(function () use ($userData, $accessToken, $onlyUpdateExisting) {
                $user = User::query()->where('sso_id', $userData['id'])->first();

                if (!$user) {
                    $user = User::query()->where('email', $userData['email'])->first();
                }

                if (!$user) {
                    $user = User::query()->where('username', $userData['username'])->first();
                }

                if ($onlyUpdateExisting && !$user) {
                    Log::info("[TSU_USER_SKIP] User tidak ditemukan: " . $userData['email']);
                    throw new \Exception('[TSU_USER_SKIP] User '. ucfirst(config('app.module.name')) .' tidak ditemukan.');
                }

                $isNewUser = false;
                if (!$user) {
                    $user = new User();
                    $user->password = null;
                    $isNewUser = true; // Tandai user baru
                }

                $user->sso_id           = $userData['id'] ?? $userData['sso_id'];
                $user->name             = $userData['name'];
                $user->email            = $userData['email'];
                $user->username         = $userData['username'] ?? $user->username;
                $user->avatar_url       = $userData['profile_photo_url'] ?? null;
                $user->isactive         = $userData['isactive'] ?? true;

                // Cek perubahan atribut
                $userDirty = $user->isDirty();
                $user->last_login_at = now();

                if ($accessToken) {
                    $user->sso_access_token = $accessToken;
                }

                $user->save();

                $roleChanged = $this->syncUserRoles($user, $userData);
                $profileChanged = $this->syncUserProfile($user, $userData);

                return [
                    'user' => $user,
                    'affected' => $isNewUser || $userDirty || $roleChanged || $profileChanged
                ];
            });
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), '[TSU_')) {
                throw $e;
            }

            Log::error("[TSU_SYS_CRITICAL] Gagal memproses user login: " . ($userData['email'] ?? 'unknown'), [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            throw new \Exception('[TSU_SYS_CRITICAL] Terjadi gangguan sistem Login di '. ucfirst(config('app.module.name')) .' .Silahkan hubungi PIKDI!');
        }
    }

    /**
     * Logika Sinkronisasi Role
     */
    private function syncUserRoles(User $user, array $userData): bool
    {
        $incomingRoleNames = [];

        // Normalisasi Data Role dari API
        if (!empty($userData['roles']) && is_array($userData['roles'])) {
            foreach ($userData['roles'] as $r) {
                $rName = is_array($r) ? ($r['name'] ?? '') : $r;
                $isIdentity = is_array($r) && (($r['is_identity'] ?? false));

                if ($rName) {
                    $lowerName = strtolower($rName);
                    $incomingRoleNames[] = $lowerName;

                    if ($isIdentity) {
                        // Role Identitas Global
                        Role::updateOrCreate(
                            ['name' => $lowerName, 'guard_name' => 'web'],
                            ['is_identity' => true]
                        );
                    } else {
                        // Role Fungsional Biasa
                        Role::where('name', $lowerName)
                            ->where('guard_name', 'web')
                            ->update(['is_identity' => false]);
                    }
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
        $currentRoles = $user->getRoleNames()->toArray();
        $rolesToKeep = [];
        $roleObjects = Role::whereIn('name', $currentRoles)->get()->keyBy('name');

        foreach ($currentRoles as $roleName) {
            $roleModel = $roleObjects->get($roleName);
            if ($roleModel && !$roleModel->is_identity) {
                $rolesToKeep[] = $roleName;
            }
        }

        $finalRoles = array_unique(array_merge($validLocalRoles, $rolesToKeep));
        $previousRoles = $user->getRoleNames()->toArray();

        sort($previousRoles);
        sort($finalRoles);

        if ($previousRoles !== $finalRoles) {
            // Eksekusi Sync
            $user->syncRoles($finalRoles);
            return true;
        }

        return false;
    }

    /**
     * Routing ke Profil
     */
    private function syncUserProfile(User $user, array $data): bool
    {
        // Logika Profil User
        if ($user->hasAnyRole(['dosen', 'tendik', 'super admin', 'admin'])) {
            $this->syncDosenTendik($user, $data);
            return true;
        } elseif ($user->hasRole('mahasiswa')) {
            $this->syncMahasiswa($user, $data);
            return true;
        }

        return false;
    }

    // --- LOGIC PROFIL DOSEN / TENDIK (MODE CLAIM PROFILE) ---
    private function syncDosenTendik(User $user, array $data): void
    {
        $nik = $data['nik'] ?? $data['username'];

        // Siapkan data yang pasti di-update (yaitu claim user_id)
        $updateData = [
            'user_id' => $user->id,
            'nama'    => $data['name'],
        ];

        // PERISAI ANTI TIMPA: Hanya isi data dari SSO jika memang ada nilainya
        // Biar data hasil migrasi SQL kita nggak rusak kerest jadi null
        if (!empty($data['nidn'])) $updateData['nidn'] = $data['nidn'];
        if (!empty($data['nip'])) $updateData['nip'] = $data['nip'];
        if (!empty($data['gelar_depan'])) $updateData['gelar_depan'] = $data['gelar_depan'];
        if (!empty($data['gelar_belakang'])) $updateData['gelar_belakang'] = $data['gelar_belakang'];
        if (!empty($data['jabatan_fungsional'])) $updateData['jabatan_fungsional'] = $data['jabatan_fungsional'];
        if (!empty($data['status_pegawai'])) $updateData['status_pegawai'] = $data['status_pegawai'];
        if (!empty($data['nik_ktp'])) $updateData['nik_ktp'] = $data['nik_ktp'];
        if (!empty($data['tempat_lahir'])) $updateData['tempat_lahir'] = $data['tempat_lahir'];
        if (!empty($data['tgl_lahir'])) $updateData['tgl_lahir'] = $data['tgl_lahir'];
        if (!empty($data['jk'])) $updateData['jenis_kelamin'] = $data['jk'];
        if (!empty($data['no_hp'])) $updateData['no_hp'] = $data['no_hp'];
        if (!empty($data['alamat'])) $updateData['alamat_domisili'] = $data['alamat'];

        // Cari berdasarkan NIK, lalu klaim / update
        DataDosenTendik::query()->updateOrCreate(
            ['nik' => $nik],
            $updateData
        );
    }

    // --- LOGIC PROFIL MAHASISWA (MODE CLAIM PROFILE) ---
    private function syncMahasiswa(User $user, array $data): void
    {
        $nim = $data['nim'] ?? $data['username'];

        $updateData = [
            'user_id' => $user->id,
            'nama'    => $data['name'],
        ];

        if (!empty($data['nik_ktp'])) $updateData['nik_ktp'] = $data['nik_ktp'];
        if (!empty($data['tempat_lahir'])) $updateData['tempat_lahir'] = $data['tempat_lahir'];
        if (!empty($data['tgl_lahir'])) $updateData['tgl_lahir'] = $data['tgl_lahir'];
        if (!empty($data['jk'])) $updateData['jenis_kelamin'] = $data['jk'];
        if (!empty($data['agama'])) $updateData['agama'] = $data['agama'];
        if (!empty($data['no_hp'])) $updateData['no_hp'] = $data['no_hp'];
        if (!empty($data['email_pribadi'])) $updateData['email_pribadi'] = $data['email_pribadi'];
        if (!empty($data['alamat'])) $updateData['alamat_lengkap'] = $data['alamat'];
        if (!empty($data['nama_ayah'])) $updateData['nama_ayah'] = $data['nama_ayah'];
        if (!empty($data['nama_ibu'])) $updateData['nama_ibu'] = $data['nama_ibu'];
        if (!empty($data['no_hp_ortu'])) $updateData['no_hp_ortu'] = $data['no_hp_ortu'];

        DataMahasiswa::query()->updateOrCreate(
            ['nim' => $nim],
            $updateData
        );
    }
}
