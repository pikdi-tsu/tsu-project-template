@extends('system::template/admin/header')
@section('title', $title)

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Daftar Hak Akses (Permission)</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="table-permission" style="width: 100%">
                        <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Permission (Key)</th>
                            <th width="10%">Guard</th>
                            <th width="15%">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CARD FORM (KANAN) - MULTIFUNGSI CREATE/EDIT --}}
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title" id="form-title">Tambah Permission Baru</h3>
                </div>
                <form action="{{ route('system.permission.store') }}" method="POST" id="form-permission">
                    @csrf
                    <div id="method-put"></div> {{-- Wadah untuk @method('PUT') saat edit --}}

                    <div class="card-body">
                        <div class="form-group">
                            <label>Nama Permission <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="input-name" class="form-control" placeholder="contoh: siakad:krs:approve" required>
                            <small class="text-muted">
                                Format saran: <code>modul:fitur:aksi</code> atau <code>aplikasi:fitur:aksi</code><br>
                            </small>
                            <div class="alert alert-light border mt-2 mb-0 p-2" style="font-size: 0.85rem; color: #555;">
                                <i class="fas fa-exclamation-circle text-info mr-1"></i>
                                Format Wajib: <code>[Modul/App]:[Fitur]:[Aksi]</code>
                                <br>
                                <span class="ml-4 text-muted">Contoh: <b>siakad:krs:input</b> atau <b>homebase:role:view</b></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        @can('system:permission:create')
                            <button type="submit" class="btn btn-success" id="btn-submit">Simpan</button>
                        @else
                            <span id="span-submit" class="badge badge-secondary p-2 shadow-sm" style="cursor: not-allowed; opacity: 0.7;" title="Anda tidak memiliki akses ke action ini">
                                <i class="fas fa-lock mr-1"></i> Simpan (No Access)
                            </span>
                        @endcan
                        @can('system:permission:edit')
                            <button type="button" class="btn btn-default btn-reset d-none">Batal Edit</button>
                            <button type="submit" class="btn btn-warning d-none" id="btn-edit">Update</button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var table = $('#table-permission').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('system.permission.json') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'guard_name', name: 'guard_name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // LOGIC EDIT
            $('body').on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var updateUrl = "{{ route('system.permission.update', ':id') }}".replace(':id', id);

                // Ubah Judul & Form
                $('#form-title').text('Edit Permission');
                $('#form-permission').attr('action', updateUrl);
                $('#input-name').val(name);

                // Tambahkan Method PUT
                $('#method-put').html('<input type="hidden" name="_method" value="PUT">');

                // Ganti Tombol
                // $('#btn-submit').text('Update').removeClass('btn-success').addClass('btn-warning');
                $('#btn-submit').addClass('d-none');
                $('#span-submit').addClass('d-none');
                $('#btn-edit').removeClass('d-none');
                $('.btn-reset').removeClass('d-none');
            });

            // LOGIC BATAL EDIT
            $('.btn-reset').click(function() {
                // Reset ke Mode Create
                $('#form-title').text('Tambah Permission Baru');
                $('#form-permission').attr('action', "{{ route('system.permission.store') }}");
                $('#input-name').val('');
                $('#method-put').html('');

                // $('#btn-submit').text('Simpan').addClass('btn-success').removeClass('btn-warning');
                $(this).addClass('d-none');
                $('#btn-edit').addClass('d-none');
                $('#btn-submit').removeClass('d-none');
                $('#span-submit').removeClass('d-none');
            });

            // LOGIC DELETE
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Hapus Permission?',
                    text: "Pastikan permission ini tidak sedang dipakai di kodingan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endsection
