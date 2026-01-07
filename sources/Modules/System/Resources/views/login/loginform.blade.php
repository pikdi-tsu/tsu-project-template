@extends('system::template/layout/masterlogin')
@section('title', $title)
@section('link_href')
    <style>
        /* CSS Wajib biar pas diklik barbar teksnya gak ke-blok biru */
        #secret-trigger {
            cursor: default;
            user-select: none; /* Chrome, Opera, Safari */
            -webkit-user-select: none;
            -moz-user-select: none; /* Firefox */
            -ms-user-select: none; /* IE 10+ */
        }
    </style>
@endsection

@section('content')
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                {{-- Tambahkan ID secret-trigger --}}
                <a href="javascript:void(0)" id="secret-trigger" class="h1 text-dark" style="text-decoration: none;">
                    <b>TSU</b> Template
                </a>
            </div>
            <div class="card-body">
                <p class="login-box-msg text-bold">Start Your Session</p>

                @if(Session::has('alert'))
                    <div class="alert alert-{{ Session::get('alert')['status'] }}">
                        {{ Session::get('alert')['message'] }}
                    </div>
                @endif

                {{-- BAGIAN 1: SSO (Default) --}}
                <div id="sso-section">
                    <a href="{{ route('sso.login') }}" class="btn btn-primary btn-block btn-lg mb-3">
                        <i class="fas fa-fingerprint mr-2"></i> <b>Login with TSU</b>
                    </a>
                    <p class="text-muted text-center text-sm mt-4">
                        Gunakan akun kampus TSU untuk masuk.
                    </p>
                </div>

                {{-- BAGIAN 2: MANUAL (Hidden) --}}
                <div id="manual-section" style="display: none;">
                    <hr>
                    <p class="text-center text-danger text-sm"><b><i class="fas fa-user-secret"></i> Administrator Access</b></p>

                    <form id="form-login" method="POST" action="{{ route('login.action') }}">
                        {{ csrf_field() }}

                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Email" name="email" id="email" required>
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-user"></span></div>
                            </div>
                        </div>

                        <div class="input-group mb-3">
                            <input type="password" class="form-control" placeholder="Password" name="password" id="password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span type="button" id="toggle-password" class="fas fa-lock" style="cursor: pointer;"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-dark btn-block btn-sm">Admin Login</button>
                            </div>
                        </div>

                        <div class="mt-2 text-center">
                            <a href="#" id="btn-close-manual" class="text-xs text-muted">Tutup Akses Admin</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let clickCount = 0;
            let clickTimer;
            let requiredClicks = 5;

            $('#secret-trigger').click(function(e) {
                e.preventDefault();
                clickCount++;

                // Debugging (bisa dihapus nanti)
                // console.log("Klik ke: " + clickCount);

                // Reset timer setiap kali klik (biar user punya waktu buat lanjutin combo)
                clearTimeout(clickTimer);

                // Cek apakah jumlah klik sudah tercapai?
                if (clickCount === requiredClicks) {
                    // SUCCESS! Munculkan Form
                    $('#manual-section').slideDown(500);

                    // Reset counter biar gak ke-trigger lagi
                    clickCount = 0;
                } else {
                    // Kalau belum sampai 5x, kasih waktu 500ms (setengah detik)
                    // Kalau lewat dari itu, reset hitungan ke 0 (Gagal Combo)
                    clickTimer = setTimeout(function() {
                        clickCount = 0;
                    }, 500);
                }
            });

            // Tombol Tutup
            $('#btn-close-manual').click(function(e) {
                e.preventDefault();
                $('#manual-section').slideUp(300);
            });

            // Toggle Password
            $('#toggle-password').click(function() {
                let passInput = $('#password');
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

        $(function() {
            if($('.select2').length > 0){ $('.select2').select2(); }
        });
    </script>
@endsection
