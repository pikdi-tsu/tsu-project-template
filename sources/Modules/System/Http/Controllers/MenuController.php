<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\System\Models\MenuSidebar;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

// Import Spatie

class MenuController extends Controller
{
    public function index()
    {
        return view('system::menu.index', [
            'title' => 'Manajemen Menu Sidebar'
        ]);
    }

    public function datatable()
    {
        // Ambil data dari tabel system_menu_sidebars
        $data = MenuSidebar::query()->orderBy('order', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('permission', function ($d) {
                // Tampilkan Label Permission (Gembok)
                return $d->permission_name
                    ? '<span class="badge badge-info">'.$d->permission_name.'</span>'
                    : '<span class="badge badge-secondary">Public</span>';
            })
            ->addColumn('status', function ($d) {
                // Adaptasi kolom 'aktif' teman Mas
                $warna = $d->isactive ? 'success' : 'danger';
                $text  = $d->isactive ? 'Aktif' : 'Non-Aktif';
                return '<span class="badge badge-'.$warna.'">'.$text.'</span>';
            })
            ->addColumn('action', function ($d) {
                // Tombol Edit & Delete
                $btnEdit = '<a href="'.route('system.menu.edit', $d->id).'" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>';
                $btnDel  = '<form action="'.route('system.menu.destroy', $d->id).'" method="POST" style="display:inline;" onsubmit="return confirm(\'Yakin hapus menu ini?\')">
                                '.csrf_field().' '.method_field('DELETE').'
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>';
                return $btnEdit . ' ' . $btnDel;
            })
            ->rawColumns(['permission', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $permissions = Permission::query()->orderBy('name')->pluck('name', 'name');
        $parents = MenuSidebar::query()->whereNull('parent_id')->orderBy('order')->pluck('text', 'id');

        return view('system::menu.create', compact('permissions', 'parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required',
            'order' => 'required|integer',
            'permission_name' => 'nullable|exists:template_users_permissions,name' // Validasi ke tabel permission
        ]);

        MenuSidebar::query()->create([
            'text'            => $request->text,
            'icon'            => $request->icon,
            'route'           => $request->route,
            'permission_name' => $request->permission_name, // Ini pengganti 'Alias' atau logic GroupUser lama
            'parent_id'       => $request->parent_id,
            'order'           => $request->order,
            'isactive'       => $request->has('isactive') ? 1 : 0,
        ]);

        return redirect()->route('system.menu.index')->with('success', 'Menu berhasil dibuat!');
    }

    public function edit($id)
    {
        $menu = MenuSidebar::query()->findOrFail($id);
        $permissions = Permission::query()->orderBy('name')->pluck('name', 'name');
        $parents = MenuSidebar::query()->whereNull('parent_id')->where('id', '!=', $id)->pluck('text', 'id');

        return view('system::menu.edit', compact('menu', 'permissions', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $menu = MenuSidebar::query()->findOrFail($id);

        $menu->update([
            'text'            => $request->text,
            'icon'            => $request->icon,
            'route'           => $request->route,
            'permission_name' => $request->permission_name,
            'parent_id'       => $request->parent_id,
            'order'           => $request->order,
            'isactive'       => $request->has('isactive') ? 1 : 0,
        ]);

        return redirect()->route('system.menu.index')->with('success', 'Menu berhasil diupdate!');
    }

    public function destroy($id)
    {
        $menu = MenuSidebar::query()->findOrFail($id);
        $menu->delete(); // Atau $menu->update(['is_active' => 0]) kalau mau soft delete

        return redirect()->route('system.menu.index')->with('success', 'Menu berhasil dihapus!');
    }
}
