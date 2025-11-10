<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('link_href'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?php echo e($title); ?></h1>
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
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- /.col-md-6 -->
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="m-0">Change your Password</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <span class="badge badge-warning">Note</span>
                                    <br>
                                    <code>
                                        Password Harus Memiliki : <br>
                                        1. Minimal 8 Karakter <br>
                                        2. Minimal Ada 1 Huruf Besar <br>
                                        3. Minimal Ada 1 angka <br>
                                        4. Tidak Boleh Menggunakan Tanda Baca (',./!@#$%^&*-=')
                                    </code>
                                </div>
                            </div>
                            <br>
                            <form action="<?php echo e(route('save.changepassword')); ?>" id="form-changepassword" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Password Lama</label>
                                        <input type="password" class="form-control" name="oldpass" id="oldpass"
                                            placeholder="Password Lama" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Password Baru</label>
                                        <input type="password" class="form-control" name="newpass" id="newpass"
                                            placeholder="Password Baru" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" name="newpass2" id="newpass2"
                                            placeholder="Konfirmasi Password Baru" required>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-right">
                            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary btn-sm mr-2"
                                id="testing-btn">Kembali</a>
                            <button type="button" class="btn btn-success btn-sm" id="btn-submit">Simpan</button>
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->
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

            // $('#loading').show()
            loadEvent()

            function loadEvent() {
                // action()
                EventSubmit()
            }

            // function action() {
            //     $('#btn-submit').click(function(e) {
            //         e.preventDefault();
            //         let oldpass = $('#oldpass').val()
            //         let newpass = $('#newpass').val()
            //         let newpass2 = $('#newpass2').val()
            //         let formData = $('#form-changepassword').serialize()
            //         if (oldpass == '' || newpass == '' || newpass2 == '') {
            //             notifalert('Information', 'Silahkan Lengkapi Password!', 'warning')
            //         } else {
            //             Swal.fire({
            //                 title: "Information",
            //                 text: "Apakah Password Sudah Benar ?",
            //                 icon: "question",
            //                 showConfirmButton: true,
            //                 showCancelButton: true,
            //             }).then((result) => {
            //                 if (result.value) {
            //                     $.ajax({
            //                         type: "POST",
            //                         url: "<?php echo e(route('save.changepassword')); ?>",
            //                         data: formData,
            //                         // processData: false,
            //                         // contentType: false,
            //                         dataType: "JSON",
            //                         beforeSend: function(response) {
            //                             $('#loading').show()
            //                             $('#btn-submit').prop('disabled', true)
            //                         },
            //                         success: function(data) {
            //                             Swal.fire({
            //                                 title: data.title,
            //                                 text: data.message,
            //                                 icon: data.status
            //                             }).then((result) => {
            //                                 $('#loading').hide()
            //                                 $('#btn-submit').prop('disabled', false)
            //                                 // $('#form-changepassword').trigger('reset');
            //                             });
            //                             return;
            //                         },
            //                         error: function(xhr, status, error) {
            //                             Swal.fire({
            //                                 title: 'Unsuccessfully Saved Data',
            //                                 text: 'Check Your Data',
            //                                 icon: 'error'
            //                             }).then((result) => {
            //                                 $('#loading').hide()
            //                                 $('#btn-submit').prop('disabled', false)
            //                             });
            //                             return;
            //                         }
            //                     });
            //                     return false;
            //                 } else {
            //                     return false;
            //                 }
            //             })
            //         }
            //     });
            // }

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

        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('system::template/admin/header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\htdocs\tsu_siakad\sources\Modules\System\Providers/../Resources/views/setting/changepassword.blade.php ENDPATH**/ ?>