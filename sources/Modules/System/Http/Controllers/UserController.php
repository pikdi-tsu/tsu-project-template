<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Models\DataMahasiswa;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

                return $this->getActionButtons($row, 'system:user', [
                    'edit_url'   => route('system.user.edit', $row->id),
                    'use_modal'  => false,
                    'delete_url' => route('system.user.destroy', $row->id)
                ]);
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
            try {
                // Hapus without verifying saat production
                $responseToken = Http::withoutVerifying()->post($homebaseUrl . '/oauth/token', [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'scope' => '', // Sesuaikan jika ada scope khusus
                ]);
            } catch (ConnectionException $e) {
                Log::error("[TSU_CONN_REFUSED] ClientID: ". $clientId, [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception("[TSU_CONN_REFUSED] Tidak dapat menghubungi Server Homebase. Cek koneksi internet.");
            }

            // Error Response (Client ID Salah / Secret Salah)
            if ($responseToken->failed()) {
                $status = $responseToken->status();
                throw new \Exception("[TSU_AUTH_FAIL] Gagal Otorisasi Client (Status: $status). Cek Client ID/Secret.");
            }

            $accessToken = $responseToken->json()['access_token'];

            if (!$accessToken) {
                throw new \Exception("[TSU_TOKEN_EMPTY] Respon token dari Homebase kosong.");
            }

            // TARIK DATA USER (Pakai Bearer Token)
            $apiUrl = $homebaseUrl . '/api/v1/users/sync';

            // Variable Counter
            $stats = [
                'processed' => 0,
                'updated'   => 0,
                'uptodate'  => 0,
                'failed'    => 0,
            ];

            // Chunking Process (Looping User Lokal)
            User::query()
                ->whereNotNull('email')
                ->chunk(50, function ($users) use ($apiUrl, $accessToken, $syncer, &$stats) {

                    // Ambil daftar email user
                    $emailList = $users->pluck('email')->toArray();

                    // Request Update  (POST)
                    try {
                        $response = Http::withoutVerifying()
                            ->withToken($accessToken)
                            ->withHeaders(['Accept' => 'application/json'])
                            ->timeout(30)
                            ->post($apiUrl, [
                                'emails' => $emailList
                            ]);

                        if ($response->successful()) {
                            $apiResult = $response->json();
                            $usersData = $apiResult['data'] ?? [];

                            // UPDATE DATA LOKAL
                            foreach ($usersData as $userData) {
                                try {
                                    // Call user sync service
                                    $result = $syncer->handle($userData, null, true);

                                    // Cek status affected
                                    $stats['processed']++;
                                    if ($result['affected'] === true) {
                                        $stats['updated']++;
                                    } else {
                                        $stats['uptodate']++;
                                    }
                                } catch (\Exception $e) {
                                    // Log error per user
                                    $stats['failed']++;
                                    Log::error("[TSU_USER_SKIP] Gagal proses user: " . ($userData['email'] ?? 'Unknown'), [
                                        'error_msg' => $e->getMessage(),
                                        'file' => $e->getFile(),
                                        'line' => $e->getLine()
                                    ]);
                                }
                            }
                        } else {
                            // Log error request batch API
                            $stats['failed'] += count($emailList);
                            Log::error("[TSU_BATCH_API_ERR] Gagal Sync Batch: ", [
                                'status_code' => $response->status(),
                                'response_body' => $response->body(),
                                'target_emails' => $emailList
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Log koneksi error request batch
                        $stats['failed'] += count($emailList);
                        Log::error("[TSU_BATCH_CONN_ERR] Koneksi Error Saat Sync Batch: ", [
                            'error' => $e->getMessage(),
                            'emails' => $emailList
                        ]);
                    }

                    return true;
                });

            // Error Total
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
            // LOG ERROR (Global)
            $rawMessage = $e->getMessage();
            $errorCode  = "[TSU_SYS_CRITICAL]";
            $userMsg    = "Terjadi kesalahan sistem yang tidak terduga.";

            // Cek throw error message
            if (preg_match('/\[TSU_.*?\]/', $rawMessage, $matches)) {
                $errorCode = $matches[0];
                $userMsg = str_replace($errorCode, '', $rawMessage);
            } else {
                $userMsg = "Terjadi gangguan teknis internal.";
            }

            Log::error("$errorCode Gagal Sync User.", [
                'original_error' => $rawMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $finalErrorMsg = "<div class='text-center'>";
            $finalErrorMsg .= "<h4 class='text-bold text-danger mb-2'>$errorCode</h4>";
            $finalErrorMsg .= "<p class='mb-2 text-bold' style='font-size: 1.1em;'>$userMsg</p>";
            $finalErrorMsg .= "<p class='text-muted small mb-0'>Silakan screenshot pesan ini dan laporkan ke PIKDI jika masalah berlanjut.</p>";
            $finalErrorMsg .= "</div>";

            return redirect()->back()->with('error', $finalErrorMsg);
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'system:user');

        $title = 'Edit User';
        $user = User::with(['roles', 'dosenTendik', 'mahasiswa'])->findOrFail($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name')->toArray();

        // Cek apakah User SSO?
        $isSso = !is_null($user->sso_id);

        $formConfig = [];

        if ($user->hasRole('mahasiswa')) {
            $formConfig = DataMahasiswa::getFormConfig();
        } elseif ($user->hasRole(['dosen', 'tendik'])) {
            $formConfig = DataDosenTendik::getFormConfig();
        }

        return view('system::user.edit', compact('title', 'user', 'roles', 'userRole', 'isSso', 'formConfig'));
    }

    public function update(Request $request, $id)
    {
        // Gunakan findOrFail biar aman
        $user = User::findOrFail($id);

        // 1. VALIDASI: CUKUP ROLES (Data Akun di-skip)
        // Kita tidak perlu validasi name/email karena tidak akan di-update.
        $rules = [
            'roles' => 'required|array',
        ];

        // Validasi Profil Tambahan (Tetap Ada)
        // Ingat logic validasi dinamis kita sebelumnya? Masih kita pakai.
        if ($user->hasRole('mahasiswa')) {
            $rules = array_merge($rules, [
                'nik_ktp' => 'numeric|nullable',
                'tempat_lahir' => 'string|nullable',
                'tgl_lahir' => 'date|nullable',
                'jenis_kelamin' => 'string|nullable',
                'agama' => 'string|nullable',
                'no_hp' => 'numeric|nullable',
                'email_pribadi' => 'string|nullable',
                'nama_ayah' => 'string|nullable',
                'nama_ibu' => 'string|nullable',
                'no_hp_ortu' => 'numeric|nullable',
                'alamat_lengkap' => 'string|nullable',
            ]);
        } elseif ($user->hasRole(['dosen', 'tendik'])) {
            $rules = array_merge($rules, [
                'nidn' => 'string|max:20|nullable',
                'nip' => 'string|max:20|nullable',
                'gelar_depan' => 'string|nullable',
                'gelar_belakang' => 'string|nullable',
                'jabatan_fungsional' => 'string|nullable',
                'nik_ktp'  => 'string|nullable',
                'tempat_lahir'  => 'string|nullable',
                'tgl_lahir'  => 'date|nullable',
                'jenis_kelamin'  => 'string|nullable',
                'no_hp' => 'numeric|nullable',
                'alamat_domisili' => 'string|nullable',
            ]);
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            // Update Status Aktif
//            if (auth()->id() !== $user->id) {
//                $user->isactive = $request->has('isactive');
//                $user->save();
//            }

            // Update Roles
            $user->syncRoles($request->roles);

            // Update Profil (MULTI-PROFIL LOGIC)
            if ($user->hasRole(['dosen', 'tendik'])) {
                DataDosenTendik::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nidn' => $request->nidn,
                        'nip'  => $request->nip,
                        'gelar_depan' => $request->gelar_depan,
                        'gelar_belakang' => $request->gelar_belakang,
                        'jabatan_fungsional' => $request->jabatan_fungsional,
                        'nik_ktp' => $request->nik_ktp,
                        'tempat_lahir' => $request->tempat_lahir,
                        'tgl_lahir' => $request->tgl_lahir,
                        'jenis_kelamin' => $request->jenis_kelamin,
                        'no_hp' => $request->no_hp,
                        'alamat_domisili' => $request->alamat_domisili,
                    ]
                );
            } elseif ($user->hasRole('mahasiswa')) {
                DataMahasiswa::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nim' => $request->nim,
                        'nik_ktp' => $request->nik_ktp,
                        'tempat_lahir' => $request->tempat_lahir,
                        'tgl_lahir' => $request->tgl_lahir,
                        'jenis_kelamin' => $request->jenis_kelamin,
                        'agama' => $request->agama,
                        'no_hp' => $request->no_hp,
                        'email_pribadi' => $request->email_pribadi,
                        'nama_ayah' => $request->nama_ayah,
                        'nama_ibu' => $request->nama_ibu,
                        'no_hp_ortu' => $request->no_hp_ortu,
                        'alamat_lengkap' => $request->alamat_lengkap,
                    ]
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Hak Akses & Data Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log update profile failed
            $rawMessage = $e->getMessage();
            $errorCode  = "[TSU_UPD_FAIL]";
            $userMsg    = "Gagal menyimpan perubahan data.";

            if (preg_match('/\[TSU_.*?\]/', $rawMessage, $matches)) {
                $errorCode = $matches[0];
                $userMsg = trim(str_replace($errorCode, '', $rawMessage));
            } else {
                $userMsg = "Terjadi gangguan teknis internal saat menyimpan data.";
            }

            // Log Asli
            Log::error("$errorCode Gagal Update User ID: $id", [
                'original_error' => $rawMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // HTML Formatted Message
            $finalErrorMsg = "<div class='text-center'>";
            $finalErrorMsg .= "<h4 class='text-bold text-danger mb-2'>$errorCode</h4>";
            $finalErrorMsg .= "<p class='mb-2 text-bold' style='font-size: 1.1em;'>$userMsg</p>";
            $finalErrorMsg .= "<p class='text-muted small mb-0'>Silakan screenshot pesan ini dan laporkan ke PIKDI jika masalah berlanjut.</p>";
            $finalErrorMsg .= "</div>";

            return redirect()->back()->with('error', $finalErrorMsg);
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
