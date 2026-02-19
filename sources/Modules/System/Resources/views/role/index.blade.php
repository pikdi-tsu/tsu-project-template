@extends('system::template/admin/header')
@section('title', $title)

@section('content')
    <div class="card card-outline card-purple">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-shield mr-1"></i> Role Matrix</h3>
            <div class="card-tools">
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
            <div class="alert alert-light border">
                <i class="fas fa-info-circle text-purple"></i>
                <b>Catatan:</b> Nama Role tidak bisa diedit disini karena menginduk ke Homebase. Gunakan tombol <b>Sync</b> jika ada role baru di Homebase.
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

            // Logic Modal Edit (Sama kayak modul system)
            $('body').on('click', '.btn-edit', function(e) {
                e.preventDefault();
                $('#modal-edit').modal('show');
                $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Mengambil Matrix Permission...</p></div>`);

                $.ajax({
                    url: $(this).attr('href'),
                    type: 'GET',
                    success: function(res) { $('#modal-edit-content').html(res); }
                });
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
        });
    </script>
@endsection
