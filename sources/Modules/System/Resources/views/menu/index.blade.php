@extends('system::template/admin/header')
@section('title', $title)
@section('link_href')
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Manajemen Menu</h3>
            <div class="box-tools pull-right">
                <a href="{{ route('system.menu.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Tambah Menu
                </a>
            </div>
        </div>

        <div class="box-body">
            <table class="table table-bordered table-striped" id="table-menu">
                <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Menu</th>
                    <th>Icon</th>
                    <th>Route</th>
                    <th>Permission (Gembok)</th>
                    <th>Status</th>
                    <th width="15%">Aksi</th>
                </tr>
                </thead>
                <tbody>
                {{-- KOSONGKAN BODY INI --}}
                {{-- DataTables AJAX akan mengisinya otomatis --}}
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#table-menu').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('system.menu.json') }}", // 👈 Ini dia yang manggil data!
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'text', name: 'text' },
                    {
                        data: 'icon',
                        name: 'icon',
                        render: function(data) {
                            return '<i class="'+data+'"></i> ('+data+')';
                        }
                    },
                    { data: 'route', name: 'route' },
                    { data: 'permission', name: 'permission_name' }, // Kolom custom di controller
                    { data: 'status', name: 'is_active' },         // Kolom custom di controller
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endpush
