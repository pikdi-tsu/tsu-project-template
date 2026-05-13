<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TsuErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        return view('users::profile.index', compact(
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
        $request->validate([
            'current_password' => 'required',
            'password'     => 'required|min:8|confirmed',
        ]);

        try {
            $token = session('homebase_access_token');
            if (!$token) {
                throw new \Exception('[TSU_SESSION_EXPIRED] Sesi kadaluarsa. Silakan login ulang.');
            }

            $response = Http::acceptJson()->withoutVerifying()->withToken($token)
                ->post(config('app.tsu_homebase.url') . '/api/v1/profile/change-password', [
                    'current_password'          => $request->current_password,
                    'password'              => $request->password,
                    'password_confirmation' => $request->password_confirmation,
                ]);

            if ($response->successful()) {
                return back()->with('success', 'Password berhasil diperbarui di Pusat Data!');
            }

            if ($response->status() === 422 && isset($response->json()['errors'])) {
                throw ValidationException::withMessages($response->json()['errors']);
            }

            $errorMessage = $response->json()['message'] ?? 'Gagal update password.';
            throw ValidationException::withMessages(['current_password' => [$errorMessage]]);

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PWD_UPD_FAIL]', 'Gagal menghubungi server pusat.', 'Gagal Update Password.', $request);
        }
    }

    /**
     * Handle Update Foto Profil (Kirim File ke API Homebase)
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photoprofile' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $user  = auth()->user();
            $file  = $request->file('photoprofile');
            $token = session('homebase_access_token');

            $response = Http::withToken($token)->acceptJson()->withoutVerifying()
                ->attach('photoprofile', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post(config('app.tsu_homebase.url') . '/api/v1/profile/change-photo');

            if ($response->successful()) {
                $homebaseUrl = $response->json()['data']['photo_url'];

                $oldPhoto = $user->avatar_url;
                if ($oldPhoto && !str_starts_with($oldPhoto, 'http') && \Storage::disk('public')->exists($oldPhoto)) {
                    \Storage::disk('public')->delete($oldPhoto);
                }

                User::query()->where('id', $user->id)->update(['avatar_url' => $homebaseUrl]);

                $user->avatar_url = $homebaseUrl;
                $user->save();

                return back()->with('success', 'Foto profil berhasil disinkronkan ke Pusat & Lokal!');
            }

            $errorMessage = $response->json()['message'] ?? 'Unknown Error';
            throw new \Exception("[TSU_PHOTO_UPD_FAIL] Gagal update ke Homebase: $errorMessage");

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PHOTO_UPD_FAIL]', 'Terjadi kesalahan sistem.', 'Gagal Update Foto Profil.', $request);
        }
    }
}
