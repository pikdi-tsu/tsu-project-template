<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="{{ asset('public/assetsku/img/logotsu.png') }}" alt="AdminLTE Logo" class="brand-image"
            style="opacity: .8">
        <span class="brand-text font-weight-light" style="font-size: 18px;font-weight: bold;">Tiga Serangkai
            University</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ url('sources/storage/app/FILE_PHOTOPROFILE/' . photo_profile()) }}"
                    class="img-circle elevation-2"
                    style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #adb5bd;" alt="User Image">
            </div>
            <div class="info text-sm">
                <a href="javascript:void(0)" class="d-block">{{ session('session')['user_nama'] }}</a>
                {{-- <a href="#"><i class="fa fa-circle text-success"></i> Online</a> --}}
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column text-sm" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
                <li class="nav-header">Main Navigation</li>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @if (checkmenu('SDM','Data Mahasiswa')>0)
                <li class="nav-item">
                    <a href="{{ route('home.mahasiswa') }}" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data Mahasiswa</p>
                    </a>
                </li>
                @endif
                @if (checkmenu('SDM','Data Pegawai')>0)
                <li class="nav-item">
                    <a href="{{ route('home.dosen') }}" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data Pegawai</p>
                    </a>
                </li>
                @endif
                <li class="nav-item" style="display: none;">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Master Data
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Master A
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Master B
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Master C
                            </a>
                        </li>
                    </ul>
                </li>
                @php
                    $changepassword = checkmenu('Tools','Change Password');
                    $listmenu = checkmenu('Tools','List Menu');
                    $groupuser = checkmenu('Tools','Group User');
                    $usermanagement = checkmenu('Tools','User Management');
                    $userreset = checkmenu('Tools','User Reset');
                    // dd($changepassword,$listmenu);
                @endphp
                @if ($changepassword>0)
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            Tools
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if ($changepassword>0)
                        <li class="nav-item">
                            <a href="{{ route('show.changepassword') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Change Password
                            </a>
                        </li>
                        @endif
                        @if($listmenu+$groupuser>0)
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon far fa-circle text-danger"></i>
                                <p> Management Menu
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @if($listmenu>0)
                                <li class="nav-item">
                                    <a href="{{route('menu.show')}}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>List Menu</p>
                                    </a>
                                </li>
                                @endif
                                @if($groupuser>0)
                                <li class="nav-item">
                                    <a href="{{route('gruopuser.show')}}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Group User</p>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        @if($usermanagement+$userreset>0)
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon far fa-circle text-danger"></i>
                                <p> Management User
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @if($usermanagement>0)
                                <li class="nav-item">
                                    <a href="{{ route('show.userManagement') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>User Management</p>
                                    </a>
                                </li>
                                @endif
                                @if($userreset>0)
                                <li class="nav-item">
                                    <a href="{{route('UserReset.show')}}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>User Reset</p>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
<style>
    .user-panel .info a {
        white-space: normal !important;
        word-break: break-word;
        display: block;
        max-width: 150px;
    }
</style>
<!-- /.Main Sidebar Container -->
