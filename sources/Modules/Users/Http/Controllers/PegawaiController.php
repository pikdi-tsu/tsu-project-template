<?php

namespace Modules\Users\Http\Controllers;

use App\Models\PegawaiModel;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Session, Crypt, DB;
use Yajra\DataTables\DataTables;

class PegawaiController extends Controller
{
    public function __construct()
    {
        $this->middleware('checklogin');
    }
    public function index()
    {
        $data = array(
            'title' => 'Data Pegawai',
            'menu' => 'Data Pegawai',
        );
        return view('users::dosen/index',$data);
    }

        public function table_pegawai()
    {
        $data = PegawaiModel::get();
        // dd($data);
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nip', function ($data) {
                return $data->nip;
            })
            ->addColumn('nama', function ($data) {
                return $data->nama;
            })
            ->addColumn('homebase', function ($data) {
                return $data->homebase;
            })
            ->addColumn('action', function ($data) {
                $edit = '<a href="#"  class="btn_edit"><i title="Edit Data" class="fa fa-edit text-orange"></i></a>';
                $delete = '<a href="#"  class="btn_delete"><i title="Delete Data" class="fa fa-trash text-red"></i></a>';
                $download = '<a href="#" class="btn_download"><i title="Download Content" class="fas fa-info-circle"></i></a>';

                return $download . '  ' . $edit . '  ' . $delete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
