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
                    <b>TSU</b> {{ ucfirst(config('app.module.name')) }}
                </a>
            </div>
            <div class="card-body">
                <p class="login-box-msg text-bold">Start Your Session</p>

                {{-- ALERT CUSTOM --}}
                @if(Session::has('alert'))
                    <div class="alert alert-{{ Session::get('alert')['status'] }} alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        @if(isset(Session::get('alert')['title']))
                            <h5><i class="icon fas fa-info-circle"></i> {{ Session::get('alert')['title'] }}</h5>
                        @endif
                        {!! Session::get('alert')['message'] !!}
                    </div>
                @endif

                {{-- ALERT ERROR STANDARD --}}
                @if(Session::has('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        {!! Session::get('error') !!}
                    </div>
                @endif

                {{-- ALERT SUCCESS STANDARD --}}
                @if(Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                        {!! Session::get('success') !!}
                    </div>
                @endif

                {{-- BAGIAN 1: SSO (Default) --}}
                <div id="sso-section">
                    <a href="{{ route('sso.login') }}" id="btn-sso" onclick="freezeButton(this)" class="btn btn-primary btn-block btn-lg mb-3">
                        <i class="fas fa-fingerprint mr-2"></i> <b>Login with TSU</b>
                    </a>
                    <p class="text-muted text-center text-sm mt-4">
                        Gunakan akun kampus TSU untuk masuk.
                    </p>
                </div>

                {{-- BAGIAN 2: MANUAL (Hidden) --}}
                <div id="manual-section" style="display: none;">
                    <hr>
                    <p class="text-center text-danger text-sm"><b><i class="fas fa-user-secret"></i> PIKDI Access</b></p>

                    <form onsubmit="document.getElementById('btn-login').disabled = true; document.getElementById('btn-login').innerText = 'Loading...';" id="form-login" method="POST" action="{{ route('login.action') }}">
                        {{ csrf_field() }}

                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Email atau Username" name="identity" id="identity" value="{{ old('identity') }}" required>
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
                                <button type="submit" id="btn-login" class="btn btn-dark btn-block btn-sm">PIKDI Login</button>
                            </div>
                        </div>

                        <div class="mt-2 text-center">
                            <a href="#" id="btn-close-manual" class="text-xs text-muted">Tutup Akses PIKDI</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Prevent spam click SSO button
        function freezeButton(element) {
            var originalWidth = element.offsetWidth;
            element.style.width = originalWidth + 'px';

            element.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Connecting...';
            element.classList.add('disabled');
            element.classList.remove('btn-primary');
            element.classList.add('btn-secondary');
            element.style.pointerEvents = 'none';
            element.style.cursor = 'not-allowed';
        }

        $(document).ready(function() {
            let clickCount = 0;
            let clickTimer;
            let requiredClicks = 5;

            $('#secret-trigger').click(function(e) {
                e.preventDefault();
                clickCount++;
                clearTimeout(clickTimer);

                if (clickCount === requiredClicks) {
                    $('#manual-section').slideDown(500);
                    clickCount = 0;
                } else {
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

            // Logic Timer SSO Lockdown
            let ssoSeconds = {{ session('retry_seconds_sso', $existing_sso_seconds ?? 0) }};

            if (ssoSeconds > 0) {
                let btnSso = $('#btn-sso');
                let alertTimer = $('#sso-alert-timer');

                // Matikan Tombol SSO
                freezeButton(btnSso[0]);

                // Fungsi Timer SSO Login
                function updateSsoTimer() {
                    btnSso.html(`<i class="fas fa-hourglass-half fa-spin mr-2"></i> Tunggu ${ssoSeconds}s`);

                    // Update Alert
                    if(alertTimer.length > 0) {
                        alertTimer.text(ssoSeconds);
                    }
                    ssoSeconds--;

                    // Cek Finish
                    if (ssoSeconds < 0) {
                        clearInterval(timerSso);
                        btnSso.removeClass('disabled btn-secondary').addClass('btn-primary');
                        btnSso.html('<i class="fas fa-fingerprint mr-2"></i> <b>Login with TSU</b>');
                        btnSso.css('pointer-events', 'auto').css('cursor', 'pointer').css('width', 'auto');
                        $('.alert-danger').fadeOut();
                    }
                }

                updateSsoTimer();

                let timerSso = setInterval(updateSsoTimer, 1000);
            }

            // Logic Timer Manual Login PIKDI Lockdown & Auto open anual login
            let manualSeconds = {{ session('retry_seconds_manual', $existing_manual_seconds ?? 0) }};
            let oldIdentity = "{{ old('identity') }}";
            let isManualBlocked = {{ session('retry_seconds_manual', $existing_manual_seconds ?? 0) }} > 0;
            if (oldIdentity !== "" || isManualBlocked) {
                $('#manual-section').show();
            }

            if (manualSeconds > 0) {
                let btnManual = $('#btn-login');
                let alertTimer = $('#sso-alert-timer');
                let originalText = btnManual.text();

                // Matikan Tombol Manual
                btnManual.prop('disabled', true).addClass('btn-secondary').removeClass('btn-dark');

                // Fungsi Timer Manual Login
                function updateManualTimer() {
                    btnManual.html(`<i class="fas fa-lock mr-1"></i> Locked (${manualSeconds}s)`);

                    if(alertTimer.length > 0) {
                        alertTimer.text(manualSeconds);
                    } else {
                        console.log("Target Timer tidak ditemukan! Cek ID di LoginController.");
                    }

                    manualSeconds--;

                    if (manualSeconds < 0) {
                        clearInterval(timerManual);
                        btnManual.prop('disabled', false).removeClass('btn-secondary').addClass('btn-dark');
                        btnManual.text(originalText);
                        $('.alert-danger').fadeOut();
                    }
                }

                updateManualTimer();
                let timerManual = setInterval(updateManualTimer, 1000);
            }

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
