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
                <i class="fas fa-sign-in-alt"></i><b> {{$title}}</b>
            </div>
            <div class="card-body">
                    <p class="login-box-msg text-bold">Masukan Email</p>
                    <form id="form-forgotpassword" method="POST" action="{{route('ForgotPassword.SendLink')}}">
                        {{ csrf_field() }}
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Email" name="email" id="email"
                                required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- /.col -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-block">Kirim Link Reset Password</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>
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
    </script>
@endsection
