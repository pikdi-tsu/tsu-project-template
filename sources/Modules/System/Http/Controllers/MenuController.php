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
        $parents = $this->getHierarchicalParents();

        return view('system::menu.index', [
            'title' => 'Manajemen Menu Sidebar',
            'permissions' => $permissions,
            'parents' => $parents
        ]);
    }

    private function getHierarchicalParents($ignoreId = null)
    {
        $nodes = MenuSidebar::query()->whereNull('parent_id')->orderBy('order')->with('children')->get();
        $options = [];

        foreach ($nodes as $node) {
            if ($node->id === $ignoreId) {
                continue;
            }

            $options[$node->id] = $node->name;

            // Panggil fungsi rekursif child
            $this->recurseChildren($node, $options, $ignoreId, 1);
        }

        return $options;
    }

    private function recurseChildren($parent, &$options, $ignoreId, $depth)
    {
        foreach ($parent->children as $child) {
            if ($child->id === $ignoreId) {
                continue;
            }

            // Prefix menjorok (Contoh: "— — Nama Submenu")
            $prefix = str_repeat('— ', $depth);
            $options[$child->id] = $prefix . $child->name;

            // Cek children
            $this->recurseChildren($child, $options, $ignoreId, $depth + 1);
        }
    }

    public function datatable()
    {
        // Ambil data
        $allMenus = MenuSidebar::query()->orderBy('order', 'asc')->get();

        // Urutan hirarki (Bapak -> Anak -> Cucu)
        $sortedData = $this->sortTree($allMenus);

        return DataTables::of($sortedData)
            ->addIndexColumn()
            ->editColumn('name', function ($d) {
                // VISUALISASI HIERARKI (POHON)
                $padding = $d->depth * 25;
                $iconPohon = '';
                if ($d->depth > 0) {
                    $iconPohon = '<i class="fas fa-level-up-alt fa-rotate-90 text-gray mr-2" style="font-size: 0.8rem; margin-left: -15px;"></i>';
                }
                $colorClass = match ((int)$d->depth) {
                    0 => 'text-dark font-weight-bold',
                    1 => 'text-primary',
                    default => 'text-muted',
                };
                return '<div style="padding-left: '.$padding.'px;" class="'.$colorClass.'">' . $iconPohon . $d->name . '</div>';
            })
            ->editColumn('route', function($d) {
                return $d->route && $d->route !== '#'
                    ? '<code class="text-dark">'.$d->route.'</code>'
                    : '<span class="text-muted text-xs font-italic">Label / Group</span>';
            })
            ->addColumn('permission', function ($d) {
                return $d->permission_name
                    ? '<span class="badge badge-info"><i class="fas fa-lock text-xs mr-1"></i> '.$d->permission_name.'</span>'
                    : '<span class="badge badge-light border">Public</span>';
            })
            ->addColumn('status', function ($d) {
                $warna = $d->isactive ? 'success' : 'secondary';
                $text  = $d->isactive ? 'Aktif' : 'Non-Aktif';
                return '<span class="badge badge-'.$warna.'">'.$text.'</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit   = auth()->user()->can('system:menu:edit');
                $canDelete = auth()->user()->can('system:menu:delete');
                $isSystemCore = Str::contains($row->route, 'dashboard');

                if (!$canEdit && !$canDelete) {
                    return '';
                }

                $btnEdit = $canEdit ? '<a href="'.route('system.menu.edit', $row->id).'" class="btn btn-xs btn-warning btn-edit mr-1" title="Edit"><i class="fas fa-pencil-alt"></i></a>' : '';

                $btnDel = '';
                if ($canDelete) {
                    if ($isSystemCore) {
                        $btnDel = '<button class="btn btn-xs btn-secondary" disabled title="System Core"><i class="fas fa-lock"></i></button>';
                    } else {
                        $btnDel = '<form action="'.route('system.menu.destroy', $row->id).'" method="POST" style="display:inline;">
                                        '.csrf_field().' '.method_field('DELETE').'
                                        <button type="submit" class="btn btn-xs btn-danger btn-delete" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>';
                    }
                }
                return $btnEdit . $btnDel;
            })
            ->setRowClass(function ($d) {
                return $d->depth === 0 ? 'table-secondary' : '';
            })
            ->rawColumns(['name', 'route', 'permission', 'status', 'action'])
            ->make(true);
    }

    private function sortTree($menus, $parentId = null, $depth = 0)
    {
        $result = collect([]);
        $children = $menus->where('parent_id', $parentId)->sortBy('order');

        foreach ($children as $child) {
            // Depth object menu
            $child->setAttribute('depth', $depth);

            // Masukkan parent ke hasil
            $result->push($child);

            // Cari recursive children dengan depth + 1
            $result = $result->merge($this->sortTree($menus, $child->id, $depth + 1));
        }

        return $result;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'order' => 'required|integer',
        ]);

        MenuSidebar::query()->create([
            'name'            => $request->name,
            'icon'            => $request->icon ?: 'fas fa-box',
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

        // Ambil parent
        $parents = $this->getHierarchicalParents($id);

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
