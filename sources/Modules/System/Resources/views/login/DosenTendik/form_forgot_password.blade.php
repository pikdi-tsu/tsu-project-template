@extends('system::template/layout/masterlogin')
@section('title', $title)
@section('link_href')
@endsection

@section('content')
    {{-- Login-box --}}
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <i class="fas fa-sign-in-alt"></i><b> {{ $title }}</b>
            </div>
            <div class="card-body">
                @if($data->forgot_password_send_email === 1)
                <p class="login-box-msg text-bold">Masukan Password Baru</p>
                <form id="form-forgot-password" method="POST" action="{{route('forgot_password.action',['type' => $type])}}">
                    {{ csrf_field() }}
                    <input type="hidden" name="params" value="{{ $params }}">
                    <small><code id="warning"></code></small>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="email" id="email" value="{{ $data->email }}" readonly>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" onkeyup="checkPassword()" placeholder="Password"
                            name="password" id="password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>

                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" onkeyup="checkPassword()" placeholder="Ulangi Password"
                            name="password_retype" id="password_retype" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- /.col -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-block">Submit</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
                @else
                <div class="alert alert-warning text-center text-bold">
                    Password Sudah Diganti ! <br> Silahkan Login !
                </div>
                @endif
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.login-box -->
@endsection

@section('script')
    <script>
        $(function() {

        });

        function checkPassword() {
            var password = $('#password').val();
            var password_re = $('#password_retype').val();

            if (password !== '' && password_re !== '') {
                if (password !== password_re) {
                    $('#password_retype').addClass('is-invalid');
                    $('#password_retype').removeClass('is-valid');

                    $('#submit').attr('disabled', 'disabled');
                } else {
                    $('#password_retype').removeClass('is-invalid');
                    $('#password_retype').addClass('is-valid');
                    $('#submit').removeAttr('disabled');
                }
            }

            if (password.length < 8) {
                $('#password').addClass('is-invalid');
                $('#password').removeClass('is-valid');

                $('#warning').html('*Mininum length : 8');
                $('#submit').attr('disabled', 'disabled');
            } else {
                pass_numb = password.replace(/[^0-9]/g, '').length;
                pass_char = password.replace(/[0-9]/g, '').length;

                if (pass_numb === 0) {
                    $('#password').addClass('is-invalid');
                    $('#password').removeClass('is-valid');

                    $('#warning').html('*Must contain Number');
                    $('#submit').attr('disabled', 'disabled');
                } else if (pass_char === 0) {
                    $('#password').addClass('is-invalid');
                    $('#password').removeClass('is-valid');

                    $('#warning').html('*Must contain Letter');
                    $('#submit').attr('disabled', 'disabled');
                }else if(password === 'password@123'){
                    $('#password').addClass('is-invalid');
                    $('#password').removeClass('is-valid');

                    $('#warning').html('*dont use password this');
                    $('#submit').attr('disabled', 'disabled');
                }else {
                    $('#password').removeClass('is-invalid');
                    $('#password').addClass('is-valid');
                    $('#warning').html('');
                }
            }
        }
        // $("#password,#password_retype").keypress(function(event) {
        //     var ew = event.which;
        //
        //     if (48 <= ew && ew <= 57)
        //         return true;
        //     if (65 <= ew && ew <= 90)
        //         return true;
        //     if (97 <= ew && ew <= 122)
        //         return true;
        //     return false;
        // });
    </script>
@endsection
