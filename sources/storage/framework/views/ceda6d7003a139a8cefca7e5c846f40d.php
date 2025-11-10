<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
        <link rel="icon" href="<?php echo e(asset('public/assetsku/img/logotsu.png')); ?>" type="image/png"/>
        <title>Tiga Serangkai University</title>
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo e(asset('public/assetsku/css/bootstrap.css')); ?>" />
        <link rel="stylesheet" href="<?php echo e(asset('public/assetsku/css/flaticon.css')); ?>" />
        <link rel="stylesheet" href="<?php echo e(asset('public/assetsku/css/themify-icons.css')); ?>" />
        <link rel="stylesheet" href="<?php echo e(asset('public/assetsku/vendors/owl-carousel/owl.carousel.min.css')); ?>" />
        <link rel="stylesheet" href="<?php echo e(asset('public/assetsku/vendors/nice-select/css/nice-select.css')); ?>" />
        <!-- main css -->
        <link rel="stylesheet" href="<?php echo e(asset('public/assetsku/css/style.css')); ?>" />
        <?php echo $__env->yieldContent('link_href'); ?>
    </head>

    <body>
        <?php echo $__env->make('system::template/halamandepan/navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->yieldContent('content'); ?>

        <?php echo $__env->make('system::template/halamandepan/footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <script>
            <?php if(Session::has('alert')): ?>
                Swal.fire('<?php echo e(session('alert')['title']); ?>', '<?php echo e(session('alert')['message']); ?>', '<?php echo e(session('alert')['status']); ?>')
            <?php endif; ?>

            function sweetAlert(alert, desc, text) {
                const Alert = Swal.mixin({
                    showConfirmButton: true,
                    timer: 3000
                });

                Alert.fire({
                    type: alert,
                    title: desc,
                    text: text,
                });
            }
        </script>
        <?php echo $__env->yieldContent('script'); ?>
    </body>
</html>
<?php /**PATH D:\htdocs\testingku\sources\Modules\System\Providers/../Resources/views/template/halamandepan/master.blade.php ENDPATH**/ ?>