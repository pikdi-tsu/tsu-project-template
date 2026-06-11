<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\User;
use App\Services\TsuErrorHandlerService;
use App\Services\UserSyncService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('users:user');
    }

    // Halaman Utama
    public function index()
    {
        return view('users::user.index', [
            'title' => 'Monitoring Pengguna Siakad'
        ]);
    }

    // JSON DataTables
    public function datatable()
    {
        // Eager load roles biar performa cepat
        $data = User::query()->with('roles')->orderBy('last_login_at', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('avatar', function($row){
                // Avatar Otomatis dari Inisial Nama
                $url = $row->profile_photo_url;
                return '<img src="'.$url.'" class="img-circle elevation-2" style="width: 35px; height: 35px;" alt="User Image">';
            })
            ->editColumn('roles', function ($row) {
                if ($row->roles->isEmpty()) {
                    return '<span class="badge badge-secondary">User</span>';
                }
                $badges = '';
                foreach ($row->roles as $role) {
                    $badges .= '<span class="badge badge-primary mr-1">' . $role->name . '</span>';
                }
                return $badges;
            })
            ->addColumn('action', function ($row) {
                if ($row->email === config('app.pikdi.email', 'pikdi@tsu.ac.id')) {
                    return '<div class="text-center"><span class="badge badge-warning shadow-sm"><i class="fas fa-lock"></i> PROTECTED</span></div>';
                }

                if (auth()->id() === $row->id) {
                    return '<div class="text-center"><span class="badge badge-success shadow-sm"><i class="fas fa-circle text-white" style="font-size: 8px; vertical-align: middle;"></i> Sedang Online</span></div>';
                }

                $userRoles = $row->roles->pluck('name')->toJson();
                $editUrl = route('users.user.edit', $row->id);
                $deleteUrl = route('users.user.destroy', $row->id);
                $token = csrf_token();

                $btnEdit = '<button type="button" class="btn btn-sm btn-primary shadow-sm mr-1 btn-edit"
                                data-url="'.$editUrl.'"
                                title="Atur Role Aplikasi">
                                <i class="fas fa-user-tag mr-1"></i> Pasang Role
                            </button>';

                $btnDelete = '
                                <form action="'.$deleteUrl.'" method="POST" style="display:inline-block; margin: 0;">
                                    <input type="hidden" name="_token" value="'.$token.'">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="button" class="btn btn-sm btn-danger btn-delete shadow-sm" data-name="'. htmlspecialchars($row->name) .'" title="Hapus Akses Sistem">
                                        <i class="fas fa-user-times mr-1"></i> Cabut Akses
                                    </button>
                                </form>
                            ';

                return '<div class="text-center" style="white-space: nowrap;">' . $btnEdit . $btnDelete . '</div>';
            })
            ->rawColumns(['avatar', 'roles', 'action'])
            ->make(true);
    }

    public function sync(UserSyncService $syncer)
    {
        $this->guard('create', 'users:user');

        try {
            $homebaseUrl  = config('app.tsu_homebase.url');
            $clientId     = config('app.oauth.client.id');
            $clientSecret = config('app.oauth.client.secret');

            // Access Token Client Credential
            $responseToken = Http::withoutVerifying()->withHeaders(['X-Sync-Secret' => config('app.pikdi.key.sync')])->post($homebaseUrl . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => '', // Sesuaikan jika ada scope khusus
            ]);

            if ($responseToken->failed()) {
                throw new \Exception("[TSU_AUTH_FAIL] Gagal Otorisasi Client (Status: {$responseToken->status()}). Cek Client ID/Secret.");
            }

            $accessToken = $responseToken->json()['access_token'];
            if (!$accessToken) {
                throw new \Exception("[TSU_TOKEN_EMPTY] Respon token dari Homebase kosong.");
            }

            // TARIK DATA USER (Pakai Bearer Token)
            $apiUrl = $homebaseUrl . '/api/v1/users/sync';
            $stats = ['processed' => 0, 'updated' => 0, 'uptodate' => 0, 'failed' => 0];

            User::query()->whereNotNull('email')->chunk(50, function ($users) use ($apiUrl, $accessToken, $syncer, &$stats) {
                $emailList = $users->pluck('email')->toArray();
                try {
                    $response = Http::withoutVerifying()->withToken($accessToken)->withHeaders(['Accept' => 'application/json', 'X-Sync-Secret' => config('app.pikdi.key.sync')])->timeout(30)->post($apiUrl, ['emails' => $emailList]);

                    if ($response->successful()) {
                        $usersData = $response->json()['data'] ?? [];
                        foreach ($usersData as $userData) {
                            try {
                                $result = $syncer->handle($userData, null, true);
                                $stats['processed']++;
                                if ($result['affected'] === true) {
                                    $stats['updated']++;
                                } else {
                                    $stats['uptodate']++;
                                }
                            } catch (\Exception $e) {
                                $stats['failed']++;
                                Log::error("[TSU_USER_SKIP] Gagal proses user: " . ($userData['email'] ?? 'Unknown'), ['error_msg' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
                        }
                    } else {
                        $stats['failed'] += count($emailList);
                        Log::error("[TSU_BATCH_API_ERR] Gagal Sync Batch: ", ['status_code' => $response->status(), 'response_body' => $response->body(), 'target_emails' => $emailList]);
                    }
                } catch (\Exception $e) {
                    $stats['failed'] += count($emailList);
                    Log::error("[TSU_BATCH_CONN_ERR] Koneksi Error Saat Sync Batch: ", ['error' => $e->getMessage(), 'emails' => $emailList]);
                }
                return true;
            });

            if ($stats['processed'] === 0 && $stats['failed'] > 0) {
                throw new \Exception("[TSU_SYNC_ZERO] Sinkronisasi gagal total. Tidak ada data yang berhasil diambil.");
            }

            // LAPORAN
            $msg = "<h6 class='font-weight-bold mb-2'>Laporan Sinkronisasi User</h6>";
            $msg .= "<ul class='mb-0 pl-3' style='list-style-type: disc;'>";
            $msg .= "<li>Total user diperiksa: <b>{$stats['processed']}</b></li>";
            if ($stats['updated'] > 0) {
                $msg .= "<li>Data diperbarui: <b>{$stats['updated']}</b> user</li>";
            }
            if ($stats['uptodate'] > 0) {
                $msg .= "<li>Data up to date: {$stats['uptodate']} user</li>";
            }
            if ($stats['failed'] > 0) {
                $msg .= "<li class='text-danger font-weight-bold'>Gagal diproses: {$stats['failed']} user (Cek Log)</li>";
            }
            $msg .= "</ul>";

            if ($stats['failed'] > 0 && $stats['processed'] === 0) {
                return back()->with('error', 'Gagal melakukan sinkronisasi. Hubungi PIKDI untuk tindak lanjut!');
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            $defaultError = 'Terjadi kesalahan sistem yang tidak terduga.';
            if ($e instanceof ConnectionException) {
                $defaultError = 'Tidak dapat menghubungi Server Homebase. Cek koneksi internet.';
            }
            return TsuErrorHandlerService::handleHtml($e, '[TSU_SYS_CRITICAL]', $defaultError, 'Gagal Sync User.');
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'users:user');

        $user = User::with('roles')->findOrFail($id);

        // Ambil Role Lokal
        $allRoles = Role::where('is_identity', 0)->pluck('name', 'name')->all();

        // Default selected ambil Role Lokal user
        $userRoles = $user->roles()->where('is_identity', 0)->pluck('name')->toArray();

        return view('users::user._modal_pasang_role', compact('user', 'allRoles', 'userRoles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'roles' => 'nullable|array',
        ]);

        try {
            $user = User::findOrFail($id);

            // Protect Role Global user
            $globalRoles = $user->roles()->where('is_identity', 1)->pluck('name')->toArray();

            // Ambil Role Lokal submit form modal
            $submittedLocalRoles = $request->roles ?? [];

            // Merge Role global dan lokal
            $finalRolesToSync = array_merge($globalRoles, $submittedLocalRoles);

            $user->syncRoles($finalRolesToSync);

            return redirect()->back()->with('success', 'Role lokal untuk user ' . $user->name . ' berhasil diperbarui.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_UPD_FAIL]', 'Gagal menyimpan perubahan data.', "Gagal Update User ID: $id", $request);
        }
    }

    // Hapus User
    public function destroy($id)
    {
        $this->guard('delete', 'users:user');

        try {
            $user = User::query()->findOrFail($id);

            // Proteksi Tambahan: Jangan hapus diri sendiri
            if(auth()->id() == $id){
                return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
            }

            $user->delete();
            return back()->with('success', 'User berhasil dikeluarkan dari modul ini!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_USER_DELETE_FAIL]', 'Gagal menghapus user.', "Gagal Hapus User ID: $id");
        }
    }
}
