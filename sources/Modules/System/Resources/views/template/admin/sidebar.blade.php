<style>
    .nav-sidebar .nav-treeview {
        padding-left: 0; margin-left: 0;
    }

    /* Styling indikator */
    .nav-indicator {
        font-size: 0.75rem;
        width: 1rem;
        text-align: center;
        transition: transform 0.3s ease;
        color: #adb5bd; /* Warna abu-abu */
    }

    /* Strip (-) */
    .nav-indicator.fa-minus {
        font-size: 0.6rem;
        opacity: 0.7;
    }

    /* Menu Open */
    .nav-item.menu-open > .nav-link {
        color: yellow !important;

        .nav-indicator.fa-chevron-right {
            transform: rotate(90deg);
            color: yellow;
        }
    }

    /* Active State */
    .nav-link.active {
        background-color: teal !important;
        color: yellow !important;
    }
    .nav-link.active > .nav-indicator {
        color: yellow !important;
        opacity: 1;
    }

    .nav-sidebar .nav-link > .nav-icon {
        margin-left: 0 !important;
        margin-right: 0.6rem !important;
        font-size: 1rem;
        width: 1.2rem;
        text-align: center;
    }
</style>

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
                <li class="nav-header">Main Navigation</li>
                <x-layouts.sidebar />
            </ul>
        </nav>
    </div>
</aside>
