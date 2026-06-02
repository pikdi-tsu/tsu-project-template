<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Services\TsuErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('system:permission');
    }

    public function index()
    {
        return view('system::permission.index', ['title' => 'Manajemen Permission (Hak Akses)']);
    }

    public function datatable()
    {
        // Ambil permission lokal
        $data = Permission::query()->orderBy('created_at', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('guard_name', function($row){
                return '<span class="badge badge-secondary">'.$row->guard_name.'</span>';
            })
            ->addColumn('action', function ($row) {
                return $this->getActionButtons($row, 'system:permission', [
                    'delete_url' => route('system.permission.destroy', $row->id),
                ]);
            })
            ->rawColumns(['guard_name', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $this->guardStore($request->id, 'system:permission');

        $request->validate([
            'name' => ['required', Rule::unique(config('app.table.permissions'), 'name')->where('guard_name', 'web')]
        ]);

        try {
            Permission::create(['name' => $request->name, 'guard_name' => 'web']);
            return back()->with('success', 'Permission baru berhasil dibuat!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PERM_STORE_FAIL]', 'Gagal menyimpan permission baru.', 'Gagal Create Permission.', $request);
        }
    }

    public function edit($id)
    {
        $this->guard('edit', 'system:permission');

        $role = Role::query()->findOrFail($id);

        $permissions = Permission::query()->orderBy('name')->get();

        $groupedPermissions = $permissions->groupBy(function($item){
            $parts = explode(':', $item->name);
            return ucfirst($parts[0]);
        });

        // Ambil permission yang SUDAH dimiliki role ini (untuk auto-check)
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('system::role.edit_modal', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $this->guard('edit', 'system:permission');

        $request->validate([
            'name' => ['required', Rule::unique(config('app.table.permissions'), 'name')->ignore($id)->where('guard_name', 'web')]
        ]);

        try {
            $permission = Permission::query()->findOrFail($id);
            $permission->update(['name' => $request->name]);
            return back()->with('success', 'Nama Permission berhasil diperbarui!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PERM_UPDATE_FAIL]', 'Gagal memperbarui permission.', "Gagal Update Permission ID: $id.", $request);
        }
    }

    public function destroy($id)
    {
        $this->guard('delete', 'system:permission');

        try {
            $permission = Permission::query()->findOrFail($id);
            $permission->delete();
            return back()->with('success', 'Permission berhasil dihapus!');
        } catch (\Exception $e) {
            return TsuErrorHandlerService::handleHtml($e, '[TSU_PERM_DELETE_FAIL]', 'Gagal menghapus permission.', "Gagal Hapus Permission ID: $id.");
        }
    }
}
