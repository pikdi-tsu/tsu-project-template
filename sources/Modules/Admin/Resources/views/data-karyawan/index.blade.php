@extends('system::template.admin.header')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-4">Data Dosen & Tendik</h3>

            <div class="d-flex gap-2 ml-auto">
                <button type="button" class="btn btn-success btn-modal btn-sm" data-url="{{ route('admin.data-karyawan.create') }}" title="Tambah Pegawai">
                    <i class="fas fa-plus"></i> Tambah Pegawai
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
                    <th width="20%">Keilmuan Inti</th>
                    <th width="25%">Jabatan</th>
                    <th width="25%">Status</th>
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
                {data: 'keilmuan_inti', name: 'keilmuan_inti', defaultContent: '-'}, // Langsung tarik dari DB
                {data: 'jabatan', name: 'jabatan_struktural'},
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

        // Logic aktif dan non-aktif data
        $('body').on('click', '.btn-toggle-status', function(e) {
            e.preventDefault();

            var form = $(this).closest('form');
            var name = $(this).data('name');
            var action = $(this).data('action'); // Bakal otomatis baca 'aktifkan' atau 'nonaktifkan' dari tombol

            // Setting dinamis berdasarkan tombol apa yang diklik
            var titleTxt = action === 'aktifkan' ? 'Aktifkan Karyawan?' : 'Nonaktifkan Pegawai?';
            var htmlTxt  = action === 'aktifkan'
                ? "Anda akan mengaktifkan kembali status pegawai: <b>" + name + "</b>.<br><small class='text-success'>Karyawan akan kembali aktif di sistem HRIS.</small>"
                : "Anda akan menonaktifkan status pegawai: <b>" + name + "</b>.<br><small class='text-warning'>Data tidak dihapus, namun akun SSO akan diputus.</small>";
            var iconBtn  = action === 'aktifkan' ? '<i class="fas fa-user-check"></i> Ya, Aktifkan!' : '<i class="fas fa-user-slash"></i> Ya, Nonaktifkan!';
            var colorBtn = action === 'aktifkan' ? '#28a745' : '#d33'; // Hijau untuk aktif, Merah untuk nonaktif

            Swal.fire({
                title: titleTxt,
                html: htmlTxt,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: colorBtn,
                cancelButtonColor: '#3085d6',
                confirmButtonText: iconBtn,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang mengubah status karyawan...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });

                    // Tembak formnya ke Controller!
                    form.submit();
                }
            });
        });
    </script>
@endsection
