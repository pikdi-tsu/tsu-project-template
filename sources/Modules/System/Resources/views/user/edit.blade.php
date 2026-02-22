@extends('system::template/admin/header')
@section('title', $title)

@section('content')
    <div class="container-fluid py-3">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Pengguna</h1>
            <a href="{{ route('system.user.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>

        <form action="{{ route('system.user.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">

                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-body text-center pt-5 pb-4">
                            {{-- AVATAR --}}
                            <img class="img-profile rounded-circle mb-3 shadow-sm"
                                 src="{{ $user->profile_photo_url }}"
                                 style="width: 120px; height: 120px; border: 4px solid #fff;" alt="Foto Profil User">

                            {{-- NAMA & IDENTITAS UTAMA --}}
                            <h5 class="font-weight-bold text-dark mb-1">{{ $user->name }}</h5>
                            <p class="text-muted small mb-2">{{ $user->email }}</p>

                            {{-- BADGES --}}
                            <div class="mb-3">
                                @if($isSso)
                                    <span class="badge badge-pill badge-info px-3 py-1"><i class="fas fa-link mr-1"></i> SSO User</span>
                                @else
                                    <span class="badge badge-pill badge-secondary px-3 py-1"><i
                                            class="fas fa-user mr-1"></i> Local User</span>
                                @endif

                                {{-- Status Aktif Badge --}}
                                @if($user->isactive)
                                    <span class="badge badge-pill badge-success px-3 py-1"><i
                                            class="fas fa-check mr-1"></i> Aktif</span>
                                @else
                                    <span class="badge badge-pill badge-danger px-3 py-1"><i
                                            class="fas fa-ban mr-1"></i> Non-Aktif</span>
                                @endif
                            </div>
                        </div>

                        {{-- LIST DATA KREDENSIAL (READ ONLY) --}}
                        <div class="card-body p-0 border-top">
                            <div class="list-group list-group-flush">

                                {{-- Username --}}
                                <div
                                    class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                    <div class="text-muted small"><i class="fas fa-id-badge mr-2"></i> Username</div>
                                    <div class="font-weight-bold text-dark">{{ $user->username }}</div>
                                </div>

                                {{-- Unit Kerja (Dummy/Placeholder) --}}
                                <div
                                    class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                    <div class="text-muted small"><i class="fas fa-building mr-2"></i> Unit Kerja</div>
                                    <div class="text-right">
                                        @if($user->hasRole('dosen'))
                                            <span class="small">Dosen Pengajar</span>
                                        @elseif($user->hasRole('mahasiswa'))
                                            <span class="small">Mahasiswa Aktif</span>
                                        @else
                                            <span class="small text-muted">-</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Bergabung --}}
                                <div
                                    class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                    <div class="text-muted small"><i class="fas fa-calendar-alt mr-2"></i> Bergabung
                                    </div>
                                    <span class="small text-dark">{{ $user->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- BAGIAN EDITABLE (ROLES & STATUS) --}}
                        <div class="card-footer bg-light p-4">
                            <h6 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-user-shield mr-1"></i> Akses Role
                            </h6>

                            {{-- Input Role (Tetap Ada) --}}
                            <div class="form-group mb-3">
                                <label class="small text-uppercase font-weight-bold text-muted">Role Aplikasi</label>
                                <select name="roles[]" class="form-control select2" multiple="multiple"
                                        data-placeholder="Pilih Role...">
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" {{ in_array($role, $userRole) ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Switch Status Aktif --}}
                            {{--                            @if(auth()->id() !== $user->id)--}}
                            {{--                                <div class="form-group mb-0">--}}
                            {{--                                    <div class="custom-control custom-switch">--}}
                            {{--                                        <input type="checkbox" class="custom-control-input" id="isactive" name="isactive"--}}
                            {{--                                            {{ $user->isactive ? 'checked' : '' }}>--}}
                            {{--                                        <label class="custom-control-label small font-weight-bold text-dark" for="isactive">--}}
                            {{--                                            Status Akun Aktif--}}
                            {{--                                        </label>--}}
                            {{--                                    </div>--}}
                            {{--                                    <small class="text-muted pl-4">Matikan untuk memblokir login user ini.</small>--}}
                            {{--                                </div>--}}
                            {{--                            @endif--}}
                        </div>
                    </div>

                    {{-- Tombol Bantuan Kecil (Opsional) --}}
                    @if(!$isSso)
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-link btn-sm text-secondary"
                                    onclick="alert('Fitur ubah password manual user lokal bisa ditambahkan di sini nanti.')">
                                <i class="fas fa-key mr-1"></i> Ubah Password Manual?
                            </button>
                        </div>
                    @endif
                </div>

                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">

                        {{-- 1. LOOPING HEADER TAB --}}
                        <div class="card-header py-3">
                            <ul class="nav nav-pills card-header-pills" id="profilTab" role="tablist">
                                @foreach($formConfig as $tabKey => $tabData)
                                    <li class="nav-item">
                                        <a class="nav-link font-weight-bold {{ $loop->first ? 'active' : '' }}"
                                           id="{{ $tabKey }}-tab" data-toggle="tab" href="#{{ $tabKey }}" role="tab">
                                            {{ $tabData['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="card-body">

                            {{-- SETUP DATA PROFIL (Existing Data) --}}
                            @php
                                $profil = $user->hasRole('mahasiswa') ? $user->mahasiswa : $user->dosenTendik;
                            @endphp
                            <div class="tab-content pt-4" id="profilTabContent">

                                {{-- 2. LOOPING CONTENT TAB --}}
                                @foreach($formConfig as $tabKey => $tabData)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                         id="{{ $tabKey }}" role="tabpanel">
                                        <div class="row">

                                            {{-- 3. LOOPING FIELDS --}}
                                            @foreach($tabData['fields'] as $field)
                                                @php
                                                    // Logic Value
                                                    $dbValue = optional($profil)->{$field['name']};

                                                    if(empty($dbValue) && $field['name'] === 'nim') {
                                                        $dbValue = $user->username;
                                                    }

                                                    $val = old($field['name'], $dbValue);

                                                    // Logic Readonly
                                                    $isReadonly = isset($field['readonly']) && $field['readonly'] === true;
                                                    $attrReadonly = $isReadonly ? 'readonly' : '';
                                                    $bgClass      = $isReadonly ? 'bg-light' : '';
                                                    $attrDisabled = $isReadonly ? 'disabled' : '';

                                                    // Logic error validasi
                                                    $hasError = $errors->has($field['name']);
                                                    $errorClass = $hasError ? 'is-invalid' : '';
                                                @endphp

                                                <div class="col-md-{{ $field['col_size'] ?? 12 }} form-group mb-3">
                                                    <label class="font-weight-bold small text-uppercase text-muted"
                                                           for="{{ $field['name'] }}">
                                                        {{ $field['label'] }}
                                                        {!! isset($field['required']) ? '<span class="text-danger">*</span>' : '' !!}
                                                        @if($isReadonly)
                                                            <i class="fas fa-lock ml-1 text-warning"
                                                               title="Data Terkunci"></i>
                                                        @endif
                                                    </label>

                                                    {{-- RENDER INPUT --}}
                                                    @if($field['type'] === 'textarea')
                                                        <textarea name="{{ $field['name'] }}" id="{{ $field['name'] }}"
                                                                  class="form-control {{ $bgClass }}"
                                                                  rows="3" {{ $attrReadonly }}>{{ $val }}</textarea>
                                                    @elseif($field['type'] === 'select')
                                                        <select name="{{ $field['name'] }}" id="{{ $field['name'] }}"
                                                                class="form-control {{ $bgClass }}" {{ $attrDisabled }}>
                                                            <option value="">- Pilih -</option>
                                                            @foreach($field['options'] as $optVal => $optLabel)
                                                                <option
                                                                    value="{{ $optVal }}" {{ $val === $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if($isReadonly)
                                                            <input type="hidden" name="{{ $field['name'] }}"
                                                                   id="{{ $field['name'] }}" value="{{ $val }}">
                                                        @endif
                                                    @else
                                                        {{-- Input Text/Date/Number --}}
                                                        <input type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                                                               id="{{ $field['name'] }}"
                                                               class="form-control {{ $bgClass }}" value="{{ $val }}"
                                                            {{ isset($field['required']) ? 'required' : '' }}
                                                            {{ $attrReadonly }}>
                                                    @endif
                                                    {{-- Pesan Error Validasi --}}
                                                    @error($field['name'])
                                                    <div class="invalid-feedback font-weight-bold">
                                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                                    </div>
                                                    @enderror
                                                    @if($isReadonly)
                                                        <small class="text-muted" style="font-size: 0.7em;">Data ini
                                                            dikunci oleh sistem.</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- 4. TOMBOL NEXT / PREV / SAVE (DINAMIS) --}}
                                        <div class="d-flex justify-content-between mt-4 border-top pt-3">
                                            {{-- Tombol Previous (Kecuali Tab Pertama) --}}
                                            @if(!$loop->first)
                                                @php
                                                    // Cari Key Tab Sebelumnya
                                                    $keys = array_keys($formConfig);
                                                    $prevKey = $keys[$loop->index - 1];
                                                @endphp
                                                <button type="button" class="btn btn-secondary btn-prev"
                                                        data-target="#{{ $prevKey }}">
                                                    <i class="fas fa-arrow-left ml-1"></i> Kembali
                                                </button>
                                            @else
                                                <div></div> {{-- Spacer biar layout gak geser --}}
                                            @endif

                                            {{-- Tombol Next / Save --}}
                                            @if(!$loop->last)
                                                @php
                                                    // Cari Key Tab Berikutnya
                                                    $keys = array_keys($formConfig);
                                                    $nextKey = $keys[$loop->index + 1];
                                                @endphp
                                                <button type="button" class="btn btn-primary btn-next"
                                                        data-target="#{{ $nextKey }}">
                                                    Lanjut <i class="fas fa-arrow-right ml-1"></i>
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-warning btn-icon-split">
                                                    <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                                                    <span class="text">Simpan Perubahan</span>
                                                </button>
                                            @endif
                                        </div>

                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            // Init Select2
            $('.select2').select2({theme: 'bootstrap4', width: '100%'});

            // Logic Tombol Next/Prev
            $('.btn-next, .btn-prev').click(function () {
                var targetPanelId = $(this).data('target');

                // Pindah Tab
                $('a[data-toggle="tab"][href="' + targetPanelId + '"]').tab('show');

                // Auto scroll
                $('html, body').animate({
                    scrollTop: 0
                }, 500); // 500ms biar smooth, gak kaget
            });

            // Auto-Focus ke Tab yang Error (Tetap sama)
            if ($('.is-invalid').length > 0) {
                var invalidTabId = $('.is-invalid').first().closest('.tab-pane').attr('id');
                $('a[href="#' + invalidTabId + '"]').tab('show');
            }
        });
    </script>
@endsection
