
<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('link_href'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?php echo e($menu); ?></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active"><?php echo e($menu); ?></li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- /.col-md-6 -->
                <div class="col-md-12">
                    <div class="card card-primary card-outline card-tabs">
                        <div class="card-header p-0 pt-1 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="pill" href="#pegawai" role="tab"
                                        aria-controls="custom-tabs-three-home" aria-selected="true">Pegawai</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="pill" href="#mahasiswa" role="tab"
                                        aria-controls="custom-tabs-three-profile" aria-selected="false">Mahasiswa</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-three-tabContent">
                                <div class="tab-pane fade show active" id="pegawai" role="tabpanel">
                                    <table id="example2" class="table table-bordered table-hover" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NIP</th>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>Role Access</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="mahasiswa" role="tabpanel">
                                    <table id="example3" class="table table-bordered table-hover" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NIM</th>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>Role Access</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2()

            let initialTab = $('.nav-tabs .nav-link.active').attr('href');

            handleTabShown(initialTab);

            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                let target = $(e.target).attr("href");
                handleTabShown(target);
            });

            function handleTabShown(target) {
                if (target === '#pegawai') {
                    loadTablePegawai()
                } else if (target === '#mahasiswa') {
                    loadTableMahasiswa()
                }
            }

            function loadTablePegawai() {
                let otable = $('#example2').DataTable({
                    destroy: true,
                    processing: true,
                    paging: false,
                    scrollX: true,
                    scrollY: '500px',
                    scrollCollapse: true,
                    serverSide: true,
                    searchDelay: 500,
                    order: [],
                    ajax: {
                        url: '<?php echo route('UserReset.tabelPegawai'); ?>',
                        type: 'GET',
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'nip'
                        },
                        {
                            data: 'nama'
                        },
                        {
                            data: 'email'
                        },
                        {
                            data: 'role'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    language: {
                        processing: '<i class="fa fa-spinner fa-lg fa-spin"></i>'
                    },
                    drawCallback: function(settings) {
                        //fungsi yang dipanggil
                    }
                });

                otable.on('draw', function(event) {
                    $('[data-toggle="tooltip"]').tooltip({
                        trigger: "hover"
                    });
                    $('[data-tooltip="tooltip"]').tooltip({
                        trigger: "hover"
                    });
                });

                // Ketika tab ditampilkan, sesuaikan ukuran kolom
                $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
                    let target = $(e.target).attr("href");
                    if (target === '#pegawai') {
                        setTimeout(() => {
                            otable.columns.adjust().draw();
                        }, 100);
                    }
                });
            }

            function loadTableMahasiswa() {
                let otable = $('#example3').DataTable({
                    destroy: true,
                    processing: true,
                    paging: false,
                    scrollX: true,
                    scrollY: '500px',
                    scrollCollapse: true,
                    serverSide: true,
                    searchDelay: 500,
                    order: [],
                    ajax: {
                        url: '<?php echo route('UserReset.tabelMahasiswa'); ?>',
                        type: 'GET',
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'nim'
                        },
                        {
                            data: 'nama'
                        },
                        {
                            data: 'email'
                        },
                        {
                            data: 'role',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    language: {
                        processing: '<i class="fa fa-spinner fa-lg fa-spin"></i>'
                    },
                    drawCallback: function(settings) {
                        //fungsi yang dipanggil

                    }
                });

                otable.on('draw', function(event) {
                    $('[data-toggle="tooltip"]').tooltip({
                        trigger: "hover"
                    });
                    $('[data-tooltip="tooltip"]').tooltip({
                        trigger: "hover"
                    });
                });

                // Ketika tab ditampilkan, sesuaikan ukuran kolom
                $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
                    let target = $(e.target).attr("href");
                    if (target === '#mahasiswa') {
                        setTimeout(() => {
                            otable.columns.adjust().draw();
                        }, 100);
                    }
                });

            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('system::template/admin/header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\htdocs\tsu_siakad\sources\Modules\System\Providers/../Resources/views/setting/userreset.blade.php ENDPATH**/ ?>