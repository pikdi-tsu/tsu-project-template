@extends('system::template/admin/header')
@section('title', $title)
@section('link_href')

@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $menu }}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ $menu }}</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- /.col-md-6 -->
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="m-0">List {{ $menu }}</h5>
                        </div>
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Periode Masuk</th>
                                        <th>Program Studi</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer" style="display: none;">

                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

@endsection
@section('script')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            loadEvent()

            function loadEvent() {
                loadTable()
            }

            function loadTable() {
                let otable = $('#example2').DataTable({
                    destroy: true,
                    processing: true,
                    paging : false,
                    scrollX: true,
                    scrollY: '500px',
                    scrollCollapse: true,
                    serverSide: true,
                    searchDelay: 500,
                    order:[],
                    ajax: {
                        url: '{!! route('home.listmahasiswa') !!}',
                        type: 'GET',
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'nim'
                        },
                        {
                            data: 'nama'
                        },
                        {
                            data: 'periode'
                        },
                        {
                            data: 'prodi'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    language: {
                        processing: '<i class="fa fa-spinner fa-lg fa-spin"></i>'
                    },
                    drawCallback: function(settings) {
                        // edit_master()
                        // btndelete_event()
                        // detail_contentbank()
                    }
                });

                otable.on('draw', function(event) {
                    $('[data-toggle="tooltip"]').tooltip({
                        trigger: "hover"
                    });
                    $('[data-tooltip="tooltip"]').tooltip({
                        trigger: "hover"
                    });
                });
            }

        });
    </script>
@endsection
