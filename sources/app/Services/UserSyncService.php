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
     * @throws \Throwable
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
                // Log akses ditolak
                Log::warning("[TSU_DENIED_ACCESS] Akses ditolak untuk user: " . ($userData['email'] ?? 'unknown'), [
                    'incoming' => $incomingRoles,
                    'allowed' => $allowedRoles
                ]);
                throw new \Exception('[TSU_DENIED_ACCESS] AKSES DITOLAK: Role Anda ' . implode(', ', $incomingRoles) . ' tidak diizinkan.');
            }
        }

        // LOGIC UPDATE / CREATE USER
        try {
            return DB::transaction(function () use ($userData, $accessToken, $onlyUpdateExisting) {
                $user = User::query()->where('sso_id', $userData['id'])->first();

                if (!$user) {
                    $user = User::query()->where('email', $userData['email'])->first();
                }

                if (!$user) {
                    $user = User::query()->where('username', $userData['username'])->first();
                }

                if ($onlyUpdateExisting && !$user) {
                    // Log skip user tidak ada di lokal
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

                $user->last_login_at    = now();

                if ($accessToken) {
                    $user->sso_access_token = $accessToken;
                }

                $user->save();

                $roleChanged = $this->syncUserRoles($user, $userData);

                $profileChanged = $this->syncUserProfile($user, $userData);

                $isAffected = $isNewUser || $userDirty || $roleChanged || $profileChanged;

                return [
                    'user' => $user,
                    'affected' => $isAffected
                ];
            });
        } catch (\Throwable $e) {
            // Log rrror login
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
     * Logika Sinkronisasi Role yang Aman
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

        // Cek flag is_identity
        $roleObjects = Role::whereIn('name', $currentRoles)->get()->keyBy('name');

        foreach ($currentRoles as $roleName) {
            $roleModel = $roleObjects->get($roleName);
            $isGlobalIdentity = $roleModel ? $roleModel->is_identity : false;

            if (!$isGlobalIdentity) {
                $rolesToKeep[] = $roleName;
            }
        }

        $finalRoles = array_unique(array_merge($validLocalRoles, $rolesToKeep));

        // Cek Perubahan
        $previousRoles = $user->getRoleNames()->toArray();
        sort($previousRoles);
        sort($finalRoles);

        // Jika role lama beda dengan role baru
        if ($previousRoles !== $finalRoles) {
            // Eksekusi Sync
            $user->syncRoles($finalRoles);
            return true;
        }

        return false;
    }

    /**
     * Routing ke Profil yang tepat
     */
    private function syncUserProfile(User $user, array $data): bool
    {
        $model = null;

        // Logika Profil User
        if ($user->hasAnyRole(['dosen', 'tendik', 'super admin', 'admin'])) {
            $this->syncDosenTendik($user, $data);
        } elseif ($user->hasRole('mahasiswa')) {
            $this->syncMahasiswa($user, $data);
        }

        // Cek perubahan data profil
        if ($model) {
            return $model->wasChanged() || $model->wasRecentlyCreated;
        }

        return false;
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
                'status_pegawai'     => $data['status_pegawai'] ?? null,
                'nik_ktp'            => $data['nik_ktp'] ?? null,
                'tempat_lahir'       => $data['tempat_lahir'] ?? null,
                'tgl_lahir'          => $data['tgl_lahir'] ?? null,
                'jenis_kelamin'      => $data['jk'] ?? null,
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
                'jenis_kelamin'     => $data['jk'] ?? null,
                'agama'             => $data['agama'] ?? null,
                'no_hp'             => $data['no_hp'] ?? null,
                'email_pribadi'     => $data['email_pribadi'] ?? null,
                'alamat_lengkap'    => $data['alamat'] ?? null,
                'nama_ayah'         => $data['nama_ayah'] ?? null,
                'nama_ibu'          => $data['nama_ibu'] ?? null,
                'no_hp_ortu'        => $data['no_hp_ortu'] ?? null,
            ]
        );
    }
}
