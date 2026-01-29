{{--
    FILE: Modules/System/Resources/views/layouts/sidebar.blade.php
    Hanya memanggil menu level teratas (Root).
--}}

@foreach ($menus as $menu)
    {{-- Panggil partial recursive--}}
    @include('system::components.sidebar-item', ['menu' => $menu, 'level' => 0])
@endforeach
