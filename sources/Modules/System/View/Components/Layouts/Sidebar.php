<?php

namespace Modules\System\View\Components\Layouts; // <--- Namespace Module

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;
use Modules\System\Models\MenuSidebar;

// Pastikan ini mengarah ke Model Menu yang kita buat tadi

class Sidebar extends Component
{
    public $menus;

    public function __construct()
    {
        // Logic ambil menu dari Database
        // Kita eager load 'children' biar query-nya efisien
        $this->menus = MenuSidebar::query()->whereNull('parent_id')
            ->where('isactive', 1)
            ->with(['children' => function ($query) {
                $query->where('isactive', 1)->orderBy('order');
            }])
            ->orderBy('order')
            ->get();
    }

    public function render()
    {
        // Arahkan ke View yang ada di dalam Module System
        // Format: 'nama-module::path.to.view'
        return view('system::components.layouts.sidebar');
    }
}
