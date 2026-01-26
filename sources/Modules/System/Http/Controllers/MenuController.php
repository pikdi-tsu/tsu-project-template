<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\System\Models\MenuSidebar;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

// Import Spatie

class MenuController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('system:menu');
    }

    public function index()
    {
        $permissions = Permission::query()->orderBy('name')->pluck('name', 'name');
        $parents = MenuSidebar::query()->whereNull('parent_id')->orderBy('order')->pluck('name', 'id');

        return view('system::menu.index', [
            'title' => 'Manajemen Menu Sidebar',
            'permissions' => $permissions,
            'parents' => $parents
        ]);
    }

    public function datatable()
    {
        // Ambil data dari tabel system_menu_sidebars
        $data = MenuSidebar::query()
            ->with('parent')
            ->orderBy('parent_id', 'asc')
            ->orderBy('order', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('name', function ($d) {
                if ($d->parent_id) {
                    return '<span class="pl-3 text-muted"><i class="fas fa-angle-right mr-1"></i> ' . $d->name . '</span> <small class="text-xs text-gray">(' . $d->parent->name . ')</small>';
                }
                return '<b>' . $d->name . '</b>';
            })
            ->editColumn('route', function($d) {
                return $d->route ? '<code class="text-primary">'.$d->route.'</code>' : '<span class="text-muted">-</span>';
            })
            ->addColumn('permission', function ($d) {
                return $d->permission_name
                    ? '<span class="badge badge-info"><i class="fas fa-lock text-xs"></i> '.$d->permission_name.'</span>'
                    : '<span class="badge badge-secondary">Public</span>';
            })
            ->addColumn('status', function ($d) {
                $warna = $d->isactive ? 'success' : 'danger';
                $text  = $d->isactive ? 'Aktif' : 'Non-Aktif';
                return '<span class="badge badge-'.$warna.'">'.$text.'</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('system:menu:edit');
                $canDelete = auth()->user()->can('system:menu:delete');
                $isSystemCore = Str::contains($row->route, 'dashboard');

                if (!$canEdit && !$canDelete) {
                    return '<span class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed;" title="Anda tidak memiliki akses ke action ini">
                                <i class="fas fa-lock mr-1"></i> Locked
                            </span>';
                }

                // Edit action
                if ($canEdit) {
                    $btnEdit = '<a href="'.route('system.menu.edit', $row->id).'" class="btn btn-xs btn-warning btn-edit" title="Edit Menu"><i class="fas fa-edit"></i></a>';
                } else {
                    $btnEdit = '<span class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.6;" title="Edit (No Access)">
                                    <i class="fas fa-lock"></i>
                                 </span>';
                }

                // Delete action
                if ($canDelete) {
                    if ($isSystemCore) {
                        $btnDel = '<span class="badge badge-info p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.8;" title="Protected: Menu Core">
                                    <i class="fas fa-shield-alt"></i>
                                 </span>';
                    } else {
                        $btnDel = '<form action="'.route('system.menu.destroy', $row->id).'" method="POST" style="display:inline;">
                                        '.csrf_field().' '.method_field('DELETE').'
                                        <button type="submit" class="btn btn-xs btn-danger btn-delete" title="Hapus Menu">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>';
                    }
                } else {
                    $btnDel = '<span class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.6;" title="No Access: Delete">
                                    <i class="fas fa-lock"></i>
                                </span>';
                }

                return $btnEdit . ' ' . $btnDel;
            })

            // Jangan lupa daftarkan kolom yang mengandung HTML
            ->rawColumns(['name', 'route', 'permission', 'status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'order' => 'required|integer',
        ]);

        MenuSidebar::query()->create([
            'name'            => $request->name,
            'icon'            => $request->icon,
            'route'           => $request->route,
            'permission_name' => $request->permission_name,
            'parent_id'       => $request->parent_id,
            'order'           => $request->order,
            'isactive'        => $request->has('isactive') ? 1 : 0,
        ]);

        // Redirect Back saja (halaman gak pindah, cuma reload)
        return back()->with('success', 'Menu berhasil dibuat!');
    }

    public function edit($id)
    {
        $menu = MenuSidebar::query()->findOrFail($id);
        $permissions = Permission::query()->orderBy('name')->pluck('name', 'name');

        // Ambil parent kecuali dirinya sendiri (biar gak infinite loop)
        $parents = MenuSidebar::query()
            ->whereNull('parent_id')
            ->where('id', '!=', $id)
            ->orderBy('order')
            ->pluck('name', 'id');

        // KITA RETURN VIEW PARTIAL (Khusus untuk dimuat di dalam Modal)
        return view('system::menu.edit_modal', compact('menu', 'permissions', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $menu = MenuSidebar::query()->findOrFail($id);

        $menu->update([
            'name'            => $request->name,
            'icon'            => $request->icon,
            'route'           => $request->route,
            'permission_name' => $request->permission_name,
            'parent_id'       => $request->parent_id,
            'order'           => $request->order,
            'isactive'        => $request->has('isactive') ? 1 : 0,
        ]);

        return back()->with('success', 'Menu berhasil diupdate!');
    }

    public function destroy($id)
    {
        $menu = MenuSidebar::withCount('children')->findOrFail($id);

        if ($menu->children_count > 0) {
            return redirect()->back()->with('error',
                '<b>Gagal Menghapus!</b>
                <br>Menu ini masih memiliki
                <b>'.$menu->children_count.' Sub-Menu</b>di dalamnya.
                <br>Silakan hapus atau pindahkan sub-menu terlebih dahulu.'
            );
        }

        if (!empty($menu->permission_name)) {
            // Cek Permission
            $permissionExists = Permission::query()->where('name', $menu->permission_name)->exists();

            if ($permissionExists) {
                $linkPermission = route('system.permission.index', ['search' => $menu->permission_name]);

                return redirect()->back()->with('error',
                    '<b>Gagal Menghapus!</b><br>'.
                    'Menu ini masih terikat dengan Permission: <b>'.$menu->permission_name.'</b>.<br><br>'.
                    'Demi keamanan data, Anda wajib menghapus data Permission-nya terlebih dahulu sebelum menghapus Menu ini.<br><br>'.
                    '<a href="'.$linkPermission.'" class="btn btn-danger btn-xs text-white shadow-sm">'.
                    '<i class="fas fa-arrow-right"></i> Hapus Permission Disini'.
                    '</a>'
                );
            }

            // Cek Penggunaan di Role (Active Usage)
            $permission = Permission::query()->where('name', $menu->permission_name)->first();

            if ($permission) {
                $usedByRoles = $permission->roles()->count();

                if ($usedByRoles > 0) {
                    return redirect()->back()->with('error',
                        '<b>Gagal Menghapus!</b><br>'.
                        'Permission menu ini (<b>'.$menu->permission_name.'</b>) sedang aktif digunakan oleh <b>'.$usedByRoles.' Role</b>.<br>'.
                        'Silakan uncheck/cabut akses permission ini dari Role terkait di Manajemen Role terlebih dahulu.'
                    );
                }
            }
        }

        $menu->delete();

        return redirect()->route('system.menu.index')->with('success', 'Menu berhasil dihapus!');
    }
}
