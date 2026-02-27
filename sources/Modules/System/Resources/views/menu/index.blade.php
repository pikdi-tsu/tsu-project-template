@extends('system::template/admin/header')
@section('title', $title)
@section('link_href')
@endsection

@section('content')
    {{-- SECTION UTAMA: Pake class 'card' (AdminLTE 3) biar ada jarak padding --}}
    <div class="card card-primary card-outline">

        {{-- HEADER --}}
        <div class="card-header">
            <h3 class="card-title">Manajemen Menu</h3>
            <div class="card-tools">
                {{-- Tombol Dropdown Panduan--}}
                <button class="btn btn-outline-info btn-sm btn-flat" type="button" data-toggle="collapse" data-target="#panduanInfo" aria-expanded="false" aria-controls="panduanInfo">
                    <i class="fas fa-info-circle mr-1"></i> Bantuan: Cara Baca Tabel
                </button>
                @can('system:menu:create')
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-create">
                        <i class="fa fa-plus"></i> Tambah Menu
                    </button>
                @else
                    <span class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.7;" title="Anda tidak memiliki akses ke action ini">
                        <i class="fas fa-lock mr-1"></i> Tambah Menu (No Access)
                    </span>
                @endcan
            </div>
        </div>

        {{-- BODY (Tabel ada disini) --}}
        <div class="card-body">
            {{-- Panduan Hirarki --}}
            <div class="collapse" id="panduanInfo">
                <div class="callout callout-info mb-4 shadow-sm">
                    <h5 class="text-info text-sm font-weight-bold">Panduan Hierarki Menu</h5>
                    <ul class="mb-0 mt-2 text-sm pl-4">
                        <li class="mb-1">
                            <span class="badge badge-secondary px-2 py-1">Row Abu-abu</span> :
                            <b>Menu Utama / Level 0</b> (Contoh: Dashboard, Master Data).
                        </li>
                        <li class="mb-1">
                            <span class="text-primary font-weight-bold">Nama Menu Biru</span> :
                            <b>Sub-Menu Level 1</b> (Anak Langsung).
                        </li>
                        <li>
                            <span class="text-muted">Nama Menu Abu-abu</span> :
                            <b>Sub-Menu Level 2 dst</b> (Cucu/Cicit).
                        </li>
                    </ul>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="table-menu" style="width: 100%;">
                    <thead class="bg-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Menu</th>
                        <th>Icon</th>
                        <th>Route</th>
                        <th>Permission</th>
                        <th>Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    {{-- Diisi lewat AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= MODAL CREATE ================= --}}
    <div class="modal fade" id="modal-create">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Menu Baru</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('system.menu.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Menu <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Data Mahasiswa">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Icon (FontAwesome)</label>
                                    <div class="input-group">
                                        <input type="text" name="icon" class="form-control" placeholder="Default: fas fa-box">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-info"></i></span>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Kosongkan untuk default: <i class="fas fa-box mx-1"></i><code>fas fa-box</code>
                                    </small>
                                    <small>
                                        <a href="https://fontawesome.com/search?ic=free-collection" target="_blank" class="text-info">
                                            <i class="fas fa-external-link-alt mr-1"></i> Lihat Referensi Icon Disini
                                        </a>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Route Laravel</label>
                                    <input type="text" name="route" id="input_route" class="form-control" placeholder="system.users.index">
                                    <small class="text-muted">Isi <code>#</code> atau kosongkan jika ini menu Parent (Dropdown).</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Permission</label>
                                    <select name="permission_name" id="input_permission" class="form-control select2" style="width: 100%;">
                                        <option value="">-- Public --</option>
                                        @if(isset($permissions))
                                            @foreach($permissions as $perm)
                                                <option value="{{ $perm }}">{{ $perm }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small id="help_permission" class="text-danger" style="display: none;">
                                        <i class="fas fa-ban"></i> Permission dimatikan untuk Menu Induk (Folder).
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Parent Menu</label>
                                    <select name="parent_id" id="input_parent" class="form-control select2" style="width: 100%;">
                                        <option value="">-- Jadikan Utama --</option>
                                        {{-- Pastikan variabel $parents dikirim dari controller --}}
                                        @if(isset($parents))
                                            @foreach($parents as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Urutan</label>
                                    <input type="number" name="order" class="form-control" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label> <br>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="create_isactive" name="isactive" value="1" checked>
                                        <label class="custom-control-label" for="create_isactive">Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
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
            // Hapus instance lama
            if ($.fn.DataTable.isDataTable('#table-menu')) {
                $('#table-menu').DataTable().destroy();
            }

            // Init DataTables Baru
            var table = $('#table-menu').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('system.menu.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    {
                        data: 'icon',
                        name: 'icon',
                        render: function(data) {
                            return data ? '<i class="'+data+'"></i>' : '-';
                        }
                    },
                    { data: 'route', name: 'route' },
                    { data: 'permission', name: 'permission_name' },
                    { data: 'status', name: 'isactive' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // AUTO DISABLE PERMISSION
            function adjustCreatePermission() {
                var routeVal  = $('#input_route').val();
                var parentVal = $('#input_parent').val();
                var permInput = $('#input_permission');
                var helpText  = $('#help_permission');

                // Logic parent menu
                if ( (parentVal === '' || parentVal == null) && (routeVal === '' || routeVal === '#') ) {
                    permInput.val('').trigger('change');
                    permInput.prop('disabled', true);
                    helpText.show();
                } else {
                    permInput.prop('disabled', false);
                    helpText.hide();
                }
            }

            $('#input_route').on('keyup change', function() {
                adjustCreatePermission();
            });

            $('#input_parent').on('change', function() {
                adjustCreatePermission();
            });

            adjustCreatePermission();

            $('form').on('submit', function() {
                $('#input_permission').prop('disabled', false);
            });

            // Event Listener Tombol Edit
            $('body').on('click', '.btn-edit', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');

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

            // Event Listener Tombol Delete
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Hapus Menu Ini?',
                    text: "Data yang dihapus tidak bisa kembali lagi!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-danger btn-lg mr-2',
                        cancelButton: 'btn btn-secondary btn-lg',
                        popup: 'animated rubberBand'
                    },
                    backdrop: `rgba(0,0,0,0.4) left top no-repeat`
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Efek Loading saat menghapus
                        Swal.fire({
                            title: 'Sedang Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });

                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
