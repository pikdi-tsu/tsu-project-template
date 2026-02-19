<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Http;
use DB;

class RoleController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('system:role');
    }

    public function index()
    {
        return view('system::role.index', [
            'title' => 'Manajemen Hak Akses Role (Matrix)'
        ]);
    }

    // JSON Datatable
    public function datatable()
    {
        // Ambil Role dari Database LOKAL Siakad
        $data = Role::query()->withCount('permissions')->orderBy('name', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('permissions_count', function($row){
                $moduleName = 'super admin ' . config('app.module.name');
                $superAdminRoles = ['super admin', $moduleName];

                if (in_array($row->name, $superAdminRoles, true)) {
                    // Tampilkan Badge "Full Access"
                    return '<span class="badge badge-success p-2 shadow-sm" style="cursor: default;" title="Role ini memiliki akses mutlak">
                                <i class="fas fa-crown mr-1"></i> Full Access
                            </span>';
                }

                return '<span class="badge badge-info">'.$row->permissions_count.' Izin</span>';
            })
            ->addColumn('is_identity_badge', function ($row) {
                $moduleName = ucfirst(config('app.module.name'));

                if ($row->is_identity) {
                    return '<span class="badge badge-info"><i class="fas fa-globe"></i> Global (Homebase)</span>';
                }

                return '<span class="badge badge-secondary"><i class="fas fa-building"></i> Lokal '. $moduleName .'</span>';
            })
            ->filterColumn('is_identity_badge', function($query, $keyword) {
                $keyword = strtolower($keyword);
                $moduleName = ucfirst(config('app.module.name'));

                if (str_contains($keyword, 'glob') || str_contains($keyword, 'home')) {
                    $query->where('is_identity', true);
                }
                elseif (str_contains($keyword, 'lok') || str_contains($keyword, $moduleName)) {
                    $query->where('is_identity', false);
                }
            })
            ->addColumn('action', function ($row) {
                $moduleName = 'super admin ' . config('app.module.name');
                $superAdminRoles = ['super admin', $moduleName];

                if (in_array($row->name, $superAdminRoles, true)) {
                    // Tampilkan Badge "Full Access" (Tanpa Tombol)
                    return '<span class="badge badge-success p-2 shadow-sm" style="cursor: default;" title="Role ini memiliki akses mutlak">
                                <i class="fas fa-crown mr-1"></i> Full Access
                            </span>';
                }


                if (auth()->user()->can('system:role:edit')) {
                    $btn = '<button type="button"
                                href="'.route('system.role.edit', $row->id).'"
                                class="btn btn-warning btn-sm btn-edit"
                                title="Atur Permission">
                                <i class="fas fa-cogs"></i> Atur Akses
                            </button>';
                } else {
                    $btn = '<span class="badge badge-secondary p-2 shadow-sm" style="cursor: default;" title="Anda tidak memiliki akses ke action ini">
                                <i class="fas fa-lock"></i> Atur Akses (No Access)
                            </span>';
                }

                return $btn ?? null;
            })
            ->rawColumns(['permissions_count', 'is_identity_badge', 'action'])
            ->make(true);
    }

    public function sync()
    {
        // Cek permission user
        $this->guard('create', 'system:role');

        try {
            $baseUrl = config('app.tsu_homebase.url');
            $clientId = config('app.oauth.client.id');
            $clientSecret = config('app.oauth.client.secret');

            // Ambil Token (Client Credentials)
            try {
                $tokenResponse = Http::withoutVerifying()->asForm()->post($baseUrl . '/oauth/token', [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'scope'         => '',
                ]);
            } catch (ConnectionException $e) {
                throw new \Exception("[TSU_CONN_REFUSED] Gagal menghubungi Server Homebase. Cek koneksi internet.");
            }

            if ($tokenResponse->failed()) {
                return back()->with('error', '[TSU_AUTH_FAIL] Gagal Otorisasi ke Homebase! Cek Client ID/Secret.');
            }

            $accessToken = $tokenResponse->json()['access_token'];

            if (!$accessToken) {
                throw new \Exception("[TSU_TOKEN_EMPTY] Respon token dari Homebase kosong.");
            }

            // Ambil Data Role
            try {
                $dataResponse = Http::withoutVerifying()
                    ->withToken($accessToken)
                    ->timeout(10) // Jangan lama-lama nunggu
                    ->get($baseUrl . '/api/v1/roles/sync-list');
            } catch (ConnectionException $e) {
                throw new \Exception("[TSU_API_TIMEOUT] Koneksi terputus saat mengambil data Role.");
            }

            if ($dataResponse->failed()) {
                throw new \Exception("[TSU_API_ERR] Gagal mengambil data Role. Status: " . $dataResponse->status());
            }

            // Proses dataa filterr️
            $rolesFromHomebase = $dataResponse->json()['data'];


            // Validasi payload kosong
            if (empty($rolesFromHomebase) || !is_array($rolesFromHomebase)) {
                // Kita anggap error karena tidak mungkin Homebase tidak punya role sama sekali
                throw new \Exception("[TSU_DATA_INVALID] Data dari Homebase Kosong atau Format Salah!");
            }

            DB::beginTransaction();

            $addedCount = 0;
            $validGlobalRoles = [];

            foreach ($rolesFromHomebase as $item) {
                // Deteksi format (Array Object atau String biasa)
                $rName = is_array($item) ? ($item['name'] ?? null) : $item;
                $isIdentity = is_array($item) && (($item['is_identity'] ?? false));

                if ($rName) {
                    $nameLower = strtolower($rName);

                    // Filter Role Identitas
                    if ($isIdentity) {
                        $validGlobalRoles[] = $nameLower;

                        $role = Role::updateOrCreate(
                            ['name' => $nameLower, 'guard_name' => 'web'],
                            ['is_identity' => true]
                        );

                        if ($role->wasRecentlyCreated) {
                            $addedCount++;
                        }
                    }
                }
            }

            // Logic cleanup role lama dari Homebase dan protect role lokal
            $deletedCount = Role::query()
                ->where('guard_name', 'web')
                ->where('is_identity', true)
                ->whereNotIn('name', $validGlobalRoles)
                ->delete();

            DB::commit();

            // Notif Settings
            $msg = "<h6 class='font-weight-bold mb-2'>Sinkronisasi Roles Selesai!</h6>";
            $msg .= "<ul class='mb-0 pl-3' style='list-style-type: disc;'>";

            if ($addedCount > 0) {
                $msg .= "<li><b>+$addedCount</b> Role Global Baru ditambahkan.</li>";
            }

            if ($deletedCount > 0) {
                $msg .= "<li><b>-$deletedCount</b> Role Global Usang dihapus.</li>";
            }

            if ($addedCount === 0 && $deletedCount === 0) {
                $msg .= "<li>Data Role Global sudah <b>Up-to-Date</b>.</li>";
            }
            $msg .= "</ul>";

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log gagal sync role
            $rawMessage = $e->getMessage();
            $errorCode  = "[TSU_ROLE_CRITICAL]"; // Default Code
            $userMsg    = "Terjadi kesalahan sistem saat sinkronisasi Role.";

            if (preg_match('/\[TSU_.*?\]/', $rawMessage, $matches)) {
                $errorCode = $matches[0];
                $userMsg = str_replace($errorCode, '', $rawMessage);
            } else {
                // Masking Error Codingan Asli
                $userMsg = "Terjadi gangguan teknis internal.";
            }

            Log::error("$errorCode Gagal Sync Role.", [
                'original_error' => $rawMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Error koneksi internet/server mati
            if ($e instanceof \Illuminate\Http\Client\ConnectionException) {
                return back()->with('error', 'Gagal menghubungi Server Homebase. Kemungkinan server pusat sedang down atau gangguan jaringan.');
            }

            // Feedback User
            $finalErrorMsg = "<div class='text-center'>";
            $finalErrorMsg .= "<h4 class='text-bold text-danger mb-2'>$errorCode</h4>";
            $finalErrorMsg .= "<p class='mb-2 text-bold' style='font-size: 1.1em;'>$userMsg</p>";
            $finalErrorMsg .= "<p class='text-muted small mb-0'>Silakan screenshot pesan ini dan laporkan ke PIKDI jika masalah berlanjut.</p>";
            $finalErrorMsg .= "</div>";

            // Error Default
            return back()->with('error', $finalErrorMsg);
        }
    }

    // Modal Edit Permission
    public function edit($id)
    {
        $this->guard('edit', 'system:role');

        $role = Role::query()->findOrFail($id);

        // Ambil SEMUA Permission LOKAL Siakad
        $permissions = Permission::query()->orderBy('name')->get();

        // Grouping Permission (Format: siakad:krs:input -> Grup 'Siakad')
        $groupedPermissions = $permissions->groupBy(function($item){
            $parts = explode(':', $item->name);
            // Ambil kata kedua sebagai sub-group jika formatnya siakad:fitur:aksi
            // Atau ambil kata pertama kalau mau simple
            return count($parts) > 1 ? ucfirst($parts[1]) : 'Umum';
        });

        // Ambil permission yang SUDAH dimiliki role ini
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('system::role.edit_modal', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    // Simpan Perubahan Permission
    public function update(Request $request, $id)
    {
        $this->guard('edit', 'system:role');

        $role = Role::query()->findOrFail($id);

        // Validasi: Nama role Read Only
        $permissions = $request->permissions ?? [];
        $role->syncPermissions($permissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', 'Hak akses role berhasil diperbarui!');
    }
}
