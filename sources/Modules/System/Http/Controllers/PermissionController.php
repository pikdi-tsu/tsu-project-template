<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
                $canEdit   = auth()->user()->can('system:permission:edit');
                $canDelete = auth()->user()->can('system:permission:delete');

                if (!$canEdit && !$canDelete) {
                    return '<span class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed;" title="Anda tidak memiliki akses ke action ini">
                                <i class="fas fa-lock mr-1"></i> Locked
                            </span>';
                }

                // Edit action
                if ($canEdit) {
                    $btnEdit = '<button type="button" class="btn btn-xs btn-warning btn-edit" data-id="'.$row->id.'" data-name="'.$row->name.'" title="Edit"><i class="fas fa-edit"></i></button>';
                } else {
                    $btnEdit = '<span class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.6;" title="Edit (No Access)">
                                    <i class="fas fa-lock"></i>
                                 </span>';
                }

                // Delete action
                if ($canDelete) {
                    $btnDel  = '<form action="'.route('system.permission.destroy', $row->id).'" method="POST" style="display:inline;">
                                    '.csrf_field().' '.method_field('DELETE').'
                                    <button type="button" class="btn btn-xs btn-danger btn-delete" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>';
                } else {
                    $btnDel = '<span class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.6;" title="No Access: Delete">
                                    <i class="fas fa-lock"></i>
                                </span>';
                }

                return $btnEdit . ' ' . $btnDel;
            })
            ->rawColumns(['guard_name', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $tablePermission = config('auth.providers.users.table');

        $request->validate([
            'name' => ['required', Rule::unique($tablePermission . '_permissions', 'name')->where('guard_name', 'web')]
        ]);

        Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return back()->with('success', 'Permission baru berhasil dibuat!');
    }

    public function edit($id)
    {
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
        $permission = Permission::query()->findOrFail($id);
        $tablePermission = config('auth.providers.users.table');

        $request->validate([
            'name' => ['required', Rule::unique($tablePermission . '_permissions', 'name')->ignore($id)->where('guard_name', 'web')]
        ]);

        $permission->update(['name' => $request->name]);

        return back()->with('success', 'Nama Permission berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $permission = Permission::query()->findOrFail($id);
        $permission->delete();

        return back()->with('success', 'Permission berhasil dihapus!');
    }
}
