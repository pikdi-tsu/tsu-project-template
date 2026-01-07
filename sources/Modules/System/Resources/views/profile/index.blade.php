@extends('system::template.admin.header')
@section('title', $title)

@section('link_href')
    {{-- Tambahkan CSS Cropper --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
    <style>
        .img-container img {
            display: block;
            max-width: 100%; /* KUNCI 1: Lebar mentok 100% container */
        }

        /* Kandang gambarnya kita kunci ukurannya */
        .img-container {
            width: 100%;
            height: 500px; /* KUNCI 2: Tinggi kita kunci di 500px */
            background-color: #333; /* Background gelap */
            overflow: hidden; /* Kalau ada yang lewat, potong! */
        }

         .cursor-pointer {
             cursor: pointer;
         }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Profil Pengguna</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profil Saya</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">

                {{-- === KOLOM KIRI: IDENTITAS === --}}
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center mb-3">
                                {{-- Menampilkan Foto Profil --}}
                                <img class="profile-user-img img-fluid img-circle shadow-sm"
                                     style="width: 140px; height: 140px; object-fit: cover; border: 3px solid #fff;"
                                     src="{{ $user->profile_photo_url }}"
                                     alt="User profile picture">
                            </div>

                            {{-- Nama & Role --}}
                            <h3 class="profile-username text-center font-weight-bold">{{ $user->name }}</h3>
                            <p class="text-muted text-center mb-4">
                                @if($formattedRoles)
                                    @foreach($formattedRoles as $role)
                                        <span class="badge {{ $role['class'] }}">{{ $role['label'] }}</span>
                                    @endforeach
                                @else
                                    <span class="badge badge-secondary">User (No Role)</span>
                                @endif
                            </p>

                            {{-- List Info Detail --}}
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <i class="fas fa-envelope mr-2 text-primary"></i> <b>Email</b>
                                    <span class="float-right text-muted">{{ $user->email }}</span>
                                </li>

                                {{-- LOGIC NIM/NIK (Sudah diatur Controller) --}}
                                <li class="list-group-item">
                                    <i class="fas fa-id-card mr-2 text-info"></i> <b>{{ $identityLabel }}</b>
                                    <span class="float-right text-muted">{{ $identityValue }}</span>
                                </li>

                                <li class="list-group-item">
                                    <i class="fas fa-building mr-2 text-success"></i> <b>Unit Kerja</b>
                                    <span class="float-right text-muted">{{ $unitKerja }}</span>
                                </li>

                                <li class="list-group-item">
                                    <i class="fas fa-calendar-alt mr-2 text-warning"></i> <b>Bergabung</b>
                                    <span class="float-right text-muted">{{ $user->created_at->format('d M Y') }}</span>
                                </li>
                            </ul>

                            {{-- Tombol Status (Hiasan) --}}
                            <div class="text-center">
                                <button class="btn {{ $accountStatus['class'] }} btn-block disabled" style="cursor: default; opacity: 1;">
                                    <i class="fas {{ $accountStatus['icon'] }} mr-1"></i> {{ $accountStatus['text'] }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Card Tambahan (Opsional: Quote atau Statistik Kerja) --}}
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> Pengumuman</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Pastikan data profil Anda selalu diperbarui. Demi keamanan, ganti password Anda secara berkala minimal 3 bulan sekali.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- === KOLOM KANAN: SETTINGS === --}}
                <div class="col-md-8">

                    {{-- Alert Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="icon fas fa-check-circle fa-2x mr-3"></i>
                                <div>
                                    <h5 class="mb-0">Berhasil!</h5>
                                    <small>{{ session('success') }}</small>
                                </div>
                            </div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="icon fas fa-ban fa-2x mr-3"></i>
                                <div>
                                    <h5 class="mb-0">Gagal!</h5>
                                    <small>{{ session('error') }}</small>
                                </div>
                            </div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="card shadow-sm">
                        <div class="card-header p-2 border-bottom-0">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#foto_profil" data-toggle="tab">
                                        <i class="fas fa-camera mr-1"></i> Foto Profil
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#security" data-toggle="tab">
                                        <i class="fas fa-lock mr-1"></i> Keamanan
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">

                                {{-- TAB 1: UPDATE FOTO --}}
                                <div class="active tab-pane" id="foto_profil">
                                    <form action="{{route('save.change-profile')}}" class="form-horizontal" method="POST" id="form-profile" enctype="multipart/form-data">
                                        @csrf

                                        <div class="callout callout-info">
                                            <h5><i class="fas fa-info"></i> Info Upload</h5>
                                            <p>Gunakan foto formal dengan rasio 1:1 (Kotak). Format: JPG/PNG. Maksimal 2MB.</p>
                                        </div>

                                        {{-- AREA 1: INPUT FILE (Muncul di Awal) --}}
                                        <div id="upload-area" class="form-group row align-items-center" style="{{ $hasPhoto ? 'display: none;' : '' }}">
                                            <label for="photoprofile" class="col-sm-3 col-form-label">Pilih Foto Baru</label>
                                            <div class="col-sm-9">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="photoprofile" name="photoprofile" accept=".jpg, .jpeg, .png">
                                                    <label class="custom-file-label" for="photoprofile">Klik untuk cari file...</label>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- AREA 2: PREVIEW HASIL CROP --}}
                                        <div id="preview-area" class="form-group row align-items-center" style="{{ $hasPhoto ? '' : 'display: none;' }}">
                                            <label class="col-sm-3 col-form-label">Foto Profil</label>
                                            <div class="col-sm-9">
                                                <div class="d-flex align-items-center">
                                                    {{-- Gambar pakai Accessor URL --}}
                                                    <img id="result-preview-img"
                                                         src="{{ $user->profile_photo_url }}"
                                                         class="img-circle border shadow-sm mr-3"
                                                         style="width: 100px; height: 100px; object-fit: cover;" alt="Preview Foto Profil">

                                                    <div>
                                                        <button type="button" class="btn btn-warning btn-sm mr-1" onclick="$('#photoprofile').click()">
                                                            <i class="fas fa-camera"></i> Ganti Foto
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-crop" style="display: none;">
                                                            <i class="fas fa-undo"></i> Batal
                                                        </button>
                                                    </div>
                                                </div>
                                                <small id="status-text" class="text-muted d-block mt-2">
                                                    {{ $hasPhoto ? 'Foto saat ini.' : 'Belum ada foto.' }}
                                                </small>
                                            </div>
                                        </div>

                                        {{-- CHECKBOX CONFIRMATION --}}
                                        <div class="form-group row">
                                            <div class="offset-sm-3 col-sm-9">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="ceklis">
                                                    <label class="custom-control-label font-weight-normal" for="ceklis">Saya yakin ingin mengubah foto profil ini.</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-4">
                                            <div class="offset-sm-3 col-sm-9">
                                                <button type="submit" id="btn-submit" class="btn btn-primary" disabled>
                                                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                {{-- TAB 2: GANTI PASSWORD --}}
                                <div class="tab-pane" id="security">
                                    <form class="form-horizontal" action="{{ route('profile.update-password') }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        {{-- 1. PASSWORD LAMA --}}
                                        <div class="form-group row">
                                            <label for="current_password" class="col-sm-3 col-form-label">Password Lama</label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                                           id="current_password" name="current_password" placeholder="Masukkan Password Lama">
                                                    <div class="input-group-append">
                                                        <div class="input-group-text cursor-pointer toggle-password" target="#current_password">
                                                            <i class="fas fa-eye"></i>
                                                        </div>
                                                    </div>
                                                    @error('current_password')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 2. PASSWORD BARU (Name diganti jadi 'password' biar match dengan controller) --}}
                                        <div class="form-group row">
                                            <label for="password" class="col-sm-3 col-form-label">Password Baru</label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                           id="password" name="password" placeholder="Minimal 8 karakter">
                                                    <div class="input-group-append">
                                                        <div class="input-group-text cursor-pointer toggle-password" target="#password">
                                                            <i class="fas fa-eye"></i>
                                                        </div>
                                                    </div>
                                                    @error('password')
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 3. KONFIRMASI PASSWORD --}}
                                        <div class="form-group row">
                                            <label for="password_confirmation" class="col-sm-3 col-form-label">Ulangi Password</label>
                                            <div class="col-sm-9">
                                                <div class="input-group">
                                                    <input type="password" class="form-control"
                                                           id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password baru">
                                                    <div class="input-group-append">
                                                        <div class="input-group-text cursor-pointer toggle-password" target="#password_confirmation">
                                                            <i class="fas fa-eye"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-4">
                                            <div class="offset-sm-3 col-sm-9">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-key mr-1"></i> Update Password
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal Cropper --}}
    <div class="modal fade" id="modal-cropper" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sesuaikan Foto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    {{-- Container Gambar --}}
                    <div class="img-container">
                        {{-- Pastikan img ini kosong src-nya saat awal, dan ID-nya benar --}}
                        <img id="image-preview" src="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-crop">
                        <i class="fas fa-crop-alt mr-1"></i> Potong & Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        $(document).ready(function () {
            // ===== Logic Last Tab =====

            // Kunci Penyimpanan di Browser
            var keyTab = 'active_tab_user_profile';

            // Halaman Dimuat cek tab yang disimpan
            var lastTab = localStorage.getItem(keyTab);
            if (lastTab) {
                $('[href="' + lastTab + '"]').tab('show');
            }

            // User Klik Tab simpan ID-nya
            $('a[data-toggle="pill"], a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var currentTab = $(e.target).attr('href');
                localStorage.setItem(keyTab, currentTab);
            });
            // ===== End Logic Last Tab =====

            // ===== Logic Toggle Password =====
            $('.toggle-password').click(function() {
                // Ambil target input dari attribute 'target'
                var inputSelector = $(this).attr('target');
                var $input = $(inputSelector);
                var $icon = $(this).find('i');

                // Cek tipe saat ini, lalu tukar
                if ($input.attr('type') === 'password') {
                    $input.attr('type', 'text');
                    $icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $input.attr('type', 'password');
                    $icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            // ===== End Logic Toggle Password =====

            // ===== Logic Update Foto Profil =====
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            // VARIABEL
            var $modal = $('#modal-cropper');
            var image = document.getElementById('image-preview');
            var cropper;
            var $inputImage = $('#photoprofile');

            // Simpan URL Foto Server Asli (Initial State)
            var originalPhotoUrl = $('#result-preview-img').attr('src');
            // Cek apakah awalnya user punya foto? (Kalau src bukan ui-avatars/default, anggap punya)
            var hasInitialPhoto = !originalPhotoUrl.includes('ui-avatars.com') && originalPhotoUrl !== '';

            // === 1. SAAT FILE DIPILIH ===
            $inputImage.change(function (event) {
                var files = event.target.files;
                if (files && files.length > 0) {
                    var file = files[0];
                    if (!file.type.match('image.*')) {
                        Swal.fire('Error', 'Harap pilih file gambar!', 'error');
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        image.src = reader.result;
                        $modal.modal('show');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // === 2. INIT CROPPER ===
            $modal.on('shown.bs.modal', function () {
                cropper = new Cropper(image, {
                    aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 1, guides: true, center: true, cropBoxMovable: false, cropBoxResizable: true, toggleDragModeOnDblclick: false,
                });
            }).on('hidden.bs.modal', function () {
                if (cropper) { cropper.destroy(); cropper = null; }
                // Kalau user tutup modal tanpa crop (batal pilih file), reset input
                if (!$('#btn-cancel-crop').is(':visible')) {
                    $inputImage.val('');
                }
            });

            // === 3. EKSEKUSI CROP ===
            $('#btn-crop').click(function () {
                var canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
                canvas.toBlob(function (blob) {
                    // Update Input File
                    const myFile = new File([blob], "avatar.jpg", { type: "image/jpeg", lastModified: new Date() });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(myFile);
                    $inputImage[0].files = dataTransfer.files;

                    // === UPDATE UI SETELAH CROP ===
                    // 1. Tampilkan Preview Baru
                    $('#result-preview-img').attr('src', canvas.toDataURL());

                    // 2. Tampilkan Area Preview (Kalau tadinya hidden)
                    $('#upload-area').hide();
                    $('#preview-area').fadeIn();

                    // 3. Munculkan Tombol BATAL & Ubah Status
                    $('#btn-cancel-crop').show(); // Munculin tombol batal
                    $('#status-text').text('Foto baru siap disimpan.').removeClass('text-muted').addClass('text-success');

                    // 4. Update Sidebar Kiri (Preview Live)
                    $('.profile-user-img').attr('src', canvas.toDataURL());

                    // 5. Enable Checkbox
                    $('#ceklis').prop('disabled', false);

                    $modal.modal('hide');
                }, 'image/jpeg', 0.8);
            });

            // === LOGIKA TOMBOL BATAL (RESET) ===
            $('#btn-cancel-crop').click(function() {
                // Reset Input File
                $inputImage.val('');

                // Balikkan Foto Sidebar ke Asli
                $('.profile-user-img').attr('src', originalPhotoUrl);

                // Logic Percabangan:
                if (hasInitialPhoto) {
                    // KASUS A: Punya foto lama -> Balikin ke foto lama
                    $('#result-preview-img').attr('src', originalPhotoUrl);
                    $('#status-text').text('Foto saat ini.').removeClass('text-success').addClass('text-muted');
                    // Sembunyikan tombol batal (karena sudah balik ke ori)
                    $(this).hide();
                } else {
                    // KASUS B: User baru (Gak punya foto) -> Balik ke Upload Area
                    $('#preview-area').hide();
                    $('#upload-area').fadeIn();
                }

                // Matikan tombol simpan & checkbox
                $('#ceklis').prop('checked', false).prop('disabled', true).trigger('change');
            });

            // === LOGIKA SIMPAN ===
            $('#ceklis').on('change', function () {
                $('#btn-submit').prop('disabled', !$(this).is(':checked'));
            });

            $('#btn-submit').click(function (e) {
                e.preventDefault();
                Swal.fire({
                    title: "Konfirmasi",
                    text: "Simpan foto profil baru ini?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Simpan",
                }).then((result) => {
                    if (result.isConfirmed) $('#form-profile').submit();
                });
            });
            // ===== End Logic Update Foto Profil =====

        });
    </script>
@endsection
