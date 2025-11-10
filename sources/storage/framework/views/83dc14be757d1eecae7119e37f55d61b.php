
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
                                    <button id="btn-add" class="btn btn-success btn-sm">Add User</button>
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

    <div class="modal fade" id="modal-adduser">
        <div class="modal-dialog">
            <div class="modal-content ">
                <div class="modal-header">
                    <h4 class="modal-title" id="judul-modal"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo e(route('show.saveUser')); ?>" method="post" id="form-user">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="userid" id="userid">
                        <label for="roleaccess">Role Access</label>
                        <select class="form-control select2" id="roleaccess" name="roleaccess" required>
                            <option value="-1" selected disabled>-- Pilih Role Akses --</option>
                            <?php $__currentLoopData = $mastergroup; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($q->KodeGroupUser); ?>"><?php echo e($q->NamaGroup); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <label for="nik">Nama User</label>
                        <select class="form-control select2" id="nik" name="nik" disabled required>

                        </select>

                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" form="form-user" class="btn btn-success">Submit</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
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
                    tab_pegawai()
                } else if (target === '#mahasiswa') {
                    tab_mahasiswa()
                }
            }

            loadEvent()

            function loadEvent(){
                addUser()
                findUser()
                closemodal()
            }

            //Tab Pegawai
            function tab_pegawai() {
                loadTablePegawai()
            }

            function EventSubmit() {
                $('#btn-submit').click(function(e) {
                    e.preventDefault(); // cegah submit langsung
                    let oldpass = $('#oldpass').val()
                    let newpass = $('#newpass').val()
                    let newpass2 = $('#newpass2').val()
                    if (oldpass == '' || newpass == '' || newpass2 == '') {
                        notifalert('Information', 'Silahkan Lengkapi Password!', 'warning')
                    } else {
                        Swal.fire({
                            title: "Konfirmasi",
                            text: "Apakah Anda yakin ingin mengirim form?",
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonText: "Ya",
                            cancelButtonText: "Tidak"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#loading').show()
                                $('#form-changepassword').submit();
                            }
                        });
                    }
                });
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
                        url: '<?php echo route('show.tabelPegawai'); ?>',
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
                        EditUser()
                        deleteUser()
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

            function addUser() {
                $('#btn-add').click(function(e) {
                    e.preventDefault();
                    changerole()
                    $('#judul-modal').html('Add User')
                    $('#modal-adduser').modal('show')
                    $('#userid').val(null)
                });
            }

            function changerole() {
                $('#roleaccess').on('change', function() {
                    let role = $(this).val()
                    if (role != '') {
                        $('#nik').prop('disabled', false)
                    } else {
                        $('#nik').prop('disabled', true)
                    }
                });
            }

            function findUser() {
                $('#nik').select2({
                    placeholder: 'Cari NIK/Nama',
                    ajax: {
                        url: '<?php echo e(route('show.finduser')); ?>', // Buat route ini nanti
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term, // keyword pencarian
                                role: $('#roleaccess').val()
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        id: item.nip,
                                        text: item.nip + '-' + item.nama
                                    }
                                })
                            };
                        },
                        cache: true
                    }
                });
            }

            function EditUser() {
                // $('.btn_edit').click(function(e) {
                $(document).off('click', '.btn_edit').on('click', '.btn_edit', function (e) {
                    e.preventDefault();
                    let params = $(this).data('id')
                    $('#judul-modal').html('Edit User')
                    $.ajax({
                        type: "GET",
                        url: '<?php echo url('setting/detailuser'); ?>' + '/' + params,
                        dataType: "JSON",
                        beforeSend: function(response) {
                            $('#loading').show()
                            $('#userid').val(null)
                        },
                        success: function(data) {
                            $('#userid').val(data.userid)

                            $('#roleaccess').val(data.role).trigger('change')
                            $('#roleaccess option[value="4"]').prop('disabled', true);
                            // $('#roleaccess').val('4').prop('disabled', true)
                            // $('#roleaccess').prop('disabled', true)

                            let option = '<option value="' + data.nik + '" selected>' + data
                                .nik + '-' + data.nama + '</option>';
                            $('#nik').append(option).trigger('change');
                            $('#nik').prop('disabled', true)
                            $('#loading').hide()

                            $('#modal-adduser').modal('show')
                        }
                    });
                    return false;
                });
            }

            //Tab Mahasiswa
            function tab_mahasiswa() {
                loadTableMahasiswa()
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
                        url: '<?php echo route('show.tabelMahasiswa'); ?>',
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
                        deleteUser()
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

            //Untuk Pegawai dan Mahasiswa
            function deleteUser() {
                $(document).off('click', '.btn_delete').on('click', '.btn_delete', function (e) {
                    e.preventDefault();
                    let params = $(this).data('id');
                    // console.log(params)
                    Swal.fire({
                            title: "Konfirmasi",
                            text: "Apakah Anda yakin menghapus User ini?",
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonText: "Ya",
                            cancelButtonText: "Tidak"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // window.location.href = e.currentTarget.href;
                                $.ajax({
                                    type: "GET",
                                    url: '<?php echo url('setting/deleteuser'); ?>' + '/' + params,
                                    dataType: "JSON",
                                    beforeSend: function(response) {
                                        $('#loading').show()
                                    },
                                    success: function(data) {
                                        $('#loading').hide()
                                        notifalert(data.title, data.message, data.status)
                                        $("#example2").DataTable().ajax.reload();
                                        $("#example3").DataTable().ajax.reload();
                                    }
                                });
                                return false;
                            }else{
                                return false;
                            }
                        });

                });
            }

            function closemodal() {
                $('#modal-adduser').on('hidden.bs.modal', function() {
                    $('#userid').val(null)
                    $('#roleaccess').val('-1').trigger('change')
                    $('#roleaccess').prop('disabled', false)
                    $('#roleaccess option[value="4"]').prop('disabled', false);

                    $('#nik').val(null)
                    $('#nik').html('')
                    $('#nik').prop('disabled', true)

                });
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('system::template/admin/header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\htdocs\tsu_siakad\sources\Modules\System\Providers/../Resources/views/setting/usermanagement.blade.php ENDPATH**/ ?>