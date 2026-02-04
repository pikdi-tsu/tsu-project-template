<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
            ->rawColumns(['permissions_count', 'action'])
            ->make(true);
    }

    public function sync()
    {
        $this->guard('create', 'system:role');

        try {
            // PERSIAPAN DATA KUNCI
            $baseUrl = config('app.tsu_homebase.url');
            $clientId = config('app.oauth.client.id');
            $clientSecret = config('app.oauth.client.secret');

            // Tembak ke route Passport: /oauth/token
            // Hapus without verifying saat production atau deploy
            $tokenResponse = Http::withoutVerifying()->asForm()->post($baseUrl . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => '', // Kosongkan jika tidak pakai scope spesifik
            ]);


            if ($tokenResponse->failed()) {
                return back()->with('error', 'Gagal Otorisasi ke Homebase! Cek Client ID/Secret.');
            }

            $accessToken = $tokenResponse->json()['access_token'];

            // Tembak route API Homebase: /api/v1/roles/sync-list
            // Hapus without verifying saat production atau deploy
            $dataResponse = Http::withoutVerifying()->withToken($accessToken)->get($baseUrl . '/api/v1/roles/sync-list');

            if ($dataResponse->failed()) {
                return back()->with('error', 'Gagal mengambil data Role. Status: ' . $dataResponse->status());
            }

            // PROSES DATA
            $rolesFromHomebase = $dataResponse->json()['data'];

            if (empty($rolesFromHomebase) || count($rolesFromHomebase) < 1) {
                return back()->with('error', 'Security Alert: Data Role dari Homebase Kosong! Sinkronisasi dibatalkan untuk mencegah penghapusan data.');
            }

            DB::beginTransaction();
            // Preservation local roles
            $moduleName = strtolower(config('app.module.name', 'template'));
            $protectedRoles = [
                "super admin {$moduleName}",
                "admin {$moduleName}"
            ];

            $addedCount = 0;
            foreach($rolesFromHomebase as $roleName) {
                $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                if($role->wasRecentlyCreated){
                    $addedCount++;
                }
            }

            $deletedCount = Role::query()
                ->where('guard_name', 'web')
                ->whereNotIn('name', $rolesFromHomebase)
                ->whereNotIn('name', $protectedRoles)
                ->delete();

            DB::commit();

            $msg = "Sinkronisasi Selesai!<br>";
            if($addedCount > 0) $msg .= "<b>+$addedCount</b> Role baru ditambahkan.<br>";
            if($deletedCount > 0) $msg .= "<b>-$deletedCount</b> Unknown Role dihapus.<br>";
            if($addedCount === 0 && $deletedCount === 0) $msg .= "Data Role sudah up-to-date.";
            $msg .= "<br><small class='text-white'><i>Protected Local Roles: " . implode(', ', $protectedRoles) . "</i></small>";

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal Sync: ' . $e->getMessage());
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
