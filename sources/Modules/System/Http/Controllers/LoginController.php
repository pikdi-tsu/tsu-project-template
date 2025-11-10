<?php

namespace Modules\System\Http\Controllers;

use App\Models\GroupUserModel;
use App\Models\MasterGroupModel;
use App\Models\ModulModel;
use App\Models\MahasiswaModel;
use App\Models\PegawaiModel;
use App\Models\UserResetPasswordModel;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

use Session, Crypt, DB;

class LoginController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('checklogin');
    // }
    public $question_1 = array(
        'What is the first film you watched in theaters',
        'What is your nickname?',
        'What is your grandmothers maiden name?',
        'What is the name of your favorite elementary school teacher?',
        'Where did you meet your partner?',
        'Where is your mothers city born?'
    );

    public $question_2 = array(
        'What is your favorite food?',
        'What is the name of your favorite sports team?',
        'Whats your best hero name?',
        'What is the name of your favorite singer?',
        'Where did your parents city meet?',
        'Where did you first work?'
    );
    public function index()
    {
        if (Session::has('session')) {
            return redirect('dashboard')->with('alert',[
                'title' => 'success!',
                'message' => 'Already login',
                'status' => 'success'
            ]);
        } else {
            $this->checkTimeChance();
            $data = array(
                'title' => 'Login',
                'menu'  => 'Login '
            );
            return view('system::login/loginform',$data);
        }
    }

    public function loginaction(Request $post)
    {
        $credentials = $post->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        // dd($this->loginChance());
        if (Auth::attempt(['email' => $post->email, 'password' => $post->password])) {
            $cek = User::where('email', $post->email)->where('isactive',1)->first();
            if ($cek == null) {
                Session::flash('alert', ['title' => 'Error', 'message' => 'Email belum terdaftar, Kesempatan : ' . $this->loginChance() . ' kali', 'status' => 'error']);
                return redirect()->back();
            }

            $pass = null;

            if (Hash::check($post->password, $cek->password)) {
                $pass = TRUE;
            }

            if (($post->email == $cek->email) && $pass == TRUE) {
                $nama = null;
                $groupuser = GroupUserModel::where('KodeGroupUser', $cek->role_access)->get();
                $mastergroup = MasterGroupModel::where('KodeGroupUser', $cek->role_access)->first();

                if ($mastergroup->NamaGroup == 'MAHASISWA') {
                    $cek1 = MahasiswaModel::where('nim', $cek->nik)->first();
                    $nama = $cek1->nama;
                } else {
                    $cek2 = PegawaiModel::where('nip', $cek->nik)->first();
                    $nama = $cek2->nama;
                }
                if(Hash::check(defaultpassword(),$cek->password)){
                    $session = array(
                        'tmp_nik'   => $cek->nik,
                        'tmp_nama'  => $nama,
                        'tmp_email' => $post->email,
                        'tmp_role' => $cek->role_access,
                    );

                    Session::put('tmp', $session);
                    return redirect('NewPassword')->with('alert', ['title' => 'Information', 'message' => 'Silahkan Input Password Baru !', 'status' => 'info']);
                }else{
                    $post->session()->regenerate();
                    $session = array(
                        'user_nik'    => $cek->nik,
                        'user_nama'   => $nama,
                        'email'       => $cek->email,
                    );
                    Session::put('session', $session);
                    Session::put('namagroup',$mastergroup->NamaGroup);
                    Session::put('groupuser',$groupuser);
                    Session::put('appname','Siakad');
                    Session::flash('alert', ['title' => 'Success', 'message' => 'Berhasil Login!', 'status' => 'success']);
                    return redirect()->intended('dashboard');
                }
            } else {
                Session::flash('alert', ['title' => 'Error', 'message' => 'Password Salah, Kesempatan : ' . $this->loginChance() . ' kali', 'status' => 'error']);
                return redirect()->back();
            }
        } else {
            Session::flash('alert', ['title' => 'Gagal', 'message' => 'Silahkan Isi Email dan Password dengan benar, Kesempatan : ' . $this->loginChance() . ' kali', 'status' => 'error']);
            return redirect()->back();
        }

        // return back()->withErrors([
        //     'email' => 'The provided credentials do not match our records.',
        // ]);
    }

    public function loginChance()
    {
        if(Session::has('login_chance')){
            $login_chance = Session::get('login_chance');

            if ($login_chance['chance'] > 0) {
                $chance = $login_chance['chance'] - 1;
                $data = array(
                    'chance'        => $chance,
                    'time_start'    => time(),
                );
                Session::put('login_chance', $data);

                return $chance;
            } elseif ($login_chance['time_start'] > (15)) {
                Session::forget('login_chance');
            } elseif ($login_chance['chance'] == 0) {
                $data = array(
                    'chance'        => 0,
                    'time_start'    => time(),
                );
                Session::put('login_chance', $data);
            }
        }else{
            $data = array(
                'chance'        => 5,
                'time_start'    => time(),
            );
            Session::put('login_chance', $data);

            return 5;
        }
    }

    public function newPassword()
    {
        $session    = Session::get('tmp');
        $data = array(
            'title'     => 'New Password',

            'action'    => '#',
            'nik'       => isset($session['tmp_nik']) ? $session['tmp_nik'] : '',
            'nama'      => isset($session['tmp_nama']) ? $session['tmp_nama'] : '',
            'role'      => isset($session['tmp_role']) ? $session['tmp_role'] : '',
            'question_1'=> $this->question_1,
            'question_2'=> $this->question_2,
        );
        // dd($data);
        return view('system::login/newpassword',$data);
    }

    public function newPasswordAction(Request $post)
    {
        $data = array(
            'nik'       => $post->nik,
            'password'  => Hash::make($post->password),
            'q1'        => $post->q_1,
            'a1'        => $post->a_1,
            'q2'        => $post->q_2,
            'a2'        => $post->a_2,
        );

        $cek = User::where('nik',$post->nik)->first();
        // dd($cek);
        if (isset($post->nik) && isset($post->password) && isset($post->q_1) && isset($post->a_1) && isset($post->q_2) && isset($post->a_2)) {
            $update = User::where('nik',$post->nik)->where('isactive',1)->update($data);
            if($update){
                Session::forget('tmp');
                return redirect('login')->with('alert',['title' => 'Berhasil', 'message' => 'Password Berhasil Diganti, Silahkan Login ulang !', 'status' => 'success']);
            }else{
                return redirect()->route('NewPassword')->with('alert',['title' => 'Gagal', 'message' => 'Password gagal diganti ! Silahkan coba kembali !', 'status' => 'error']);
            }
        }else{
            return redirect()->route('NewPassword')->with('alert',['title' => 'Gagal', 'message' => 'Silahkan Lengkapi Data !', 'status' => 'error']);
        }
    }

    function checkTimeChance(){
        if(Session::has('login_chance')){
            $login_chance = Session::get('login_chance');
            if ($login_chance['chance'] == 0) {
                $chance = date('H:i:s', strtotime('+30 second', $login_chance['time_start']));
                if (time() >= strtotime($chance)) {
                    Session::forget('login_chance');
                }else{
                    Session::put('time_chance', strtotime('+30 second', $login_chance['time_start']) - time());
                }
            }
        }
    }

    public function checkbirthday(Request $get){
        $birthday = $get->birthday;
        $nik      = $get->nik;
        $role     = $get->role;

        $cekrole = MasterGroupModel::where('KodeGroupUser', $role)->first();

        if ($cekrole->NamaGroup == 'MAHASISWA') {
            $cek1 = MahasiswaModel::where('nim', $nik)->first();
            $tgl = $cek1->tgl_lahir;
        } else {
            $cek1 = PegawaiModel::where('nip', $nik)->first();
            $tgl = $cek1->tgl_lahir;
        }
        if (strtotime($tgl) == strtotime($birthday)) {
            return '1';
        } else {
            return '0';
        }
    }

    public function logout(Request $req)
    {
        // dd($req);
        Auth::logout();

        $req->session()->invalidate();
        $req->session()->regenerateToken();

        Session::flash('alert', ['title' => 'Success', 'message' => 'Anda sudah logout', 'status' => 'success']);
        return redirect(route('loginform'));
    }

    public function forgotPassword()
    {
        $data = array(
            'title' => 'Forgot Password',
        );
        return view('system::login/forgotpassword', $data);
    }

    public function ActionSendLink(Request $post)
    {
        $post->validate(['email' => 'required|email']);

        $cek = User::where('email',$post->email)->first();
        $email = $cek->email;
        $enc = encrypt($cek->nik);
        // dd($post);
        if($cek){
            UserResetPasswordModel::insert([
                'email'   => $cek->email,
                'token'   => $post->_token,
                'activity'=> 'Forgot Password'
            ]);
            $resetLink = route('ForgotPassword.formreset', $enc);
            $login = route('loginform');

            // Kirim email
            // $email, $nama, $data, $jenis, $subject
            $nama = session('session')['user_nama'];
            $data = session('namagroup').'##'.session('session')['user_nik'].'##'.$resetLink.'##'.$login;
            $jenis = 'Reset Password';
            $subject = 'Reset Password Siakad';

            $send = SendEmail($email, $nama, $data, $jenis, $subject);
            // Mail::raw("Klik link berikut untuk reset password: $resetLink", function($message) use ($email) {
            //     $message->to($email)
            //             ->subject('Reset Password Siakad');
            // });
            User::where('email',$post->email)->where('nik',$cek->nik)->update([
                'forgotpassword_sendemail' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $cek->nik
            ]);
            return redirect()->route('loginform')->with('alert',['title' => 'success', 'message' => 'Silahkan cek email anda untuk reset password', 'status' => 'success']);
        }else{
            return redirect()->back()->with('alert',['title' => 'Gagal', 'message' => 'Gagal Mengirim Link ! User Tidak Terdaftar', 'status' => 'error']);
        }
    }

    public function FormForgotPassword($params)
    {
        // dd($params);
        $nik = decrypt($params);
        $cek = User::where('nik',$nik)->select('email','nik','forgotpassword_sendemail')->first();

        $data = array(
            'title' => 'Form Forgot Password',
            'data' => $cek,
            'params' => $params
        );
        // dd($data);
        return view('system::login/form_forgotpassword', $data);
    }

    public function ForgotPasswordAction(Request $post,$params)
    {
        $nik = decrypt($params);
        // dd($post,$params);
        // $cek = User::where('nik',$nik)->where('email',$post->email)->first();
        $arr = array(
            'password' => Hash::make($post->password),
            'forgotpassword_sendemail' => '0',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $nik
        );

        $updt = User::where('nik',$nik)->where('email',$post->email)->update($arr);
        if($updt){
            return redirect()->route('loginform')->with('alert',['title' => 'success', 'message' => 'Password Sudah diubah ! Silahkan Login', 'status' => 'success']);
        }else{
            return redirect()->back()->with('alert',['title' => 'Error', 'message' => 'Password gagal diganti !', 'status' => 'error']);
        }
    }


}
