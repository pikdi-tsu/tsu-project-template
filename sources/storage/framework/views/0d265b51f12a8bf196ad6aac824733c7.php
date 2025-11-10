<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo e(asset('public/assetsku/img/logotsu.png')); ?>" type="image/png" />
    <title>TSU - <?php echo e($title); ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/plugins/fontawesome-free/css/all.min.css')); ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/dist/css/adminlte.min.css')); ?>">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css')); ?>">
        <!-- Select2 -->
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/plugins/select2/css/select2.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')); ?>">
    <?php echo $__env->yieldContent('link_href'); ?>
</head>

<body class="hold-transition layout-top-nav layout-footer-fixed layout-navbar-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand-md navbar-light bg-lightblue text-sm">
            <div class="container">
                <a href="#" class="navbar-brand">
                    <img src="<?php echo e(asset('public/assetsku/img/logotsu.png')); ?>" alt="AdminLTE Logo" class="brand-image"
                        style="opacity: .8">
                    <span class="brand-text font-weight-light">Tiga Serangkai University</span>
                </a>
            </div>
        </nav>
        <!-- /.navbar -->

        <!-- Main content -->
        <div class="content login-page">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
        <!-- /.content -->

        <!-- Main Footer -->
        <footer class="main-footer text-sm">
            <!-- To the right -->
            <div class="float-right">
            </div>
            <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights
            reserved.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

    <!-- jQuery -->
    <script src="<?php echo e(asset('public/assets/plugins/jquery/jquery.min.js')); ?>"></script>
    <!-- Bootstrap 4 -->
    <script src="<?php echo e(asset('public/assets/plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo e(asset('public/assets/dist/js/main.js')); ?>"></script>
    <script src="<?php echo e(asset('public/assets/dist/js/adminlte.min.js')); ?>"></script>
    <script src="<?php echo e(asset('public/assets/dist/js/sweetalert.js')); ?>"></script>
    <!-- Select2 -->
    <script src="<?php echo e(asset('public/assets/plugins/select2/js/select2.full.min.js')); ?>"></script>
    <script>
        <?php if(Session::has('alert')): ?>
            Swal.fire('<?php echo e(session('alert')['title']); ?>', '<?php echo e(session('alert')['message']); ?>',
                '<?php echo e(session('alert')['status']); ?>')
        <?php endif; ?>

        function sweetAlert(alert, desc, text) {
            const Alert = Swal.mixin({
                showConfirmButton: true,
                timer: 2000
            });

            Alert.fire({
                icon: alert,
                title: desc,
                text: text,
            });
        }

        $("#password,#a_1,#a_2").keypress(function(event){
            var ew = event.which;

            if(48 <= ew && ew <= 57)
                return true;
            if(65 <= ew && ew <= 90)
                return true;
            if(97 <= ew && ew <= 122)
                return true;
            return false;
        });
    </script>
    <?php echo $__env->yieldContent('script'); ?>
</body>

</html>
<?php /**PATH D:\htdocs\tsu_siakad\sources\Modules\System\Providers/../Resources/views/template/layout/masterlogin.blade.php ENDPATH**/ ?>