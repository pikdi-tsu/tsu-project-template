{{--
    FILE: Modules/System/Resources/views/layouts/sidebar-item.blade.php
    Merender menu secara berulang (Recursive)
--}}

@php
    // CEK PERMISSION
    if ($menu->permission_name && !auth()->user()->can($menu->permission_name)) {
        return;
    }

    // Filter Children
    $visibleChildren = $menu->children->filter(function ($child) {
        return empty($child->permission_name) || auth()->user()->can($child->permission_name);
    });

    $hasChildren = $visibleChildren->isNotEmpty();

    // Cek Status Aktif
    $isActive = $menu->isActive();

    // Hirarki
    $currentLevel = isset($level) ? $level : 0;
    $paddingLeft  = 0.8 + ($currentLevel * 1.0);

    // Menu indikator
    $indicator = $hasChildren ? 'fas fa-chevron-right' : 'fas fa-minus';

    // Default Icon Menu
    $mainIcon = $menu->icon ?: 'fas fa-box';

    // Default-nya href
    $href = '#';

    if (! $hasChildren && Route::has($menu->route)) {
        $href = route($menu->route);
    }
@endphp

<li class="nav-item {{ $hasChildren && $isActive ? 'menu-open' : '' }}">

    <a href="{{ $href }}"
       class="nav-link {{ $isActive ? 'active' : '' }}"
       style="padding-left: {{ $paddingLeft }}rem !important; display: flex; align-items: center;">
        <i class="nav-indicator {{ $indicator }} mr-2"></i>
        <i class="nav-icon {{ $mainIcon }} mr-2"></i>
        <p class="mb-0" style="flex: 1;">
            {{ $menu->name }}
        </p>
    </a>

    @if($hasChildren)
        <ul class="nav nav-treeview">
            @foreach ($visibleChildren as $child)
                @include('system::components.sidebar-item', ['menu' => $child, 'level' => $currentLevel + 1])
            @endforeach
        </ul>
    @endif
</li>
