<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        // Racik ulang token untuk dicocokkan
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
            // Parameter ke-2 null karena tidak ada access token OAuth
            $user = $syncer->handle($userData, null);

            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('alert', ['title' => 'Success', 'message' => 'Login Berhasil!', 'status' => 'success']);
        } catch (\Exception $e) {
            // Tangkap error jika Role ditolak oleh Service
            return redirect()->route('login')
                ->with('alert', ['title' => 'AKSES DITOLAK', 'message' => $e->getMessage(), 'status' => 'danger']);
        }
    }

    // Fitur Rescue Login untuk Alternatif jika Homebase down
    public function showRescueForm()
    {
        $data = [
            'title' => 'Login',
        ];

        return view('system::login.rescue-login', $data);
    }

    public function processRescueLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'rescue_key' => 'required',
        ]);

        // Ambil Hash Password dari Config
        $savedHash = config('app.pikdi.key.rescue');

        if (!$savedHash) {
            return back()->withErrors(['rescue_key' => 'Server Config Error: Rescue Hash missing.']);
        }

        // CEK PASSWORD (HASHING CHECK)
        // Membandingkan inputan user dengan Hash di server
        if (!Hash::check($request->rescue_key, $savedHash)) {
            return back()->withErrors(['rescue_key' => 'Kunci Akses Salah! Akses Ditolak.']);
        }

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
                return back()->withErrors(['username' => 'User tidak ditemukan di database lokal.']);
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

        return redirect('/dashboard')->with('success', "LOG: Masuk via $methodName sebagai {$user->name}");
    }
}
