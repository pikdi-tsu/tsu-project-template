<?php

namespace Modules\System\Http\Controllers;

use App\Models\DataDosenTendik;
use App\Models\DataMahasiswa;
use App\Models\GroupUserModel;
use App\Models\MasterGroupModel;
use App\Models\ModulModel;
use App\Models\MahasiswaModel;
use App\Models\PegawaiModel;
use App\Models\PertanyaanKeamanan;
use App\Models\SiakadMahasiswa;
use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use App\Models\UserResetPasswordModel;
use App\Models\User;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Session, Crypt, DB;

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
            return redirect()->route('dashboard')
                ->with('alert', ['title' => 'Info', 'message' => 'Anda sudah login.', 'status' => 'info']);
        }

        $data = [
            'title' => 'Login Administrator (Local)',
            'app_name' => config('app.name', 'TSU Template'),
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
            Session::flash('alert', ['title' => 'Blocked', 'message' => "Terlalu banyak percobaan. Tunggu $seconds detik.", 'status' => 'danger']);
            return back();
        }

        $loginType = filter_var($request->identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginType  => $request->identity,
            'password'  => $request->password,
            'isactive' => 1 // Hanya user aktif yang boleh masuk
        ];

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();
            Session::put('appname', config('app.name', 'TSU Template'));

            $user = Auth::user();
            $roles = $user->getRoleNames()->toArray();

            if (in_array('dosen', $roles, true) || in_array('tendik', $roles, true) || in_array('super admin', $roles, true) || in_array('admin', $roles, true)) {
                $profil = DataDosenTendik::query()->where('user_id', $user->id)->first();
                $roleAktif = 'tendik';
            } else {
                $profil = DataMahasiswa::query()->where('user_id', $user->id)->first();
                $roleAktif = 'mahasiswa';
            }

            if ($profil) {
                Session::put('active_role', $roleAktif);
                Session::put('active_profile_id', $profil->id);
                Session::put('active_identity', $profil->nim ?? $profil->nik);
            } else {
                // logout paksa jika akun baru/not found
                Auth::logout();
                return back()->with('alert', ['title' => 'Gagal', 'message' => 'Profil User tidak ditemukan.', 'status' => 'danger']);
            }

            // Bersihkan rate limiter
            $this->clearLoginAttempts($request);

            return redirect()->route('dashboard')
                ->with('alert', ['title' => 'Success', 'message' => 'Login Berhasil!', 'status' => 'success']);
        }

        $this->incrementLoginAttempts($request);
        return back()->with('alert', ['title' => 'Gagal', 'message' => 'Username atau Password salah / Akun tidak aktif.', 'status' => 'danger']);
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
