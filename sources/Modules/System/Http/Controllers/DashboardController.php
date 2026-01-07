<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use \Illuminate\Support\Facades\Session, Crypt, DB;

class DashboardController extends Controller
{
    public function __construct()
    {
//        $this->middleware('checklogin');
        $this->middleware('auth');
//        $this->middleware('verified');
    }

    public function index(){

        if(Session::has('tmp')){
            Session::forget('tmp');
        }

        $data = array(
            'title' => 'Halaman Dashboard',
            'menu'  => 'dashboard',
        );
//         dd(session()->all());
        return view('system::dashboard/dashboard', $data);
    }
}
