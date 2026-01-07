@foreach ($menus as $menu)
    {{-- Cek Permission (Kalau gak punya akses, skip) --}}
    @if($menu->permission_name && !Auth::user()->can($menu->permission_name))
        @continue
    @endif

    {{-- Cek Submenu --}}
    @if ($menu->children->isEmpty())
        {{-- === MENU TUNGGAL === --}}
        <li class="nav-item">
            <a href="{{ Route::has($menu->route) ? route($menu->route) : '#' }}"
               class="nav-link {{ request()->routeIs($menu->route.'*') ? 'active' : '' }}">
                <i class="nav-icon {{ $menu->icon }}"></i>
                <p>{{ $menu->name }}</p>
            </a>
        </li>
    @else
        {{-- === MENU PARENT (Dropdown) === --}}
        @php
            $isActive = false;
            foreach($menu->children as $child) {
                if (request()->routeIs($child->route.'*')) {
                    $isActive = true;
                    break;
                }
            }
        @endphp
        <li class="nav-item {{ $isActive ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $isActive ? 'active' : '' }}">
                <i class="nav-icon {{ $menu->icon }}"></i>
                <p>
                    {{ $menu->name }}
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @foreach ($menu->children as $child)
                    {{-- Cek Permission Anak --}}
                    @if($child->permission_name && !Auth::user()->can($child->permission_name))
                        @continue
                    @endif
                    <li class="nav-item">
                        <a href="{{ Route::has($child->route) ? route($child->route) : '#' }}"
                           class="nav-link {{ request()->routeIs($child->route.'*') ? 'active' : '' }} pl-4">

                            <i class="{{ $child->icon ?? 'far fa-circle' }} nav-icon"></i>
                            <p>{{ $child->name }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    @endif
@endforeach
