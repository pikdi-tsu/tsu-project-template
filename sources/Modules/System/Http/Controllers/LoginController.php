<?php

namespace Modules\System\Http\Controllers;

use App\Models\DataDosenTendik;
use App\Models\DataMahasiswa;
use App\Models\User;
use App\Services\TsuErrorHandlerService;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use ThrottlesLogins;

    protected $maxAttempts = 5;
    protected $decayMinutes = 1;

    public function username()
    {
        return 'email';
    }

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard.index')
                ->with('alert', ['title' => 'Info', 'message' => 'Anda sudah login.', 'status' => 'info']);
        }

        // SSO Block (IP Based)
        $ssoThrottleKey = 'sso-attempt:' . request()->ip();
        $ssoSeconds = 0;
        if (RateLimiter::tooManyAttempts($ssoThrottleKey, 5)) {
            $ssoSeconds = RateLimiter::availableIn($ssoThrottleKey);
            session()->now('error', "SECURITY LOCKDOWN: Tunggu <b id='sso-alert-timer'>$ssoSeconds</b> detik lagi.");
        }

        // Manual Block (Session Based)
        $manualSeconds = 0;
        if (session()->has('manual_block_until')) {
            $timeLeft = session('manual_block_until') - now()->timestamp;

            if ($timeLeft > 0) {
                $manualSeconds = $timeLeft;
                session()->now('error', "SECURITY LOCKDOWN: Tunggu <b id='sso-alert-timer'>$manualSeconds</b> detik lagi.");
            } else {
                session()->forget('manual_block_until');
            }
        }

        $data = [
            'title' => 'Login Administrator (Local)',
            'app_name' => config('app.name', 'TSU Template'),
            'existing_sso_seconds' => $ssoSeconds,
            'existing_manual_seconds' => $manualSeconds,
        ];

        return view('system::login.loginform', $data);
    }

    public function login(Request $request)
    {
        // Validasi Input Dasar
        $request->validate([
            'identity' => ['required'],
            'password' => ['required'],
        ]);

        // Cek Throttling (Anti Brute Force)
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $seconds = $this->limiter()->availableIn($this->throttleKey($request));
            session()->put('manual_block_until', now()->addSeconds($seconds)->timestamp);
            return back()
                ->with('error', "SECURITY LOCKDOWN: Tunggu <b id='sso-alert-timer'>$seconds</b> detik lagi.")
                ->with('retry_seconds_manual', $seconds)
                ->withInput($request->only('identity'));
        }

        $loginType = filter_var($request->identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginType  => $request->identity,
            'password'  => $request->password,
            'isactive' => 1 // Hanya user aktif yang boleh masuk
        ];

        if (Auth::attempt($credentials)) {
            try {
                $request->session()->regenerate();

                $superAdminModule = 'super admin ' . config('app.module.name');
                $user = Auth::user();
                $roles = $user->getRoleNames()->toArray();
                $isMahasiswa = in_array('mahasiswa', $roles, true);

                $profil = null;
                if ($isMahasiswa) {
                    $profil = DataMahasiswa::query()->where('user_id', $user->id)->first();
                    $roleLabel = 'mahasiswa';
                } else {
                    $profil = DataDosenTendik::query()->where('user_id', $user->id)->first();
                    $roleLabel = $roles[0] ?? 'user';
                }

                if ($profil) {
                    Session::put('active_role', $roleLabel);
                    Session::put('active_profile_id', $profil->id);
                    Session::put('active_identity', $profil->nim ?? $profil->nik ?? $profil->nidn ?? '-');
                } elseif ($user->email === config('app.pikdi.email') || in_array($superAdminModule, $roles, true)) {
                    // Biarkan masuk mode darurat tanpa profil
                    Session::put('active_role', 'super admin');
                    Session::put('active_identity', 'ADMIN-PUSAT');
                } else {
                    // Paksa logout jika profil tidak ada (kecuali super admin)
                    throw new \Exception('[TSU_LOGIN_NOPROFILE] Data Profil (Dosen/Mhs) tidak ditemukan. Hubungi Admin.');
                }

                // Bersihkan rate limiter dan session block
                session()->forget('manual_block_until');
                $this->clearLoginAttempts($request);

                return redirect()->route('admin.dashboard')
                    ->with('success', 'Login Berhasil!');

            } catch (\Exception $e) {
                Auth::logout(); // Pastikan logout jika ada error setelah login
                return TsuErrorHandlerService::handleHtml($e, '[TSU_LOGIN_FAIL]', 'Terjadi kesalahan saat proses login.', 'Gagal Login.', $request);
            }
        }

        $this->incrementLoginAttempts($request);
        return back()->with('error', 'Username atau Password salah / Akun tidak aktif.')->withInput($request->only('identity'));
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Logout Auth Laravel

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('alert', ['title' => 'Success', 'message' => 'Anda berhasil logout.', 'status' => 'success']);
    }
}
