@extends('system::template/layout/masterlogin')
@section('title', 'PIKDI Rescue Login')

@section('content')
    <div class="login-box">
        <div class="card card-outline card-danger">
            <div class="card-header text-center">
                <a href="{{ url('/') }}" class="h1 text-dark" style="text-decoration: none;">
                    <b>TSU</b> Rescue Login
                </a>
            </div>
            <div class="card-body">
                <p class="login-box-msg text-danger text-bold">
                    <i class="fas fa-user-shield mr-1"></i> EMERGENCY ACCESS
                </p>

                @if ($errors->any())
                    <div class="alert alert-danger text-sm">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(Session::has('alert'))
                    <div class="alert alert-{{ Session::get('alert')['status'] }}">
                        {{ Session::get('alert')['message'] }}
                    </div>
                @endif

                <form action="{{ route('rescue.post') }}" method="POST">
                    @csrf

                    {{-- INPUT 1: USERNAME / NIM --}}
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Target NIM / NIK" name="username" required autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-id-card text-danger"></span>
                            </div>
                        </div>
                    </div>

                    {{-- INPUT 2: MAGIC KEY --}}
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Rescue Key" name="rescue_key" id="rescue_key" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span type="button" id="toggle-password" class="fas fa-lock text-danger" style="cursor: pointer;"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger btn-block text-bold">
                                <i class="fas fa-biohazard mr-1"></i> EXECUTE RESCUE
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <a href="{{ url('/login') }}" class="text-sm text-muted">Kembali ke Login Normal</a>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Fitur Lihat Password (Penting buat ngecek Key yang rumit)
            $('#toggle-password').click(function() {
                let passInput = $('#rescue_key');
                let icon = $(this);
                if (passInput.attr('type') === 'password') {
                    passInput.attr('type', 'text');
                    icon.removeClass('fa-lock').addClass('fa-unlock');
                } else {
                    passInput.attr('type', 'password');
                    icon.removeClass('fa-unlock').addClass('fa-lock');
                }
            });
        });
    </script>
@endsection
