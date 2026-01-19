<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $title = 'Profil Saya';
        $hasPhoto = !empty($user->avatar_url);
        $formattedRoles = $user->getRoleNames()->map(function($role) {
            $badgeClass = match($role) {
                'super admin' => 'badge-danger',    // Merah
                'dosen'       => 'badge-success',   // Hijau
                'mahasiswa'   => 'badge-primary',   // Biru
                'tendik'      => 'badge-warning',   // Kuning
                default       => 'badge-secondary'  // Abu-abu
            };

            return [
                'label' => ucwords($role), // Ubah 'super admin' -> 'Super Admin'
                'class' => $badgeClass
            ];
        });

        $isMahasiswa = $user->hasRole('mahasiswa');
        $identityLabel = $isMahasiswa ? 'NIM' : 'NIK / NIDN';
        $identityValue = $user->username ?? $user->nim ?? '-';

        $unitKerja = $user->unit ?? 'Menunggu Sinkronisasi';

        $accountStatus = [
            'isActive' => $user->isactive ?? true, // Default true kalau kolom belum ada
            'text'     => ($user->isactive ?? true) ? 'Akun Aktif' : 'Dibekukan',
            'class'    => ($user->isactive ?? true) ? 'btn-primary' : 'btn-danger',
            'icon'     => ($user->isactive ?? true) ? 'fa-check-circle' : 'fa-ban',
        ];

        return view('system::profile.index', compact(
            'user',
            'title',
            'hasPhoto',
            'formattedRoles',
            'identityLabel',
            'identityValue',
            'unitKerja',
            'accountStatus'
        ));
    }

    /**
     * Handle Update Ganti Password (Kirim Data ke API Homebase)
     */
    public function updatePassword(Request $request)
    {
        // Validasi Input di Sisi Client
        $request->validate([
            'current_password' => 'required',
            'password'     => 'required|min:8|confirmed',
        ]);


        try {
            // Ambil Token SSO
            $token = session('homebase_access_token');

            if (!$token) {
                return back()->with('error', 'Sesi kadaluarsa. Silakan login ulang.');
            }

            // Tembak API Homebase
            $response = Http::
                acceptJson()
                ->withoutVerifying()
                ->withToken($token)
                ->post(config('app.tsu_homebase.url') . '/api/v1/profile/change-password', [
                    'current_password'          => $request->current_password,
                    'password'              => $request->password,
                    'password_confirmation' => $request->password_confirmation,
                ]);

            // Cek Respon dari Homebase
            if ($response->successful()) {
                return back()->with('success', 'Password berhasil diperbarui di Pusat Data!');
            }

            $json = $response->json();

            // ERROR VALIDASI DARI HOMEBASE (Status 422)
            if ($response->status() === 422 && isset($json['errors'])) {
                return back()
                    ->withErrors($json['errors'])
                    ->withInput();
            }

            // ERROR PESAN BIASA (Misal 401, 403, atau custom message)
            $errorMessage = $response->json()['message'] ?? 'Gagal update password.';

            // Lempar sebagai error validasi biar field input jadi merah
            throw ValidationException::withMessages([
                'current_password' => [$errorMessage],
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghubungi server pusat: ' . $e->getMessage());
        }
    }

    /**
     * Handle Update Foto Profil (Kirim File ke API Homebase)
     */
    public function updatePhoto(Request $request)
    {
        // Validasi
        $request->validate([
            'photoprofile' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $user  = auth()->user();
            $file  = $request->file('photoprofile');
            $token = session('homebase_access_token'); // Pastikan nama session token benar

            // KIRIM KE HOMEBASE (API)
            $response = Http::withToken($token)
                ->acceptJson()
                ->withoutVerifying()
                ->attach(
                    'photoprofile', // Nama field yang diminta Homebase
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post(config('app.tsu_homebase.url') . '/api/v1/profile/change-photo');

            // CEK HASIL DARI HOMEBASE
            if ($response->successful()) {

                // Ambil URL dari JSON
                $homebaseUrl = $response->json()['data']['photo_url'];

                // Hapus accessor foto lama jika ada
                $oldPhoto = $user->avatar_url;

                if ($oldPhoto && !str_starts_with($oldPhoto, 'http')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPhoto);
                }

                // B. JURUS PAMUNGKAS: UPDATE DATABASE PAKSA (Query Builder)
                // Kita "bypass" Eloquent Model supaya tidak kena blokir $fillable
//                DB::table(config('auth.providers.users.table'), 'template_users')
                User::query()->where('id', $user->id)->update(['avatar_url' => $homebaseUrl]);

                // Update database LOKAL Template (Manual Query)
                // Asumsi kolom di tabel user template namanya 'profile_photo_path' atau sesuaikan
                $user->profile_photo_path = $homebaseUrl;

                return back()->with('success', 'Foto profil berhasil disinkronkan ke Pusat & Lokal!');
            }

            return back()->with('error', 'Gagal update ke Homebase: ' . $response->json()['message'] ?? 'Unknown Error');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
