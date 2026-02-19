@extends('system::template/admin/header')
@section('title', $title)

@section('content')
    <div class="card card-outline card-purple">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-shield mr-1"></i> Role Matrix</h3>
            <div class="card-tools">
                {{-- Tombol Panduan --}}
                <button type="button" class="btn btn-outline-info mr-2" data-toggle="collapse" data-target="#panduanRole" title="Klik untuk lihat panduan">
                    <i class="fas fa-question-circle"></i> Panduan
                </button>
                {{-- TOMBOL CREATE --}}
                @can('system:role:create')
                    <a href="{{ route('system.role.create') }}" class="btn btn-success btn-sm btn-create mr-1" title="Buat Role Lokal Baru">
                        <i class="fas fa-plus"></i> Tambah Role
                    </a>
                @endcan
                {{-- TOMBOL SYNC --}}
                @can('system:role:create')
                    <form action="{{ route('system.role.sync') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm btn-sync" title="Tarik data terbaru">
                            <i class="fas fa-sync-alt"></i> Sync Roles dari Homebase
                        </button>
                    </form>
                @else
                    <span class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.7;" title="Anda tidak memiliki akses ke action ini">
                        <i class="fas fa-lock mr-1"></i> Sync Roles (No Access)
                    </span>
                @endcan
            </div>
        </div>
        <div class="card-body">
            {{-- Panduan Manajemen Roles --}}
            <div class="collapse" id="panduanRole">
                <div class="alert bg-dark text-white shadow-lg mb-3 border-0" style="background: linear-gradient(45deg, #343a40, #495057);">
                    <div class="d-flex align-items-start">
                        <div class="mr-3 mt-1">
                            <i class="fas fa-lightbulb fa-2x text-warning"></i>
                        </div>
                        <div class="flex-fill">
                            <h6 class="font-weight-bold text-warning mb-2">Panduan Matrix Role:</h6>

                            <div class="row">
                                {{-- KOLOM 1: TIPE ROLE --}}
                                <ul class="mb-0 pl-3 small" style="list-style-type: none; padding-left: 0;">
                                    <li class="mb-2">
                                        <span class="badge badge-info p-1" style="width: 110px;">
                                            <i class="fas fa-globe mr-1"></i> Global
                                        </span>
                                        : Role dari Homebase (Pusat). <span class="text-white-50">Nama & Hapus Terkunci.</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge badge-warning text-dark p-1" style="width: 110px;">
                                            <i class="fas fa-lock mr-1"></i> Lokal Inti
                                        </span>
                                        : Role Bawaan (Admin/Dosen). <span class="text-white-50">Bisa edit izin, tapi <b>Nama & Hapus Terkunci</b>.</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge badge-secondary p-1" style="width: 110px;">
                                            <i class="fas fa-user-tag mr-1"></i> Lokal Custom
                                        </span>
                                        : Role buatan sendiri. <span class="text-white-50">Bebas Edit Nama, Izin, & Hapus.</span>
                                    </li>
                                </ul>

                                {{-- KOLOM 2: FITUR KHUSUS --}}
                                <div class="col-md-6">
                                    <ul class="mb-0 pl-3 small" style="list-style-type: none; padding-left: 0;">
                                        <li class="mb-2">
                                            <span class="badge badge-success" style="width: 120px;">
                                                <i class="fas fa-crown mr-1"></i> Full Access
                                            </span>
                                            : Hak akses mutlak (Super Admin). <span class="text-white-50">Mengabaikan semua setting permission.</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge badge-primary" style="width: 120px;">
                                                <i class="fas fa-sync-alt mr-1"></i> Sync
                                            </span>
                                            : Gunakan tombol ini jika ada role baru yang ditambahkan di Homebase.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Close --}}
                        <button type="button" class="close text-white opacity-1" data-toggle="collapse" data-target="#panduanRole">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
            <table class="table table-hover table-striped" id="table-role" style="width: 100%">
                <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Role</th>
                    <th>Jumlah Izin (Permission)</th>
                    <th>Tipe Role</th>
                    <th width="15%">Aksi</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- MODAL EDIT CONTAINER --}}
    <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" id="modal-edit-content">
                {{-- Loading State --}}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var table = $('#table-role').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('system.role.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'permissions_count', name: 'permissions_count', searchable: false },
                    { data: 'is_identity_badge', name: 'is_identity_badge' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Logic Sync Roles Homebase
            $('body').on('click', '.btn-sync', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Sinkronisasi Role?',
                    html: "Sistem akan mengambil data Role terbaru dari <b>Homebase</b>.<br><small class='text-muted'>Pastikan server Homebase sedang aktif.</small>",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-sync fa-spin"></i> Ya, Sync Sekarang!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    backdrop: `rgba(0,0,0,0.4) left top no-repeat`,
                    showClass: { popup: 'animate__animated animate__fadeInDown' },
                    hideClass: { popup: 'animate__animated animate__fadeOutUp' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        let timerInterval;
                        Swal.fire({
                            title: 'Sedang Menghubungkan...',
                            html: 'Mohon tunggu, sedang meminta data ke Homebase.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        form.submit();
                    }
                });
            });

            // Logic Modal Create
            $('body').on('click', '.btn-create', function(e) {
                e.preventDefault();
                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-success"></div><p>Memuat Form...</p></div>`);

                $.ajax({
                    url: $(this).attr('href'),
                    type: 'GET',
                    success: function(res) {
                        $('#modal-edit-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat form.</div>`);
                    }
                });
            });

            // Logic Modal Edit
            $('body').on('click', '.btn-edit', function(e) {
                e.preventDefault();

                // Cekdata-url
                var url = $(this).data('url');
                if (!url) {
                    url = $(this).attr('href');
                }

                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Mengambil Data...</p></div>`);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-edit-content').html(res);
                    },
                    error: function(xhr) {
                        $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal mengambil data. Error: ${xhr.status}</div>`);
                    }
                });
            });

            // Logic delete
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();

                var form = $(this).closest('form');
                var name = $(this).closest('tr').find('td:eq(1)').text();

                Swal.fire({
                    title: 'Hapus Role Lokal?',
                    html: "Anda akan menghapus role: <b>" + name + "</b>.<br><small class='text-danger'>Data yang dihapus tidak dapat dikembalikan!</small>",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang menghapus data',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading() }
                        });

                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
