<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="{{ asset('public/assetsku/img/logotsu.png') }}" alt="TSU Logo" class="brand-image"
             style="opacity: .8">
        <span class="brand-text font-weight-light" style="font-size: 18px;font-weight: bold;">Tiga Serangkai University</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <img src="{{ Auth::user()->profile_photo_url }}"
                     class="img-circle elevation-2"
                     style="width: 2.1rem; height: 2.1rem; object-fit: cover;"
                     alt="User Image">
            </div>
            <div class="info w-100 overflow-hidden">
                <a href="javascript:void(0)" class="d-block text-truncate">{{ Auth::user()->name }}</a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column text-sm" data-widget="treeview" role="menu" data-accordion="false">

                {{-- Label Header --}}
                <li class="nav-header">Main Navigation</li>

                {{-- Panggil Component Logic Database Kita --}}
                <x-layouts.sidebar />

            </ul>
        </nav>
    </div>
</aside>
