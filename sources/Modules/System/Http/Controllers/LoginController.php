<?php

namespace Modules\System\Http\Controllers;

use App\Models\GroupUserModel;
use App\Models\MasterGroupModel;
use App\Models\ModulModel;
use App\Models\MahasiswaModel;
use App\Models\PegawaiModel;
use App\Models\PertanyaanKeamanan;
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
    use ThrottlesLogins;

    protected $maxAttempts = 5;
    protected $decayMinutes = 1;

    public function username()
    {
        return 'email';
    }

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard')
                ->with('alert', ['title' => 'Info', 'message' => 'Anda sudah login.', 'status' => 'info']);
        }

        $data = [
            'title' => 'Login',
            'app_name' => config('app.name', 'TSU Template'),
        ];

        return view('system::login.loginform', $data);
    }

    public function login(Request $request)
    {
        // Validasi Input Dasar
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Cek Throttling (Anti Brute Force)
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $seconds = $this->limiter()->availableIn($this->throttleKey($request));
            Session::flash('alert', ['title' => 'Blocked', 'message' => "Terlalu banyak percobaan. Tunggu $seconds detik.", 'status' => 'danger']);
            return back();
        }

//        $loginType = filter_var($request->identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            'email'     => $request->email,
            'password'  => $request->password,
            'isactive'  => 1
        ];

        if (Auth::attempt($credentials)) {

            Session::put('appname', config('app.name', 'TSU Template'));

            // Bersihkan rate limiter
            $this->clearLoginAttempts($request);

            return redirect()->route('dashboard')
                ->with('alert', ['title' => 'Success', 'message' => 'Login Berhasil!', 'status' => 'success']);
        }

        $this->incrementLoginAttempts($request);
        return back()->with('alert', ['title' => 'Gagal', 'message' => 'Username atau Password salah / Akun tidak aktif.', 'status' => 'danger']);
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Logout Auth Laravel

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('alert', ['title' => 'Success', 'message' => 'Anda berhasil logout.', 'status' => 'success']);
    }

    public function newPassword()
    {
        // Cek Session TMP (Dari LoginController saat detect default password)
        $session = Session::get('tmp');
        if (!$session) {
            return redirect(route('login'));
        }

        // AMBIL PERTANYAAN DARI DATABASE (Dinamis)
        $questions_1 = PertanyaanKeamanan::query()->where('jenis', 'q1')->get();
        $questions_2 = PertanyaanKeamanan::query()->where('jenis', 'q2')->get();

        $data = [
            'title'      => 'New Password',
            'action'     => '#',
            'nik'        => $session['tmp_nik'],
            'nama'       => $session['tmp_nama'],
            'role'       => $session['tmp_role'],
            'question_1' => $questions_1,
            'question_2' => $questions_2,
        ];
        return view('system::login/newpassword', $data);
    }

    public function newPasswordAction(Request $post)
    {
        $session = Session::get('tmp');
        if (!$session) {
            return redirect(route('login'));
        }

        $post->validate([
            'password' => 'required|min:6|confirmed',
            'q_1'      => 'required',
            'a_1'      => 'required',
            'q_2'      => 'required',
            'a_2'      => 'required',
        ]);

        $updateData = [
            'password' => Hash::make($post->password),
            'q1'       => $post->q_1,
            'a1'       => $post->a_1,
            'q2'       => $post->q_2,
            'a2'       => $post->a_2,
            'updated_at' => now(),
        ];

        // LOGIKA UPDATE UNIFIED (Cek Role/Guard dari Session TMP)
        $guard = $session['tmp_guard'];
        $id    = $session['tmp_nik'];

        $updated = false;

        // Tentukan Model berdasarkan Guard
        if ($guard === 'mahasiswa') {
            $updated = UserMahasiswa::query()->where('nim', $id)->update($updateData);
        } elseif ($guard === 'dosen_tendik') {
            $updated = UserDosenTendik::query()->where('nik', $id)->update($updateData);
        }

        try {
            if ($updated) {
                Session::forget('tmp'); // Hapus session sementara
                return redirect()->route('login')->with('alert', ['title' => 'Berhasil', 'message' => 'Password & Keamanan berhasil diatur. Silakan Login ulang!', 'status' => 'success']);
            }
        } catch (\Throwable $th) {
            return back()->with('alert', ['title' => 'Gagal', 'message' => 'Gagal mengupdate data. Silakan coba lagi.', 'status' => 'danger']);
        }

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
                $update = UserMahasiswa::query()->where('nim', $post->nik)->update($data);
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

    public function checkbirthday(Request $get)
    {
        $birthday = $get->birthday;
        $nik      = $get->nik;
        $role     = $get->role;

        $cekrole = MasterGroupModel::query()->where('KodeGroupUser', $role)->first();

        if ($cekrole->NamaGroup === 'MAHASISWA') {
            $cek1 = MahasiswaModel::where('nim', $nik)->first();
            $tgl = $cek1->tgl_lahir;
        } else {
            $cek1 = PegawaiModel::where('nip', $nik)->first();
            $tgl = $cek1->tgl_lahir;
        }
        if (strtotime($tgl) === strtotime($birthday)) {
            return '1';
        }

        return '0';
    }

    public function forgotPassword()
    {
        return view('system::login.forgot_password', [
            'title' => 'Lupa Password',
            'action_url' => route('forgot_password.send')
        ]);
    }

    public function actionSendLink(Request $post)
    {
        return $this->sendResetLink($post);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // 1. AMBIL LIST GUARD DARI CONFIG (Sama kayak login)
        $guardsToCheck = config('app.active_guards', ['dosen_tendik', 'mahasiswa']);

        $foundUser = null;
        $guardName = null;

        // 2. LOOPING PENCARIAN USER (Scanning semua tabel)
        foreach ($guardsToCheck as $guard) {
            $user = Auth::guard($guard)->getProvider()->retrieveByCredentials([
                'email' => $request->email
            ]);

            // Cek user ketemu & aktif
            if ($user && isset($user->isactive) && $user->isactive === 1) {
                $foundUser = $user;
                $guardName = $guard;
                break; // Ketemu! Stop looping.
            }
        }

        // 3. JIKA GAK KETEMU DI SEMUA TABEL
        if (!$foundUser) {
            return back()->with('alert', ['title' => 'Gagal', 'message' => 'Email tidak terdaftar di sistem manapun.', 'status' => 'danger']);
        }

        // 4. GENERATE "SMART TOKEN"
        // Ini triknya: Kita selipkan info "GUARD" di dalam token terenkripsi.
        // Format: ID_USER ## NAMA_GUARD ## TIMESTAMP
        $identifier = ($guardName === 'mahasiswa') ? $foundUser->nim : $foundUser->nik; // Sesuaikan field primary

        // Encrypt logic (sama kayak kode lamamu tapi tambah timestamp biar expired)
        $tokenPayload = $identifier . '##' . $guardName . '##' . now()->timestamp;
        $encryptedToken = encrypt($tokenPayload);
        $safeToken = bin2hex($encryptedToken);

        // 5. SIMPAN LOG AKTIVITAS (Optional, sesuai kode lama)
        UserResetPasswordModel::query()->insert([
            'email' => $request->email,
            'token' => $safeToken, // Bisa simpan token atau flag
            'activity' => 'Forgot Password Unified'
        ]);

        // 6. PERSIAPAN DATA EMAIL (Bagian Crucial!)
        // A. Logic Nama: Mahasiswa ambil dari Profil, Dosen dari User langsung
        $namaUser = $foundUser->name;
        // Cek relasi profil (sesuaikan nama relasi di model UserMahasiswa)
        if (($guardName === 'mahasiswa') && $foundUser->profil) {
            $namaUser = $foundUser->profil->nama_lengkap;
        }

        // B. Link Reset & Login
        // Arahkan ke route unified form reset
        $resetLink = route('forgot_password.form_reset', ['token' => $safeToken]);
        $loginRoute = route('login'); // Route login unified

        // C. Rakit String Data Email (Sesuai format lama: Nama##ID##LinkReset##LinkLogin)
        $emailData = $namaUser . '##' . $identifier . '##' . $resetLink . '##' . $loginRoute;

        // 7. KIRIM EMAIL
        // Panggil Helper Global 'SendEmail'
        try {
            SendEmail(
                $foundUser->email,
                $namaUser,
                $emailData,
                'Reset Password',
                'Reset Password ' . config('app.name') // Judul bisa dibuat dinamis dari config('tsu.app_name')
            );
        } catch (\Exception $e) {
            return back()->with('alert', ['title' => 'Error', 'message' => 'Gagal mengirim email: ' . $e->getMessage(), 'status' => 'danger']);
        }

        // 8. UPDATE FLAG DI TABLE USER (Legacy Support)
        // Kode lama mengupdate kolom 'forgot_password_send_email' di tabel user
        // Kita pertahankan biar logic lain gak rusak.
        $foundUser->forgot_password_send_email = 1;
        $foundUser->updated_at = Carbon::now();
        $foundUser->updated_by = $identifier; // Audit log sederhana
        $foundUser->save(); // Eloquent save biasa

        // Pastikan di email view, variabel $nama dan $link dikirim benar.
        return back()->with('alert', ['title' => 'Sukses', 'message' => 'Link reset sudah dikirim ke email.', 'status' => 'success']);
    }

    public function showResetForm(Request $request)
    {
        $tokenHex = $request->query('token');

        try {
            if (!ctype_xdigit($tokenHex)) {
                throw new \RuntimeException("Token format invalid");
            }

            $token = hex2bin($tokenHex);

            // 1. BONGKAR TOKEN
            $decrypted = decrypt($token);
            [$userId, $guardName, $timestamp] = explode('##', $decrypted);

            // 2. CEK KADALUARSA (Misal 60 menit)
            if (now()->timestamp - $timestamp > 3600) {
                throw new \RuntimeException("Token Expired");
            }

            $provider = config("auth.guards.{$guardName}.provider");
            $modelClass = config("auth.providers.{$provider}.model");

            $keyName = ($guardName === 'mahasiswa') ? 'nim' : 'nik'; // Sesuaikan logic primary key kamu

            $user = $modelClass::where($keyName, $userId)->first();

            // validasi one time use link
            if (!$user || $user->forgot_password_send_email !== 1) {
                return redirect()->route('login')->with('alert', ['title' => 'Error', 'message' => 'Link tidak valid atau sudah digunakan.', 'status' => 'danger']);
            }

            // 3. TAMPILKAN FORM
            // Kirim token & guardName tersembunyi ke view
            return view('system::login.form_forgot_password', [
                'title' => 'Form Reset Password',
                'data' => $user,
                'token' => $tokenHex,
                'email' => $request->query('email') // Opsional buat UX
            ]);

        } catch (\Exception $e) {
            return redirect()->route('login')->with('alert', ['title' => 'Error', 'message' => 'Link tidak valid atau kadaluwarsa.', 'status' => 'danger']);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required'],
            'password_retype' => ['required'],
        ]);

        try {
            // 1. BONGKAR LAGI TOKENNYA
            $tokenHex = $request->input('token');
            $encryptedToken = hex2bin($tokenHex);
            $decrypted = decrypt($encryptedToken);
            [$userId, $guardName] = explode('##', $decrypted);


            // 2. Setup Model & Key
            $provider = config("auth.guards.{$guardName}.provider");
            $modelClass = config("auth.providers.{$provider}.model");
            $keyName = ($guardName === 'mahasiswa') ? 'nim' : 'nik';


            // 3. UPDATE PASSWORD & FLAG
            // Cari user yang email-nya cocok (opsional extra check) dan ID-nya cocok
            $user = $modelClass::where($keyName, $userId)->first();

            if ($user) {
                // Cek lagi flag-nya (double protection)
                if ($user->forgot_password_send_email !== 1) {
                    return back()->with('alert', ['title' => 'Gagal', 'message' => 'Permintaan reset password sudah tidak aktif.', 'status' => 'danger']);
                }

                // ACTION UTAMA: Update DB
                $user->update([
                    'password' => Hash::make($request->password),
                    'forgot_password_send_email' => 0,
                    'updated_at' => now(),
                    // 'updated_by' => $userId // Opsional kalau ada kolom ini
                ]);

                return redirect()->route('login')->with('alert', ['title' => 'Sukses', 'message' => 'Password berhasil diubah. Silakan Login.', 'status' => 'success']);

            }
            return back()->with('alert', ['title' => 'Gagal', 'message' => 'User tidak ditemukan.', 'status' => 'danger']);
        } catch (\Exception $e) {
            return back()->with('alert', ['title' => 'Error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage(), 'status' => 'danger']);
        }
    }
}
