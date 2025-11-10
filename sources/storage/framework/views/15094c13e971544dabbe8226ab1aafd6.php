
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
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">

                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #adb5bd;" src="<?php echo e(url('sources/storage/app/FILE_PHOTOPROFILE/'.photo_profile())); ?>" alt="User profile picture">
                            </div>

                            <h3 class="profile-username text-center"><?php echo e(session('session')['user_nama']); ?></h3>

                            <p class="text-muted text-center">Software Engineer</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Followers</b> <a class="float-right">1,322</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Following</b> <a class="float-right">543</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Friends</b> <a class="float-right">13,287</a>
                                </li>
                            </ul>

                            <a href="#" class="btn btn-primary btn-block"><b>Follow</b></a>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                    <!-- About Me Box -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">About Me</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <strong><i class="fas fa-book mr-1"></i> Education</strong>

                            <p class="text-muted">
                                B.S. in Computer Science from the University of Tennessee at Knoxville
                            </p>

                            <hr>

                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>

                            <p class="text-muted">Malibu, California</p>

                            <hr>

                            <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>

                            <p class="text-muted">
                                <span class="tag tag-danger">UI Design</span>
                                <span class="tag tag-success">Coding</span>
                                <span class="tag tag-info">Javascript</span>
                                <span class="tag tag-warning">PHP</span>
                                <span class="tag tag-primary">Node.js</span>
                            </p>

                            <hr>

                            <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>

                            <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum
                                enim neque.</p>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#settings" data-toggle="tab">Settings</a>
                                </li>
                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="settings">
                                    <form action="<?php echo e(route('save.changeprofile')); ?>" class="form-horizontal" method="POST" id="form-profile" enctype="multipart/form-data">
                                        <?php echo csrf_field(); ?>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">Photo Profil</label>
                                            <div class="input-group col-sm-10">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="photoprofile" name="photoprofile" accept="image/*">
                                                    <label class="custom-file-label" for="photoprofile">Choose file</label>
                                                </div>
                                            </div>

                                        </div>
                                    </form>
                                    <div class="form-group row">
                                        <div class="offset-sm-2 col-sm-10">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="ceklis"> Setuju Untuk Mengubah Profile
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="offset-sm-2 col-sm-10">
                                            <button type="submit" id="btn-submit" class="btn btn-success" disabled>Submit</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.tab-pane -->
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
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
            //Load Function
            loadEvent()

            function loadEvent()
            {
                checkEvent()
                eventSubmit()
            }

            function checkEvent()
            {
                $('#ceklis').on('change', function () {
                    if ($(this).is(':checked')) {
                        $('#btn-submit').prop('disabled', false); // aktifkan tombol
                    } else {
                        $('#btn-submit').prop('disabled', true); // nonaktifkan tombol
                    }
                });
            }

            function eventSubmit() {
                $('#btn-submit').click(function(e) {
                    e.preventDefault(); // cegah submit langsung
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
                            $('#form-profile').submit();
                        }
                    });
                });
            }

        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('system::template/admin/header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\htdocs\tsu_siakad\sources\Modules\System\Providers/../Resources/views/setting/editprofile.blade.php ENDPATH**/ ?>