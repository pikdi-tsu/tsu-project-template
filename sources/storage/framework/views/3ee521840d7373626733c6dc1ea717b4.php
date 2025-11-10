
<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('link_href'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <i class="fas fa-sign-in-alt"></i><b> Login</b>
        </div>
        <div class="card-body">
            <?php
                $login_chance = Session::get('login_chance');
                if (Session::has('login_chance')) {
                    $chance = $login_chance['chance'];
                    $time = $login_chance['time_start'];
                } else {
                    $chance = 5;
                    $time = 0;
                }

                if (Session::has('time_chance')) {
                    $time_chance = date('i:s', Session::get('time_chance'));
                } else {
                    $time_chance = '00:00';
                }
            ?>
            <?php if($chance > 0): ?>
                <p class="login-box-msg text-bold">Start Your Session</p>
                <form id="form-login" method="POST" action="<?php echo e(route('loginaction')); ?>">
                    <?php echo e(csrf_field()); ?>

                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Email" name="email" id="email"
                            required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Password" name="password"
                            id="password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <a href="<?php echo e(route('ForgotPassword.show')); ?>" class="btn btn-danger btn-block">Forgot
                                Password</a>
                        </div>
                        <!-- /.col -->
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-warning text-center">
                    Kesempatan login habis. Silakan tunggu:
                    <h1 id="time_remaining" class="text-danger font-weight-bold text-center mt-2"></h1>
                </div>
            <?php endif; ?>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
<!-- /.login-box -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    function chance() {
        $.ajax({
            url: '<?php echo e(url('loginChance')); ?>',
            success: function(data) {
                console.log(data);
            },
        });
    }

    $(function() {
        //Initialize Select2 Elements
        <?php if($chance <= 0): ?>
            var timer2 = '<?php echo e($time_chance); ?>';
            var interval = setInterval(function() {

                var timer = timer2.split(':');
                //by parsing integer, I avoid all extra string processing
                var minutes = parseInt(timer[0], 10);
                var seconds = parseInt(timer[1], 10);
                --seconds;
                minutes = (seconds < 0) ? --minutes : minutes;
                if (minutes < 0) clearInterval(interval);
                seconds = (seconds < 0) ? 59 : seconds;
                seconds = (seconds < 10) ? '0' + seconds : seconds;
                //minutes = (minutes < 10) ?  minutes : minutes;

                if (minutes == 0 && seconds == 0) {
                    window.location.href = "<?php echo e(url('login')); ?>";
                }

                $('#time_remaining').html(minutes + ':' + seconds);
                timer2 = minutes + ':' + seconds;
            }, 1000);
        <?php endif; ?>
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('system::template/layout/masterlogin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\htdocs\tsu_siakad\sources\Modules\System\Providers/../Resources/views/login/loginform.blade.php ENDPATH**/ ?>