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
                            <h5 class="m-0">{{ $menu }}
                                <button type="button" class="btn btn-success btn-sm float-right" id="btn-addgroup">Add
                                    Data</button>
                            </h5>
                        </div>
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Group User</th>
                                        <th>Privilege</th>
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

    <div class="modal fade" id="modal-group">
        <div class="modal-dialog">
            <div class="modal-content ">
                <div class="modal-header">
                    <h4 class="modal-title" id="judul-modal"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('gruopuser.Save') }}" method="post" id="form-user">
                        @csrf
                        <input type="hidden" name="groupuserid" id="groupuserid">

                        <label for="groupuser">Nama Group User</label>
                        <input type="text" name="groupuser" id="groupuser" class="form-control"
                            placeholder="Nama Group User" required>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" form="form-user" class="btn btn-success">Submit</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
@endsection

@section('script')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2()

            LoadEvent()

            function LoadEvent() {
                loadTableGroup()
                ShowModal()
                closemodal()
            }

            function loadTableGroup() {
                let otable = $('#example2').DataTable({
                    destroy: true,
                    processing: true,
                    paging: false,
                    scrollX: true,
                    scrollY: '500px',
                    scrollCollapse: true,
                    serverSide: true,
                    searchDelay: 500,
                    order: [],
                    ajax: {
                        url: '{!! route('gruopuser.TabelGroupUser') !!}',
                        type: 'GET',
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'group'
                        },
                        {
                            data: 'privilege'
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
                        EditMenu()
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

            function ShowModal(){
                $('#btn-addgroup').click(function (e) {
                    e.preventDefault();
                    $('#judul-modal').html('New Master Group User')
                    $('#modal-group').modal('show')
                });
            }

            function EditMenu(){
                $('.btn_edit').click(function (e) {
                    e.preventDefault();
                    let params = $(this).data('id')
                    $('#judul-modal').html('Edit Master Group User')
                    $.ajax({
                        type: "GET",
                        url: '{!! url('setting/GetGroupUser') !!}' + '/' + params,
                        dataType: "JSON",
                        beforeSend: function(response) {
                            $('#loading').show()
                            $('#groupuserid').val(null)
                        },
                        success: function(data) {
                            $('#loading').hide()
                            if(data.hasil==0){
                                notifalert('Information', 'Data Tidak Ditemukan', 'error')
                            }else{
                                $('#groupuserid').val(data.groupid)
                                $('#groupuser').val(data.group.NamaGroup)
                                $('#modal-group').modal('show')
                            }
                        }
                    });
                    return false;
                });
            }

            function closemodal() {
                $('#modal-menu').on('hidden.bs.modal', function() {
                    $('#judul-modal').html('')
                    $('#groupuserid').val('')
                });
            }

        });
    </script>
@endsection
