<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DataDosenTendik;
use App\Models\DataMahasiswa;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SsoController extends Controller
{
    // Fungsi Melempar User ke TSU Homebase
    public function redirect()
    {
        $throttleKey = 'sso-attempt:' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return redirect()->route('login')
                ->with('error', "Terlalu banyak klik SSO. Tunggu <b id='sso-alert-timer'>$seconds</b> detik.")
                ->with('retry_seconds_sso', $seconds);
        }

        RateLimiter::hit($throttleKey, 60);

        $query = http_build_query([
            'client_id' => config('app.oauth.authorization.id'),
            'redirect_uri' => config('app.oauth.authorization.redirect'),
            'response_type' => 'code',
            'scope' => '',
        ]);

        return redirect(config('app.tsu_homebase.url') . '/oauth/authorize?' . $query);
    }

    // Fungsi Menangkap User + Tukar Token
    public function callback(Request $request, UserSyncService $syncer)
    {
        // Cek TSU Homebase error
        if ($request->has('error')) {
            if ($request->error === 'access_denied') {
                return redirect()->route('login')
                    ->with('error', 'Login dibatalkan. Anda menolak memberikan akses.');
            }

            // Error lain dari Homebase
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
                'client_id' => config('app.oauth.authorization.id'),
                'client_secret' => config('app.oauth.authorization.secret'),
                'redirect_uri' => config('app.oauth.authorization.redirect'),
                'code' => $request->code,
            ]);

            if ($response->failed()) {
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

            try {
                $user = $syncer->handle($userData, $accessToken);

                // Simpan token di session
                session(['homebase_access_token' => $accessToken]);

                Auth::login($user);

                // Cek role user dari Spatie atau UserSync
                $roles = $user->getRoleNames()->toArray();

                // Prioritas Dosen
                if (in_array('dosen', $roles, true) || in_array('tendik', $roles, true)) {
                    $profil = DataDosenTendik::query()->where('user_id', $user->id)->first();
                    $roleAktif = 'dosen';
                } else {
                    $profil = DataMahasiswa::query()->where('user_id', $user->id)->first();
                    $roleAktif = 'mahasiswa';
                }

                if ($profil) {
                    session([
                        'active_role' => $roleAktif,
                        'active_profile_id' => $profil->id,
                        'active_identity' => $profil->nim ?? $profil->nik
                    ]);
                }

                return redirect()->route('dashboard')
                    ->with('alert', ['title' => 'Success', 'message' => 'Login Berhasil!', 'status' => 'success']);
            } catch (\Exception $e) {
                return redirect()->route('login')
                    ->with('alert', ['title' => 'AKSES DITOLAK', 'message' => $e->getMessage(), 'status' => 'danger']);
            }
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
            Log::error($e->getMessage());
            return response()->view('system::errors.index', [
                'title' => 'Terjadi Kesalahan Login',
                'message' => 'Terjadi kesalahan teknis saat memproses login',
                'code' => 500
            ], 500);
        }
    }
}
