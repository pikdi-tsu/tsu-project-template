<?php

namespace Modules\Users\Http\Controllers;

use App\Models\MahasiswaModel;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\DataTables;

use Session, Crypt, DB;

class MahasiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware('checklogin');
    }
    public function index()
    {
        if (!Session::has('session')) {
            Session::flash('alert', ['title' => 'Gagal !', 'message' => 'Silahkan Login Terlebih Dahulu !', 'status' => 'warning']);
            return redirect()->route('loginform');
        }
        $data = array(
            'title' => 'Data Mahasiswa',
            'menu' => 'Data Mahasiswa',
        );
        return view('users::mahasiswa/index', $data);
    }

    public function table_mahasiswa()
    {
        $data = MahasiswaModel::get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nim', function ($data) {
                return $data->nim;
            })
            ->addColumn('nama', function ($data) {
                return $data->nama;
            })
            ->addColumn('periode', function ($data) {
                return $data->periode_masuk;
            })
            ->addColumn('prodi', function ($data) {
                return $data->program_studi;
            })
            ->addColumn('action', function ($data) {
                $edit = '<a href="#"  class="btn_edit"><i title="Edit Data" class="fa fa-edit text-orange"></i></a>';
                $delete = '<a href="#"  class="btn_delete"><i title="Delete Data" class="fa fa-trash text-red"></i></a>';
                $download = '<a href="#" class="btn_download"><i title="Detail Content" class="fas fa-info-circle"></i></a>';

                return $download . '  ' . $edit . '  ' . $delete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
