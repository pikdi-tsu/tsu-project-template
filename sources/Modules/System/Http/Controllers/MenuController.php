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
            ->with('parent') // Eager load biar performa kenceng
            ->orderBy('parent_id', 'asc') // Kelompokkan berdasarkan induk
            ->orderBy('order', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()

            // ✨ UPGRADE 1: Tampilan Nama Menu (Hierarki)
            ->editColumn('name', function ($d) {
                if ($d->parent_id) {
                    // Kalau dia Submenu (Punya Bapak), kasih indentasi & icon panah kecil
                    return '<span class="pl-3 text-muted"><i class="fas fa-angle-right mr-1"></i> ' . $d->name . '</span> <small class="text-xs text-gray">(' . $d->parent->name . ')</small>';
                }
                // Kalau Menu Utama, tebalkan
                return '<b>' . $d->name . '</b>';
            })

            // ✨ UPGRADE 2: Tampilan Route
            ->editColumn('route', function($d) {
                // Kalau route kosong (biasanya parent), kasih strip
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

            ->addColumn('action', function ($d) {
                // 🔴 CRITICAL FIX: Tambahkan class 'btn-edit'
                $btnEdit = '<a href="'.route('system.menu.edit', $d->id).'" class="btn btn-xs btn-warning btn-edit" title="Edit Menu"><i class="fas fa-edit"></i></a>';

                $btnDel = '<form action="'.route('system.menu.destroy', $d->id).'" method="POST" style="display:inline;">
                                '.csrf_field().' '.method_field('DELETE').'
                                <button type="submit" class="btn btn-xs btn-danger btn-delete" title="Hapus Menu">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>';
                return $btnEdit . ' ' . $btnDel;
            })

            // Jangan lupa daftarkan kolom yang mengandung HTML
            ->rawColumns(['name', 'route', 'permission', 'status', 'action'])
            ->make(true);
    }

//    public function create()
//    {
//        $permissions = Permission::query()->orderBy('name')->pluck('name', 'name');
//        $parents = MenuSidebar::query()->whereNull('parent_id')->orderBy('order')->pluck('name', 'id');
//
//        return view('system::menu.create', compact('permissions', 'parents'));
//    }

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
        return view('system::menu.edit-modal', compact('menu', 'permissions', 'parents'));
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
        $menu = MenuSidebar::query()->findOrFail($id);
        $menu->delete(); // Atau $menu->update(['is_active' => 0]) kalau mau soft delete

        return redirect()->route('system.menu.index')->with('success', 'Menu berhasil dihapus!');
    }
}
