<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo e(route('dashboard')); ?>" class="brand-link">
        <img src="<?php echo e(asset('public/assetsku/img/logotsu.png')); ?>" alt="AdminLTE Logo" class="brand-image"
            style="opacity: .8">
        <span class="brand-text font-weight-light" style="font-size: 18px;font-weight: bold;">Tiga Serangkai
            University</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?php echo e(url('sources/storage/app/FILE_PHOTOPROFILE/' . photo_profile())); ?>"
                    class="img-circle elevation-2"
                    style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #adb5bd;" alt="User Image">
            </div>
            <div class="info text-sm">
                <a href="javascript:void(0)" class="d-block"><?php echo e(session('session')['user_nama']); ?></a>
                
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column text-sm" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
                <li class="nav-header">Main Navigation</li>
                <li class="nav-item">
                    <a href="<?php echo e(route('dashboard')); ?>" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php if(checkmenu('SDM','Data Mahasiswa')>0): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('home.mahasiswa')); ?>" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data Mahasiswa</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(checkmenu('SDM','Data Pegawai')>0): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('home.dosen')); ?>" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data Pegawai</p>
                    </a>
                </li>
                <?php endif; ?>
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
                <?php
                    $changepassword = checkmenu('Tools','Change Password');
                    $listmenu = checkmenu('Tools','List Menu');
                    $groupuser = checkmenu('Tools','Group User');
                    $usermanagement = checkmenu('Tools','User Management');
                    $userreset = checkmenu('Tools','User Reset');
                    // dd($changepassword,$listmenu);
                ?>
                <?php if($changepassword>0): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            Tools
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if($changepassword>0): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('show.changepassword')); ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                Change Password
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if($listmenu+$groupuser>0): ?>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon far fa-circle text-danger"></i>
                                <p> Management Menu
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <?php if($listmenu>0): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('menu.show')); ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>List Menu</p>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if($groupuser>0): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('gruopuser.show')); ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Group User</p>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>
                        <?php if($usermanagement+$userreset>0): ?>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon far fa-circle text-danger"></i>
                                <p> Management User
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <?php if($usermanagement>0): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('show.userManagement')); ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>User Management</p>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if($userreset>0): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('UserReset.show')); ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>User Reset</p>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
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
<?php /**PATH D:\htdocs\testingkuB\sources\Modules\System\Providers/../Resources/views/template/admin/sidebar.blade.php ENDPATH**/ ?>