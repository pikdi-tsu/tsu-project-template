@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">Data Dosen & Tendik</h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-primary btn-modal btn-sm shadow-sm" data-url="#" title="Sync Data dari HRIS">
                    <i class="fas fa-sync-alt mr-1"></i> Sync API HRIS
                </button>
            </div>
        </div>

        <div class="card-body">
            <table id="table-karyawan" class="table table-bordered table-striped w-100">
                <thead>
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="25%">Nama Lengkap & Kontak</th>
                    <th width="15%">Identitas</th>
                    <th width="20%">Jabatan</th>
                    <th width="15%">Unit</th>
                    <th width="10%">Status & Akun</th>
                    <th width="10%" class="text-center">Aksi</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
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
        // Inisialisasi Yajra DataTables Karyawan
        $('#table-karyawan').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.data-karyawan.json') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'nama_lengkap', name: 'nama'},
                {data: 'identitas', name: 'nik'}, // Bisa ditambah name: 'nidn' di backend kalau mau multi-search
                {data: 'jabatan', name: 'jabatan_struktural'},
                {data: 'unit', name: 'unit', defaultContent: '-'}, // Langsung tarik dari DB
                {data: 'status_karyawan', name: 'status_karyawan'},
                {data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center'},
            ]
        });

        $('body').on('click', '.btn-detail', function(e) {
            e.preventDefault();
            var url = $(this).data('url');

            $('#modal-edit').modal('show'); // Kita pinjam modal-edit yang udah ada aja biar gampang
            $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-info"></div><p>Memuat Detail Data...</p></div>`);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#modal-edit-content').html(res);
                },
                error: function(xhr) {
                    $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat detail. Error: ${xhr.status}</div>`);
                }
            });
        });

        // Logic Create & Sync
        $('body').on('click', '.btn-modal', function(e) {
            e.preventDefault();
            var url = $(this).data('url');

            if(url === '#') return Swal.fire('Info', 'Fitur ini segera hadir!', 'info');

            $('#modal-edit').modal('show');
            $('#modal-edit-content').html(`<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>Memuat Form...</p></div>`);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#modal-edit-content').html(res);
                },
                error: function(xhr) {
                    $('#modal-edit-content').html(`<div class="text-center text-danger p-5">Gagal memuat form. Error: ${xhr.status}</div>`);
                }
            });
        });

        // Logic Modal Edit
        $('body').on('click', '.btn-edit', function(e) {
            e.preventDefault();

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


    </script>
@endsection
