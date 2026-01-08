<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SsoController extends Controller
{
    // Fungsi Melempar User ke TSU Homebase
    public function redirect()
    {
        $query = http_build_query([
            'client_id' => config('app.oauth.authorization_id'),
            'redirect_uri' => config('app.oauth.authorization_redirect'),
            'response_type' => 'code',
            'scope' => '',
        ]);

        return redirect(config('app.tsu_homebase.url') . '/oauth/authorize?' . $query);
    }

    // Fungsi Menangkap User + Tukar Token
    public function callback(Request $request)
    {
        // Cek TSU Homebase error
        if ($request->has('error')) {
            if ($request->error === 'access_denied') {
                return redirect()->route('login')
                    ->with('error', 'Login dibatalkan. Anda menolak memberikan akses.');
            }

            // Error lain dari Homebase (misal invalid scope)
            abort(403, 'SSO Error: ' . $request->error_description);
        }

        // Validasi Code
        if (! $request->code) {
            return redirect('/login')->with('error', 'Login SSO Gagal: Authorization Code tidak ditemukan.');
        }

        try {
            // Tukar Code jadi Token (Ke Homebase)
            // Note: hapus withoutVerifying() saat production (https)
            $response = Http::withoutVerifying()->asForm()->post(config('app.tsu_homebase.url') . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('app.oauth.authorization_id'),
                'client_secret' => config('app.oauth.authorization_secret'),
                'redirect_uri' => config('app.oauth.authorization_redirect'),
                'code' => $request->code,
            ]);

            if ($response->failed()) {
                // dd($response->json());
                return redirect()->route('login')->with('error', 'Gagal menukar token dengan server SSO.');
            }

            $accessToken = $response->json()['access_token'];

            // Ambil Data Profil User dari Homebase
            // Note: hapus withoutVerifying() saat production (https)
            $userResponse = Http::withoutVerifying()
                ->withToken($accessToken)
                ->acceptJson()
                ->get(config('app.tsu_homebase.url') . '/api/v1/profile');

            if ($userResponse->failed()) {
                return redirect()->route('login')->with('error', 'Gagal mengambil data profil user.');
            }

            $userData = $userResponse->json();

            // Filer allowed role
            $allowedRoles = config('app.allowed_roles', []);

            if (!empty($allowedRoles)) {
                $incomingRoles = [];
                if (!empty($userData['roles']) && is_array($userData['roles'])) {
                    foreach ($userData['roles'] as $role) {
                        if (isset($role['name'])) {
                            $incomingRoles[] = strtolower($role['name']);
                        }
                    }
                }

                $allowedRoles = array_map('strtolower', $allowedRoles);

                // Cek role user dengan whitelist
                $hasAccess = !empty(array_intersect($incomingRoles, $allowedRoles));

                // PENGECUALIAN SUPER ADMIN
                $isSuperAdminRole = in_array('super admin', $incomingRoles, true);

                if (!$hasAccess && !$isSuperAdminRole) {
                    return redirect()->route('login')
                        ->with('alert', ['title' => 'AKSES DITOLAK', 'message' => 'Role Anda ' . implode(', ', $incomingRoles) . ' tidak diizinkan masuk ke aplikasi ini.', 'status' => 'danger']);
                }
            }

            // Update atau Buat User Lokal
            $user = User::query()->updateOrCreate(
                ['id' => $userData['id']],
                [
                    'tsu_homebase_id' => $userData['id'],
                    'name' => $userData['name'],
                    'username' => $userData['username'] ?? null,
                    'nidn' => $userData['nidn'] ?? null,
                    'email' => $userData['email'],
                    'password' => null, // Password null karena login via SSO
                    'unit' => $userData['unit'] ?? null,
                    'isactive' => $userData['isactive'] ?? true,
                    'sso_access_token' => $accessToken,
                ]
            );

            // Simpan Token di Session
            session(['homebase_access_token' => $accessToken]);

            $rolesToSync = [];

            // Ambil Role dari Kiriman Homebase
            if (isset($userData['roles']) && is_array($userData['roles'])) {
                foreach ($userData['roles'] as $rolePayload) {
                    $roleName = $rolePayload['name'];

                    // Auto-create Role di lokal
                    Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

                    $rolesToSync[] = $roleName;
                }
            }

            // Logika Pengaman Super Admin (Email PIKDI)
            if ($user->email === config('app.pikdi.email')) {
                $rolesToSync[] = 'super admin';
                Role::query()->firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
            }

            // Logika Preservation (Opsional)
            // Jika user lokal ini sudah punya Super Admin.
            if ($user->hasRole('super admin')) {
                $rolesToSync[] = 'super admin';
            }

            // Eksekusi Sync
            $user->syncRoles(array_unique($rolesToSync));

            // Login User
            Auth::login($user);

            return redirect()->route('dashboard')->with('alert', ['title' => 'Success', 'message' => 'Login Berhasil!', 'status' => 'success']);
        } catch (ConnectionException $e) {
            // KASUS: HOMEBASE Down
            return response()->view('system::errors.index', [
                'title' => 'Server SSO Tidak Dapat Dihubungi',
                'message' => 'Sistem tidak dapat terhubung ke TSU Homebase. Kemungkinan server sedang down atau ada gangguan jaringan. Silakan coba sesaat lagi.',
                'code' => 503
            ], 503);

        } catch (\InvalidArgumentException $e) {
            // KASUS: STATE MISMATCH
            return redirect()->route('login')
                ->with('error', 'Sesi login kadaluarsa. Silakan coba login ulang.');

        } catch (\Exception $e) {
            // KASUS: ERROR LAIN-LAIN (General)
            \Log::error($e->getMessage());
            return response()->view('system::errors.index', [
                'title' => 'Terjadi Kesalahan Login',
                'message' => 'Terjadi kesalahan teknis saat memproses login',
                'code' => 500
            ], 500);
        }
    }
}
