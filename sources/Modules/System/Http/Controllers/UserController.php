<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;

class UserController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('system:user');
    }

    // Halaman Utama
    public function index()
    {
        return view('system::user.index', [
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
                    return '<span class="badge badge-warning"><i class="fas fa-lock"></i> PROTECTED</span>';
                }

                if (auth()->id() === $row->id) {
                    return '<span class="badge badge-success">Sedang Online</span>';
                }

                if (auth()->user()->can('system:user:delete')) {
                    $btnKick = '<form action="' . route('system.user.destroy', $row->id) . '" method="POST" style="display:inline;">
                                ' . csrf_field() . ' ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-xs btn-danger btn-kick" title="Keluarkan User (Kick)">
                                    <i class="fas fa-power-off"></i> Kick
                                </button>
                            </form>';
                } else {
                    $btnKick = '<span class="badge badge-secondary p-2 shadow-sm" style="cursor: default;" title="Anda tidak memiliki akses ke action ini">
                                    <i class="fas fa-lock"></i> Kick (No Access)
                                </span>';
                }

                return $btnKick ?? null;
            })
            ->rawColumns(['avatar', 'roles', 'action'])
            ->make(true);
    }

    public function sync(UserSyncService $syncer)
    {
        $this->guard('create', 'system:user');

        $homebaseUrl  = config('app.tsu_homebase.url');
        $clientId     = config('app.oauth.client.id');
        $clientSecret = config('app.oauth.client.secret');

        try {
            // Access Token Client Credential
            // Hapus without verifying saat production
            $responseToken = Http::withoutVerifying()->post($homebaseUrl . '/oauth/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'scope'         => '', // Sesuaikan jika ada scope khusus
            ]);

            if ($responseToken->failed()) {
                throw new \Exception("Gagal Autentikasi ke Homebase. Cek Client ID/Secret.");
            }

            $accessToken = $responseToken->json()['access_token'];

            // TARIK DATA USER (Pakai Bearer Token)
            $apiUrl = $homebaseUrl . '/api/v1/users/sync';

            $response = Http::withoutVerifying()->withToken($accessToken)
                ->withHeaders(['Accept' => 'application/json'])
                ->timeout(60)
                ->get($apiUrl);

            if ($response->failed()) {
                throw new \Exception("Gagal mengambil data user. Status: " . $response->status());
            }

            $result = $response->json();
            $usersData = $result['data'] ?? [];

            if (empty($usersData)) {
                return redirect()->back()->with('warning', 'Tidak ada data user yang diterima.');
            }

            // Proses Sync (Re-use Service)
            $countSuccess = 0;
            $countError   = 0;

            foreach ($usersData as $userData) {
                try {
                    $syncer->handle($userData, null, true);
                    $countSuccess++;
                } catch (\Exception $e) {
                    $countError++;
                }
            }

            // LAPORAN
            $modulName = ucfirst(config('app.module.name'));
            $msg = "<b>Sinkronisasi Selesai!</b><br>";
            $msg .= "$countSuccess User berhasil diproses.<br>";
            if ($countError > 0) {
                $msg .= "$countError User dilewati (User belum masuk ke $modulName).";
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Sync Error: ' . $e->getMessage());
        }
    }

    // Hapus User
    public function destroy($id)
    {
        $this->guard('delete', 'system:user');

        $user = User::query()->findOrFail($id);

        // Proteksi Tambahan: Jangan hapus diri sendiri
        if(auth()->id() === $id){
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();
        return back()->with('success', 'User berhasil dikeluarkan dari modul ini!');
    }
}
