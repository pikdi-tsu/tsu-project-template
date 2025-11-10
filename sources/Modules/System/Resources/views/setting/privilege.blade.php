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

                            </h5>
                        </div>
                        <div class="card-body">
                            <center><b style="font-size: 15pt">Nama Group : {{ $data->NamaGroup }}</b></center><br>
                            <hr>
                            <form class="form-horizontal" method="POST" action="{{ route('gruopuser.SavePrivilege', [$id]) }}">
                                @csrf
                                <div class="col-md-6">
                                    <b style="font-size: 13pt;color: blue">Privilege Action and Menu : </b>
                                    @foreach ($modul as $mod)
                                        <table style="width: 100%">
                                            <tr style="background-color: #87CEFA">
                                                <td><b style="font-size: 13pt">
                                                        <center>{{ strtoupper($mod->modul) }}</center>
                                                    </b></td>
                                            </tr>
                                        </table>
                                        <table class="table-hover" style="width: 100%">
                                            @foreach ($moduldata[$mod->modul] as $mo)
                                                <?php
                                                $cari = $mo->modul . $mo->menu;

                                                if (array_search($cari, $m_modgroup)) {
                                                    $ckh = 'checked';
                                                } else {
                                                    $ckh = '';
                                                }
                                                ?>
                                                <tr>
                                                    <td style="width: 70%">
                                                        <label>
                                                            <input type="checkbox" name="menumod[{{ $cari }}]"
                                                                class="minimal"
                                                                value="{{ $mo->modul . '#' . $mo->menu . '#' . $mo->alias }}"
                                                                {{ $ckh }}>

                                                            {{ $mo->alias }}
                                                        </label>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $ch = ($ac = $akses
                                                                ->where('Modul', $mo->modul)
                                                                ->where('Menu', $mo->menu)
                                                                ->first())
                                                                ? $ac->FullAkses
                                                                : '';
                                                        @endphp
                                                        <select style="width: 100%" name="actionmod[{{ $cari }}]"
                                                            class="select2">
                                                            <option value=''></option>
                                                            <option value="readonly"
                                                                {{ $ch == 'readonly' ? 'selected' : '' }}>Readonly
                                                            </option>
                                                            <option value="full" {{ $ch == 'full' ? 'selected' : '' }}>
                                                                Full</option>
                                                            <option value="update" {{ $ch == 'update' ? 'selected' : '' }}>
                                                                Update
                                                            </option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    @endforeach
                                </div>
                                <div class="col-sm-12 text-center"
                                    style="padding-top: 2rem; margin-top: 1rem; border-top: solid lightgrey;">
                                    <button type="submit" class="btn btn-primary btn-lg">Save</button>
                                </div>
                            </form>
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

        });
    </script>
@endsection
