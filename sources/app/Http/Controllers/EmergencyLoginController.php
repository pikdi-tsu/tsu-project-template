<?php

namespace App\Http\Controllers;

use App\Models\DataDosenTendik;
use App\Models\DataMahasiswa;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\MessageBag;

class EmergencyLoginController extends Controller
{
    // Fitur Login As dari Link Homebase
    public function login(Request $request, UserSyncService $syncer)
    {
        $payloadBase64 = $request->query('payload');
        $timestamp = $request->query('timestamp');
        $token     = $request->query('signature');

        // Cek Kadaluarsa (Link valid 5 menit)
        if (now()->timestamp - $timestamp > 300) {
            return response()->view('system::errors.index', [
                'title' => 'Expired Link!',
                'message' => 'Link Login Kadaluarsa. Silakan generate ulang dari Homebase.',
                'code' => 403
            ], 403);
        }

        // Validasi Tanda Tangan (Signature)
        $secret = config('app.pikdi.key.emergency');

        if (!$secret) {
            return response()->view('system::errors.index', [
                'title' => 'Oops! Ada yang hilang!',
                'message' => 'Server Config Error: Emergency Secret missing.',
                'code' => 500
            ], 500);
        }

        // Custom token untuk dicocokkan
        $expectedToken = hash_hmac('sha256', $payloadBase64 . $timestamp, $secret);

        if (!hash_equals($expectedToken, $token)) {
            return response()->view('system::errors.index', [
                'title' => 'Akses Ditolak!',
                'message' => 'Akses Ditolak! Token signature tidak valid.',
                'code' => 403
            ], 403);
        }

        $jsonPayload = base64_decode($payloadBase64);

        try {
            $userData = json_decode($jsonPayload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return response()->view('system::errors.index', [
                'title' => 'Oops!',
                'message' => 'Bad Payload Data',
                'code' => 400
            ], 400);
        }

        try {
            // Parameter null karena tidak ada access token OAuth
            $user = $syncer->handle($userData, null);

            Auth::login($user);

            // Cari profil berdasarkan role dari Homebase
            $requestedRole = $userData['role'] ?? null;

            // Cek roles user di lokal
            if ($requestedRole && $user->hasRole($requestedRole)) {
                $finalRole = $requestedRole;
            } else {
                // Fallback: role request ditolak/tidak ada, pakai role pertama
                $finalRole = $user->getRoleNames()->first();
            }

            $profil = null;

            if ($finalRole === 'mahasiswa') {
                $profil = DataMahasiswa::query()->where('user_id', $user->id)->first();
            } elseif (in_array($finalRole, ['dosen', 'tendik', 'admin prodi'])) {
                $profil = DataDosenTendik::query()->where('user_id', $user->id)->first();
            }

            // SIMPAN SESSION
            if ($finalRole) {
                $identity = $profil->nim ?? $profil->nik ?? $user->username;

                session([
                    'active_role'       => $finalRole,
                    'active_profile_id' => $profil->id ?? null,
                    'active_identity'   => $identity
                ]);
            } else {
                Auth::logout();
                throw new \Exception(
                    "User berhasil disinkronkan, tetapi tidak memiliki Role yang diizinkan di modul ini. \n" .
                    "Mohon hubungi Admin untuk menambahkan Role Lokal (Template) terlebih dahulu."
                );
            }

            return redirect()->route('dashboard')
                ->with('alert', ['title' => 'Success', 'message' => 'Login Berhasil!', 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->view('system::errors.index', [
                'title' => 'Emergency Login Gagal',
                'message' => $e->getMessage(),
                'code' => 403
            ], 403);
        }
    }

    // Fitur Rescue Login untuk Alternatif jika Homebase down
    public function showRescueForm()
    {
        // Throttle security setup
        $throttleKey = 'rescue-login:' . request()->ip();
        $rescueSeconds = 0;
        $errorBag = new MessageBag();

        if (session()->has('rescue_block_until')) {
            $timeLeft = session('rescue_block_until') - now()->timestamp;

            if ($timeLeft > 0) {
                $rescueSeconds = $timeLeft;
                $errorBag->add('rescue_key', "SECURITY LOCKDOWN: Tunggu <b class='rescue-timer-display'>$rescueSeconds</b> detik lagi.");
                session()->now('errors', $errorBag);
            } else {
                session()->forget('rescue_block_until');
            }
        } elseif (RateLimiter::attempts($throttleKey) > 0) {
            $attemptsLeft = RateLimiter::retriesLeft($throttleKey, 3);
            $errorBag->add('rescue_key', "Peringatan! Sisa percobaan Anda: <b>$attemptsLeft kali</b> lagi.");
        }

        $data = [
            'title' => 'Login',
            'existing_rescue_seconds' => $rescueSeconds,
        ];

        return view('system::login.rescue-login', $data)->withErrors($errorBag);
    }

    public function processRescueLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'rescue_key' => 'required',
        ]);

        // Max attempt setup
        $throttleKey = 'rescue-login:' . $request->ip();
        $maxAttempts = 3;

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            session()->put('rescue_block_until', now()->addSeconds($seconds)->timestamp);
            return back()
                ->withErrors(['rescue_key' => "SECURITY LOCKDOWN: Tunggu <b class='rescue-timer-display'>$seconds</b> detik lagi."])
                ->with('retry_seconds_rescue', $seconds)
                ->withInput($request->only('username'));
        }

        // Ambil Hash Password dari Config
        $savedHash = config('app.pikdi.key.rescue');
        if (!$savedHash) {
            return back()->withErrors(['rescue_key' => 'Server Config Error: Rescue Hash missing.']);
        }

        // CEK PASSWORD (HASHING CHECK)
        if (!Hash::check($request->rescue_key, $savedHash)) {
            RateLimiter::hit($throttleKey, 60);
            if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($throttleKey);
                session()->put('rescue_block_until', now()->addSeconds($seconds)->timestamp);
                return back()
                    ->withErrors(['rescue_key' => "SECURITY LOCKDOWN: Tunggu <b class='rescue-timer-display'>$seconds</b> detik lagi."])
                    ->with('retry_seconds_rescue', $seconds)
                    ->withInput($request->only('username'));
            }

            $attemptsLeft = RateLimiter::retriesLeft($throttleKey, $maxAttempts);

            return back()->withErrors([
                'rescue_key' => "Kunci Akses Salah! Sisa percobaan: <b>$attemptsLeft kali</b> lagi."
            ])->withInput($request->only('username'));
        }

        RateLimiter::clear($throttleKey);
        session()->forget('rescue_block_until');

        return $this->performLogin($request->username, 'Rescue Mode');
    }

    // Helper Login
    private function performLogin($username, $methodName)
    {
        // Cari user berdasarkan username/NIM/NIK
        $user = User::query()->where('username', $username)->first();

        // User not found handler
        if (!$user) {
            // login manual
            if (request()->isMethod('post')) {
                return back()
                    ->with('error', '<b>User Tidak Ditemukan!</b> Akun tersebut belum ada di database lokal.')
                    ->withErrors(['username' => 'User Tidak Ditemukan!'])
                    ->withInput(request()->only('username'));
            }
            // Via Login As Homebase
            return response()->view('system::errors.index', [
                'title' => 'User tidak dikenal!',
                'message' => 'Silahkan hubungi Admin untuk memperbaiki masalah ini.',
                'code' => 404
            ], 404);
        }

        // LOGIN PAKSA
        Auth::login($user);
        $user->update(['last_login_at' => now()]);

        return redirect()->route('dashboard')
            ->with('alert', [
                'title' => 'Rescue Success',
                'message' => "Login Darurat Berhasil via <b>$methodName</b> sebagai: <b>{$user->name}</b>",
                'status' => 'success'
            ]);
    }
}
