<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Services\TsuErrorHandlerService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('system:role');
    }

    public function index()
    {
        return view('system::role.index', [
            'title' => 'Manajemen Hak Akses Role'
        ]);
    }

    // JSON Datatable
    public function datatable()
    {
        $data = Role::query()->withCount('permissions')->orderBy('name', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('permissions_count', function($row){
                $moduleName = 'super admin ' . config('app.module.name');
                if (in_array($row->name, ['super admin', $moduleName], true)) {
                    return '<span class="badge badge-success p-2 shadow-sm"><i class="fas fa-crown mr-1"></i> Full Access</span>';
                }
                return '<span class="badge badge-info">'.$row->permissions_count.' Izin</span>';
            })
            ->addColumn('is_identity_badge', function ($row) {
                // Cek Global Role (Homebase)
                if ($row->is_identity) {
                    return '<span class="badge badge-info p-2"><i class="fas fa-globe"></i> Global (Homebase)</span>';
                }

                // Cek Lokal Inti
                $isCore = Str::contains($row->name, 'admin')
                    || in_array($row->name, ['dosen', 'tendik', 'mahasiswa']);

                $moduleName = ucfirst(config('app.module.name'));

                // Render Badge Lokal
                if ($isCore) {
                    return '<span class="badge badge-warning p-2 text-dark shadow-sm" title="Role Bawaan Sistem (Protected)">
                                <i class="fas fa-lock mr-1"></i> Lokal Inti '. $moduleName .'
                            </span>';
                }

                return '<span class="badge badge-secondary p-2" title="Role Buatan Sendiri">
                            <i class="fas fa-user-tag mr-1"></i> Lokal '. $moduleName .'
                        </span>';
            })
            ->filterColumn('is_identity_badge', function($query, $keyword) {
                $keyword = strtolower($keyword);
                $moduleName = strtolower(config('app.module.name'));

                // Filter Global
                if (str_contains($keyword, 'glob') || str_contains($keyword, 'home')) {
                    $query->where('is_identity', true);
                }
                // Filter Lokal Inti
                elseif (str_contains($keyword, 'inti') || str_contains($keyword, 'core') || str_contains($keyword, 'lock')) {
                    $query->where('is_identity', false)
                        ->where(function($q) {
                            $q->where('name', 'like', '%admin%')
                                ->orWhereIn('name', ['dosen', 'tendik', 'mahasiswa']);
                        });
                }
                // Filter Lokal Custom
                elseif (str_contains($keyword, 'custom') || str_contains($keyword, 'biasa')) {
                    $query->where('is_identity', false)
                        ->where(function($q) {
                            $q->where('name', 'not like', '%admin%')
                                ->whereNotIn('name', ['dosen', 'tendik', 'mahasiswa']);
                        });
                }
                // Filter Lokal Umum
                elseif (str_contains($keyword, 'lok') || str_contains($keyword, $moduleName)) {
                    $query->where('is_identity', false);
                }
            })
            ->addColumn('action', function ($row) {
                // Inisiasi protected roles
                $superAdminLokal = 'super admin ' . config('app.module.name');
                $adminLokal = 'admin ' . config('app.module.name');
                $isSuperAdmin = in_array($row->name, ['super admin', $superAdminLokal], true);
                $isProtectedRoles = in_array($row->name, ['super admin', $superAdminLokal, $adminLokal], true);

                // Cek Flag Database
                $isLocked = $isProtectedRoles  || $row->is_identity;

                if ($isSuperAdmin) {
                    return '<div class="text-center"><span class="badge badge-success"><i class="fas fa-crown"></i> Full Access</span></div>';
                }

                return $this->getActionButtons($row, 'system:role', [
                    'edit_url'   => route('system.role.edit', $row->id),
                    'use_modal'  => true,
                    'can_delete' => $isLocked ? false : null,
                    'delete_url' => route('system.role.destroy', $row->id),
                ]);
            })
            ->rawColumns(['permissions_count', 'is_identity_badge', 'action'])
            ->make(true);
    }

    public function sync()
    {
        $this->guard('create', 'system:role');

        try {
            $result = DB::transaction(function () {
                $baseUrl = config('app.tsu_homebase.url');
                $clientId = config('app.oauth.client.id');
                $clientSecret = config('app.oauth.client.secret');

                // Ambil Token (Client Credentials)
                $tokenResponse = Http::withoutVerifying()->withHeaders(['X-Sync-Secret' => config('app.pikdi.key.sync')])->asForm()->post($baseUrl . '/oauth/token', [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'scope'         => '',
                ]);

                if ($tokenResponse->failed()) {
                    throw new \Exception('[TSU_AUTH_FAIL] Gagal Otorisasi ke Homebase! Cek Client ID/Secret.');
                }

                $accessToken = $tokenResponse->json()['access_token'];

                if (!$accessToken) {
                    throw new \Exception("[TSU_TOKEN_EMPTY] Respon token dari Homebase kosong.");
                }

                // Ambil Data Role
                $dataResponse = Http::withoutVerifying()
                    ->withHeaders(['X-Sync-Secret' => config('app.pikdi.key.sync')])
                    ->withToken($accessToken)
                    ->timeout(10) // Jangan lama-lama nunggu
                    ->get($baseUrl . '/api/v1/roles/sync-list');

                if ($dataResponse->failed()) {
                    throw new \Exception("[TSU_API_ERR] Gagal mengambil data Role. Status: " . $dataResponse->status());
                }

                $rolesFromHomebase = $dataResponse->json()['data'];

                if (empty($rolesFromHomebase) || !is_array($rolesFromHomebase)) {
                    throw new \Exception("[TSU_DATA_INVALID] Data dari Homebase Kosong atau Format Salah!");
                }

                $addedCount = 0;
                $validGlobalRoles = [];

                foreach ($rolesFromHomebase as $item) {
                    $rName = is_array($item) ? ($item['name'] ?? null) : $item;
                    $isIdentity = is_array($item) && (($item['is_identity'] ?? false));

                    if ($rName) {
                        $nameLower = strtolower($rName);
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

                $deletedCount = Role::query()
                    ->where('guard_name', 'web')
                    ->where('is_identity', true)
                    ->whereNotIn('name', $validGlobalRoles)
                    ->delete();

                return ['added' => $addedCount, 'deleted' => $deletedCount];
            });

            // Notif Settings
            $msg = "<h6 class='font-weight-bold mb-2'>Sinkronisasi Roles Selesai!</h6>";
            $msg .= "<ul class='mb-0 pl-3' style='list-style-type: disc;'>";
            if ($result['added'] > 0) {
                $msg .= "<li><b>+{$result['added']}</b> Role Global Baru ditambahkan.</li>";
            }
            if ($result['deleted'] > 0) {
                $msg .= "<li><b>-{$result['deleted']}</b> Role Global Usang dihapus.</li>";
            }
            if ($result['added'] === 0 && $result['deleted'] === 0) {
                $msg .= "<li>Data Role Global sudah <b>Up-to-Date</b>.</li>";
            }
            $msg .= "</ul>";

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            $defaultError = 'Terjadi kesalahan sistem saat sinkronisasi Role.';
            if ($e instanceof ConnectionException) {
                $defaultError = 'Gagal menghubungi Server Homebase. Cek koneksi internet.';
            }
            return TsuErrorHandlerService::handleHtml($e, '[TSU_ROLE_CRITICAL]', $defaultError, 'Gagal Sync Role.');
        }
    }

    public function create()
    {
        $this->guard('create', 'system:role');

        // Get Permission Lokal
        $permissions = Permission::query()->orderBy('name')->get();

        // Grouping Permission
        $groupedPermissions = $permissions->groupBy(function($item){
            $parts = explode(':', $item->name);
            return count($parts) > 1 ? ucfirst($parts[1]) : 'Umum';
        });

        return view('system::role.create_modal', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'system:role');

        $request->validate([
            'name'        => 'required|string|max:50|unique:'.config('app.table.roles').',name',
            'permissions' => 'array'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $role = Role::create([
                    'name'        => strtolower($request->name),
                    'guard_name'  => 'web',
                    'is_identity' => false
                ]);

                $permissions = $request->permissions ?? [];
                $role->syncPermissions($permissions);

                app()[PermissionRegistrar::class]->forgetCachedPermissions();
            });

            return back()->with('success', 'Role lokal baru berhasil dibuat!');

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_ROLE_STORE_FAIL]', 'Gagal menyimpan role baru.', 'Gagal Create Role.', $request);
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'system:role');

        $role = Role::query()->findOrFail($id);

        $permissions = Permission::query()->orderBy('name')->get();

        // Grouping Permission (Format: siakad:krs:input -> Grup 'Siakad')
        $groupedPermissions = $permissions->groupBy(function($item){
            $parts = explode(':', $item->name);
            return count($parts) > 1 ? ucfirst($parts[1]) : 'Umum';
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('system::role.edit_modal', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'system:role');

        $role = Role::query()->findOrFail($id);
        $isLocked = $role->is_identity;

        $rules = ['permissions' => 'array'];
        if (!$isLocked) {
            $rules['name'] = 'required|string|max:50|unique:'.config('app.table.roles').',name,' . $id;
        }
        $request->validate($rules);

        try {
            DB::transaction(function () use ($request, $role, $isLocked) {
                if (!$isLocked) {
                    $role->name = strtolower($request->name);
                    $role->save();
                }

                $role->syncPermissions($request->permissions ?? []);
                app()[PermissionRegistrar::class]->forgetCachedPermissions();
            });

            return back()->with('success', 'Role berhasil diperbarui!');

        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_ROLE_UPD_FAIL]', 'Gagal menyimpan perubahan role.', "Gagal Update Role ID: $id.", $request);
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'system:role');

        try {
            $role = Role::findOrFail($id);

            if (in_array($role->name, ['super admin', 'super admin ' . config('app.module.name')], true)) {
                return back()->with('error', '[SYS_ERR] Super Admin tidak boleh dihapus.');
            }

            if ($role->name === 'admin ' . config('app.module.name')) {
                return back()->with('error', '[TSU_PROTECTED] Role admin lokal tidak dapat dihapus dari sistem lokal.');
            }

            if ($role->is_identity) {
                return back()->with('error', '[TSU_PROTECTED] Role Global (Homebase) tidak dapat dihapus dari sistem lokal.');
            }

            $role->delete();
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return back()->with('success', 'Role Lokal berhasil dihapus.');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_ROLE_DELETE_FAIL]', 'Gagal menghapus role.', "Gagal Hapus Role ID: $id.");
        }
    }
}
