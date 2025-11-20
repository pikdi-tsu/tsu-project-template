<?php

namespace Modules\System\Http\Controllers;

use App\Models\GroupUserModel;
use App\Models\MasterGroupModel;
use App\Models\ModulModel;
use App\Models\MahasiswaModel;
use App\Models\PegawaiModel;
use App\Models\SiakadMahasiswa;
use App\Models\UserDosenTendik;
use App\Models\UserMahasiswa;
use App\Models\UserResetPasswordModel;
use App\Models\User;
use Illuminate\Foundation\Auth\ThrottlesLogins;
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

use Illuminate\Support\Facades\Session, Crypt, DB;

class LoginController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('checklogin');
    // }
    use ThrottlesLogins;

    protected $maxAttempts = 5;
    protected $decayMinutes = 1;

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

    public function username()
    {
        return 'email';
    }

    public function indexDosenTendik()
    {
        if (Auth::guard('dosen_tendik')->check()) {
            return redirect('dashboard')->with('alert', ['title' => 'Success!', 'message' => 'Already login', 'status' => 'success']);
        }
//        if (Session::has('session')) {
//            return redirect('dashboard')->with('alert',[
//                'title' => 'success!',
//                'message' => 'Already login',
//                'status' => 'success'
//            ]);
//        }

        $this->checkLockoutSession();

//        $chance = 5;
//        $time = 0;
//        $time_chance = '00:00';
//        $this->checkTimeChance();
//
//        $login_chance = Session::get('login_chance');
//        if (Session::has('login_chance')) {
//            $chance = $login_chance['chance'];
//            $time = $login_chance['time_start'];
//        }
//
//        if (Session::has('time_chance')) {
//            $time_chance = date('i:s', Session::get('time_chance'));
//        }

        $data = array(
            'title' => 'Login',
            'menu'  => 'Login ',
//            'chance' => $chance,
//            'time' => $time,
//            'time_chance' => $time_chance
        );

        return view('system::login/DosenTendik/loginform',$data);
    }

    public function indexMahasiswa()
    {
        if (Auth::guard('mahasiswa')->check()) {
            return redirect('dashboard')->with('alert', ['title' => 'Success!', 'message' => 'Already login', 'status' => 'success']);
        }

//        if (Session::has('session')) {
//            return redirect('dashboard')->with('alert',[
//                'title' => 'success!',
//                'message' => 'Already login',
//                'status' => 'success'
//            ]);
//        }

//        $chance = 5;
//        $time = 0;
//        $time_chance = '00:00';
//        $this->checkTimeChance();

        $this->checkLockoutSession();

//        $login_chance = Session::get('login_chance');
//        if (Session::has('login_chance')) {
//            $chance = $login_chance['chance'];
//            $time = $login_chance['time_start'];
//        }
//
//        if (Session::has('time_chance')) {
//            $time_chance = date('i:s', Session::get('time_chance'));
//        }

        $data = array(
            'title' => 'Login',
            'menu'  => 'Login ',
//            'chance' => $chance,
//            'time' => $time,
//            'time_chance' => $time_chance
        );
        return view('system::login/Mahasiswa/loginform',$data);
    }

    private function checkLockoutSession()
    {
        if (Session::has('lockout_expiration')) {
            $expiration = Session::get('lockout_expiration');
            $remaining = $expiration - now()->timestamp;

            if ($remaining > 0) {
                Session::flash('alert', [
                    'title' => 'Terlalu Banyak Percobaan',
                    'message' => "Silakan coba lagi dalam $remaining detik.",
                    'status' => 'danger'
                ]);
            } else {
                Session::forget('lockout_expiration');
            }
        }
    }

    public function loginActionDosenTendik(Request $post)
    {
        // Panggil helper utama dengan parameter khusus Dosen
        return $this->handleLogin($post, 'dosen_tendik', 'DOSEN_TENDIK');
    }

    public function loginActionMahasiswa(Request $post)
    {
        // Panggil helper utama dengan parameter khusus Mahasiswa
        return $this->handleLogin($post, 'mahasiswa', 'MAHASISWA');
    }

    private function handleLogin(Request $post, string $guard, string $roleType)
    {
        // Validasi Input
        $credentials = $post->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Cek Throttling (Anti Brute Force)
        if ($this->hasTooManyLoginAttempts($post)) {
            $this->fireLockoutEvent($post);
            $seconds = $this->limiter()->availableIn($this->throttleKey($post));
            // 1. Simpan waktu "bebas penjara" di Session biasa (biar awet)
            // Kita simpan timestamp kapan dia boleh login lagi
            Session::put('lockout_expiration', now()->addSeconds($seconds)->timestamp);

            Session::flash('alert', ['title' => 'Terlalu Banyak Percobaan', 'message' => "Silakan coba lagi dalam $seconds detik.", 'status' => 'danger']);
            return redirect()->back();
        }

        // Coba Login ke Guard yang Sesuai
        if (Auth::guard($guard)->attempt($credentials)) {

            $user = Auth::guard($guard)->user();

            // Cek Status Aktif
            if (!$user->isactive) {
                Auth::guard($guard)->logout();
                $this->incrementLoginAttempts($post);
                Session::flash('alert', ['title' => 'Error', 'message' => 'Akun Anda tidak aktif.', 'status' => 'danger']);
                return redirect()->back();
            }

            // Reset Percobaan Login (Jika sukses)
            $this->clearLoginAttempts($post);

            // Ambil Data Profil (Logic Pembeda)
            $namaGroup = null;
            $groupUser = collect([]);

            if (!empty($user->role_access)) {
                $masterGroup = MasterGroupModel::query()->where('KodeGroupUser', $user->role_access)->first();
                $groupUser   = GroupUserModel::query()->where('KodeGroupUser', $user->role_access)->get();
                if ($masterGroup) {
                    $namaGroup = $masterGroup->NamaGroup;
                }
            }

            $namaUser = null;
            $identifier = ($guard === 'mahasiswa') ? $user->nim : $user->nik;

            if ($guard === 'mahasiswa') {
                $profil = SiakadMahasiswa::query()->where('nim', $identifier)->first();
                if (!$profil) {
                    Auth::guard($guard)->logout();
                    Session::flash('alert', ['title' => 'Error', 'message' => 'Data profil Siakad tidak ditemukan.', 'status' => 'danger']);
                    return redirect()->back();
                }
                $namaUser = $profil->nama_lengkap;
            } else {
                $namaUser = $user->name;
            }

            // Cek Password Default (Redirect ke Ganti Password)
            if (Hash::check(defaultpassword(), $user->password)) {
                Session::put('tmp', [
                    'tmp_nik'   => $identifier,
                    'tmp_nama'  => $namaUser,
                    'tmp_email' => $user->email,
                    'tmp_role'  => $user->role_access,
                    'tmp_guard' => $guard,
                ]);
                return redirect('NewPassword')->with('alert', ['title' => 'Informasi', 'message' => 'Silakan Input Password Baru!', 'status' => 'info']);
            }

            $post->session()->regenerate();

            // Buat Session Utama & Redirect Dashboard
            $post->session()->regenerate();

            Session::put('session', [
                'user_nik'      => $identifier,
                'user_nama'     => $namaUser,
                'email'         => $user->email,
                'role_access'   => $user->role_access,   // <-- Ambil dari DB
                'privilege_pmb' => $user->privilege_pmb, // <-- Ambil dari DB (PMB)
            ]);

            Session::put('namagroup', $namaGroup);
            Session::put('groupuser', $groupUser);
            Session::put('appname', 'Siakad');

            Session::flash('alert', ['title' => 'Success', 'message' => 'Berhasil Login!', 'status' => 'success']);
            return redirect()->intended('dashboard');

        }

        // Login Gagal
        $this->incrementLoginAttempts($post);
        Session::flash('alert', ['title' => 'Gagal', 'message' => 'Email atau Password salah.', 'status' => 'danger']);
        return redirect()->back();
    }

    public function logout(Request $req)
    {
        $redirectRoute = '';

        if (Auth::guard('mahasiswa')->check()) {
            Auth::guard('mahasiswa')->logout();
            $redirectRoute = 'login.mahasiswa';
        } elseif (Auth::guard('dosen_tendik')->check()) {
            Auth::guard('dosen_tendik')->logout();
            $redirectRoute = 'login.dosen-tendik';
        } elseif (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $redirectRoute = 'indexing';
        }

        $req->session()->invalidate();
        $req->session()->regenerateToken();
        Session::flash('alert', ['title' => 'Success', 'message' => 'Anda sudah logout', 'status' => 'success']);

        return redirect(route($redirectRoute));
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
        $session = Session::get('tmp');
        if (!$session) return redirect(route('login.mahasiswa'));

        $data = [
            'title'      => 'New Password',
            'action'     => '#',
            'nik'        => $session['tmp_nik'],
            'nama'       => $session['tmp_nama'],
            'role'       => $session['tmp_role'],
            'question_1' => $this->question_1,
            'question_2' => $this->question_2,
        ];
        return view('system::login/newpassword', $data);
    }

    public function newPasswordAction(Request $post)
    {
        $session = Session::get('tmp');
        if (!$session) return redirect(route('login.mahasiswa'));

        $data = [
            'password'  => Hash::make($post->password),
            'q1' => $post->q_1, 'a1' => $post->a_1,
            'q2' => $post->q_2, 'a2' => $post->a_2,
        ];

        // Validasi manual sederhana
        if ($post->nik && $post->password && $post->q_1 && $post->a_1 && $post->q_2 && $post->a_2) {
            $update = false;

            // Gunakan Session role untuk menentukan tabel target
            if ($session['tmp_role'] === 'MAHASISWA') {
                $update = UserMahasiswa::where('nim', $post->nik)->update($data);
            } elseif ($session['tmp_role'] === 'DOSEN_TENDIK') {
                $update = UserDosenTendik::where('nik', $post->nik)->update($data);
            }

            if ($update) {
                Session::forget('tmp');
                return redirect(route('login.mahasiswa'))->with('alert', ['title' => 'Berhasil', 'message' => 'Password Berhasil Diganti, Silahkan Login ulang!', 'status' => 'success']);
            }
        }

        return redirect()->route('NewPassword')->with('alert', ['title' => 'Gagal', 'message' => 'Gagal mengganti password. Silakan lengkapi data.', 'status' => 'danger']);
    }

//    function checkTimeChance(){
//        if(Session::has('login_chance')){
//            $login_chance = Session::get('login_chance');
//            if ($login_chance['chance'] == 0) {
//                $chance = date('H:i:s', strtotime('+30 second', $login_chance['time_start']));
//                if (time() >= strtotime($chance)) {
//                    Session::forget('login_chance');
//                }else{
//                    Session::put('time_chance', strtotime('+30 second', $login_chance['time_start']) - time());
//                }
//            }
//        }
//    }

    public function checkbirthday(Request $get){
        $birthday = $get->birthday;
        $nik      = $get->nik;
        $role     = $get->role;

        $cekrole = MasterGroupModel::where('KodeGroupUser', $role)->first();

        if ($cekrole->NamaGroup === 'MAHASISWA') {
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

    public function forgotPasswordMahasiswa()
    {
        return view('system::login.Mahasiswa.forgot_password', [
            'title' => 'Lupa Password Mahasiswa',
            'action_url' => route('forgot_password.send.mahasiswa') // Pastikan route ini ada
        ]);
    }

    public function forgotPasswordDosenTendik()
    {
        return view('system::login.DosenTendik.forgot_password', [
            'title' => 'Lupa Password Dosen & Tendik',
            'action_url' => route('forgot_password.send.dosen_tendik')
        ]);
    }

    public function actionSendLinkMahasiswa(Request $post)
    {
        return $this->handleSendLink($post, 'mahasiswa');
    }

    public function actionSendLinkDosenTendik(Request $post)
    {
        return $this->handleSendLink($post, 'dosen_tendik');
    }

    private function handleSendLink(Request $post, string $type)
    {
        $post->validate(['email' => 'required|email']);

        // Tentukan konfigurasi
        if ($type === 'mahasiswa') {
            $model = UserMahasiswa::class;
            $id_col = 'nim';
            $redirectBack = route('forgot_password.mahasiswa');
            $loginRoute = route('login.mahasiswa');
        } else {
            $model = UserDosenTendik::class;
            $id_col = 'nik';
            $redirectBack = route('forgot_password.dosen_tendik');
            $loginRoute = route('login.dosen-tendik');
        }

        $user = $model::query()->where('email', $post->email)->where('isactive', 1)->first();

        if (!$user) {
            return redirect($redirectBack)->with('alert', ['title' => 'Gagal', 'message' => 'Email tidak ditemukan di data ' . ucfirst($type), 'status' => 'danger']);
        }

        $id_val = $user->$id_col;

        // Token Tetap Terenkripsi (Untuk Validasi Keamanan)
        $enc_param = encrypt($id_val . '##' . $type);

        // route('name', ['type' => ..., 'params' => ...])
        $resetLink = route('forgot_password.form_reset', ['type' => $type, 'token' => $enc_param]);

        UserResetPasswordModel::query()->insert([
            'email'    => $user->email,
            'token'    => $post->_token,
            'activity' => 'Forgot Password ' . ucfirst($type)
        ]);

        $nama = $user->name;
        if ($type === 'mahasiswa' && $user->profil) {
            $nama = $user->profil->nama_lengkap;
        }

        $emailData = $nama . '##' . $id_val . '##' . $resetLink . '##' . $loginRoute;

        SendEmail($user->email, $nama, $emailData, 'Reset Password', 'Reset Password Siakad');

        $model::query()->where($id_col, $id_val)->update([
            'forgot_password_send_email' => 1,
            'updated_at' => now(),
            'updated_by' => $id_val
        ]);

        return redirect($redirectBack)->with('alert', ['title' => 'Sukses', 'message' => 'Silahkan cek email Anda untuk reset password.', 'status' => 'success']);
    }

    public function FormForgotPassword(Request $request, $type)
    {
        // Tentukan Route Login untuk Redirect Error
        $loginRoute = ($type === 'mahasiswa') ? route('login.mahasiswa') : route('login.dosen-tendik');

        // Ambil token dari URL (?token=...)
        $params = $request->query('token');

        if (!$params) {
            return redirect($loginRoute)->with('alert', ['title' => 'Error', 'message' => 'Token tidak ditemukan.', 'status' => 'danger']);
        }

        try {
            // Bongkar Token
            $decrypted = decrypt($params);
            [$id_val, $token_type] = explode('##', $decrypted);

            // Validasi Tipe
            if ($type !== $token_type) {
                throw new \RuntimeException("Type mismatch");
            }

            // Cari User
            $model = ($type === 'mahasiswa') ? UserMahasiswa::class : UserDosenTendik::class;
            $col   = ($type === 'mahasiswa') ? 'nim' : 'nik';

            $user = $model::query()->where($col, $id_val)->select('email', "$col as nik_or_nim", 'forgot_password_send_email')->first();

            if (!$user || $user->forgot_password_send_email != 1) {
                return redirect($loginRoute)->with('alert', ['title' => 'Error', 'message' => 'Link sudah kedaluwarsa.', 'status' => 'danger']);
            }

            $viewName = ($type === 'mahasiswa')
                ? 'system::login/Mahasiswa/form_forgot_password'
                : 'system::login/DosenTendik/form_forgot_password';

            return view($viewName, [
                'title'  => 'Form Reset Password',
                'data'   => $user,
                'params' => $params, // Kirim token ke view buat di-submit lagi
                'type'   => $type
            ]);

        } catch (\Exception $e) {
            return redirect($loginRoute)->with('alert', ['title' => 'Error', 'message' => 'Link tidak valid.', 'status' => 'danger']);
        }
    }

    public function ForgotPasswordAction(Request $request, $type)
    {
        $loginRoute = ($type === 'mahasiswa') ? route('login.mahasiswa') : route('login.dosen-tendik');

        $params = $request->input('params') ?? $request->query('token');

        try {
            [$id_val, $token_type] = explode('##', decrypt($params));

            if ($type !== $token_type) {
                throw new \RuntimeException("Type mismatch");
            }

            $model = ($type === 'mahasiswa') ? UserMahasiswa::class : UserDosenTendik::class;
            $col   = ($type === 'mahasiswa') ? 'nim' : 'nik';

            $update = $model::where($col, $id_val)->where('email', $request->email)->update([
                'password' => Hash::make($request->password),
                'forgot_password_send_email' => '0',
                'updated_at' => now()
            ]);

            if ($update) {
                return redirect($loginRoute)->with('alert', ['title' => 'Sukses', 'message' => 'Password berhasil diubah. Silahkan Login.', 'status' => 'success']);
            }

            return back()->with('alert', ['title' => 'Gagal', 'message' => 'Gagal update password. Email tidak cocok.', 'status' => 'danger']);

        } catch (\Exception $e) {
            return redirect($loginRoute)->with('alert', ['title' => 'Error', 'message' => 'Terjadi kesalahan token.', 'status' => 'danger']);
        }
    }

//    public function FormForgotPassword($params)
//    {
//        try {
//            list($id_val, $type) = explode('##', decrypt($params));
//
//            $model = ($type === 'mahasiswa') ? UserMahasiswa::class : UserDosenTendik::class;
//            $col   = ($type === 'mahasiswa') ? 'nim' : 'nik';
//
//            $user = $model::where($col, $id_val)->select('email', "$col as nik_or_nim", 'forgotpassword_sendemail')->first();
//
//            if (!$user || $user->forgotpassword_sendemail != 1) throw new \Exception("Invalid Token");
//
//            return view('system::login/form_forgotpassword', ['title' => 'Form Reset', 'data' => $user, 'params' => $params]);
//
//        } catch (\Exception $e) {
//            return redirect(route('login.mahasiswa'))->with('alert', ['title' => 'Error', 'message' => 'Link tidak valid.', 'status' => 'error']);
//        }
//    }

//    public function ForgotPasswordAction(Request $post, $params)
//    {
//        try {
//            list($id_val, $type) = explode('##', decrypt($params));
//
//            $model = ($type === 'mahasiswa') ? UserMahasiswa::class : UserDosenTendik::class;
//            $col   = ($type === 'mahasiswa') ? 'nim' : 'nik';
//
//            $update = $model::where($col, $id_val)->where('email', $post->email)->update([
//                'password' => Hash::make($post->password),
//                'forgotpassword_sendemail' => '0',
//                'updated_at' => now()
//            ]);
//
//            if ($update) return redirect(route('login.mahasiswa'))->with('alert', ['title' => 'Sukses', 'message' => 'Password berhasil diubah.', 'status' => 'success']);
//
//            return back()->with('alert', ['title' => 'Gagal', 'message' => 'Gagal update password.', 'status' => 'error']);
//
//        } catch (\Exception $e) {
//            return redirect(route('login.mahasiswa'))->with('alert', ['title' => 'Error', 'message' => 'Link expired.', 'status' => 'error']);
//        }
//    }

}
