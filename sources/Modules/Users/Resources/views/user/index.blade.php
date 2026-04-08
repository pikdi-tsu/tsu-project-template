@extends('system::template/admin/header')
@section('title', $title)

@section('content')
    <div class="card card-outline card-info"> <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users-cog mr-1"></i> Data Pengguna Modul
            </h3>
            <div class="card-tools">
                {{-- FITUR SYNC USER --}}
                @can('system:user:create')
                    <form action="{{ route('system.user.sync') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm btn-sync" title="Tarik data terbaru">
                            <i class="fas fa-sync-alt"></i> Update Users Lokal
                        </button>
                    </form>
                @else
                    <span class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.7;" title="Anda tidak memiliki akses ke action ini">
                        <i class="fas fa-lock mr-1"></i> Update Users Lokal (No Access)
                    </span>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-light border">
                <i class="fas fa-info-circle text-info"></i>
                Halaman ini hanya untuk <b>Monitoring</b>. Penambahan User & Role dilakukan secara otomatis melalui SSO atau Admin Pusat.
            </div>

            <table class="table table-hover text-nowrap" id="table-user-modul" style="width: 100%">
                <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="5%">Avatar</th>
                    <th>Nama User</th>
                    <th>Email</th>
                    <th>Role di Modul Ini</th>
                    <th width="10%">Aksi</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- ================= MODAL EDIT (AJAX CONTAINER) ================= --}}
    <div class="modal fade" id="modal-edit">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modal-edit-content">
                {{-- Loading State --}}
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Sedang mengambil data...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var table = $('#table-user-modul').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('system.user.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'avatar', name: 'avatar', orderable: false, searchable: false },
                    { data: 'name', name: 'name' }, // Read Only
                    { data: 'email', name: 'email' }, // Read Only
                    { data: 'roles', name: 'roles' }, // Read Only
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Logic Sync Roles Homebase
            $('body').on('click', '.btn-sync', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Sinkronisasi User?',
                    html: "Sistem akan mengambil data User terbaru dari <b>Homebase</b>.<br><small class='text-muted'>Pastikan server Homebase sedang aktif.</small>",
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

            // Event Listener Tombol Edit
            $('body').on('click', '.btn-edit', function(e) {
                e.preventDefault();
                var url = $(this).attr('href') || $(this).data('url');

                if (!url) {
                    console.error('URL Edit tidak ditemukan!');
                    return;
                }

                $('#modal-edit').modal('show')
                // Reset konten modal ke loading state setiap kali dibuka
                $('#modal-edit-content').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p>Loading...</p></div>');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#modal-edit-content').html(response);
                    },
                    error: function() {
                        $('#modal-edit-content').html('<div class="alert alert-danger m-3">Gagal mengambil data menu.</div>');
                    }
                });
            });

            // Modal Delete/Kick
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Keluarkan User?',
                    text: "User harus login ulang untuk masuk ke modul ini.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-power-off"></i> Ya, Kick!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
