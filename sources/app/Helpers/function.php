<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Models\SiakadMahasiswa;
use App\Models\SiakadDosenTendik;

if (!function_exists('defaultpassword')) {
    function defaultpassword()
    {
        return 'password@123';
    }
}

if (!function_exists('rupiah')) {
    function rupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('photo_profile')) {
    function photo_profile()
    {
        // Default photo jika tidak ditemukan
        $photo = 'user.png';

        // Ambil NIK/NIM dari session (pastikan key-nya sesuai LoginController)
        // Di LoginController kita set: Session::put('session', ['user_nik' => ...])
        $sessionData = Session::get('session');
        $nik = $sessionData['user_nik'] ?? null;

        if (empty($nik)) {
            return $photo;
        }

        $profil = null;

        // 1. Cek Guard: Apakah yang login Mahasiswa?
        if (Auth::guard('mahasiswa')->check()) {
            // Cari di tabel profil mahasiswa
            $profil = SiakadMahasiswa::query()->where('nim', $nik)->first();
        }
        // 2. Cek Guard: Apakah yang login Dosen/Tendik?
        elseif (Auth::guard('dosen_tendik')->check()) {
            // Cari di tabel profil dosen tendik
            $profil = SiakadDosenTendik::query()->where('nik', $nik)->first();
        }
        // 3. Fallback (Jika guard tidak terdeteksi tapi session ada - Legacy Support)
        else {
            // Coba cari di Mahasiswa dulu
            $profil = SiakadMahasiswa::query()->where('nim', $nik)->first();
            // Kalau gak ketemu, cari di Dosen
            if (!$profil) {
                $profil = SiakadDosenTendik::query()->where('nik', $nik)->first();
            }
        }

        if ($profil && !empty($profil->photo_profile)) {
            $photo = $profil->photo_profile;
        }

        return $photo;
    }
}

//if (!function_exists('photo_profile')) {
//    function photo_profile()
//    {
//        $nik = session('session')['user_nik'];
//        $email = session('session')['email'];
//        $photo = 'user.png';
//        // $photo = 'avatar.png';
//        $cek = User::where('nik', $nik)->where('email', $email)->first();
//        if ($cek->photo_profile != null) {
//            $photo = $cek->photo_profile;
//        }
//
//        return $photo;
//    }
//}

if (!function_exists('SendEmail')) {
    function SendEmail($email, $nama, $data, $jenis, $subject)
    {
        // Mail::raw($text, function ($message) use ($email, $subject) {
        //     $message->to($email)
        //         ->subject($subject);
        // });

        try {
            Mail::send('system::template/layout/email', ['nama' => $nama, 'data' => $data, 'jenis' => $jenis], function ($message) use ($subject, $email) {
                $message->subject($subject);
                $message->to($email);
            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
}

#- nama hari
if (! function_exists('Hari')) {
    function Hari($str)
    {
        if ($str == 'Sun') $str = 'Minggu';
        if ($str == 'Mon') $str = 'Senin';
        if ($str == 'Tue') $str = 'Selasa';
        if ($str == 'Wed') $str = 'Rabu';
        if ($str == 'Thu') $str = 'Kamis';
        if ($str == 'Fri') $str = 'Jumat';
        if ($str == 'Sat') $str = 'Sabtu';
        return $str;
    }
}

#-- funsi nama bulan
if (! function_exists('Bulan')) {
    function Bulan($str)
    {
        if ($str == '1' or $str == '01') $str = 'Januari';
        elseif ($str == '2' or $str == '02') $str = 'Februari';
        elseif ($str == '3' or $str == '03') $str = 'Maret';
        elseif ($str == '4' or $str == '04') $str = 'April';
        elseif ($str == '5' or $str == '05') $str = 'Mei';
        elseif ($str == '6' or $str == '06') $str = 'Juni';
        elseif ($str == '7' or $str == '07') $str = 'Juli';
        elseif ($str == '8' or $str == '08') $str = 'Agustus';
        elseif ($str == '9' or $str == '09') $str = 'September';
        elseif ($str == '10') $str = 'Oktober';
        elseif ($str == '11') $str = 'November';
        elseif ($str == '12') $str = 'Desember';
        return $str;
    }
}

#-- Format TanggalInonesia
if (! function_exists('tglIndo')) {
    function tglIndo($str)
    {
        if ($str != '') :
            list($Thn, $Bln, $Tgl) = explode('-', $str, 3);
            $str = $Tgl . ' ' . Bulan($Bln) . ' ' . $Thn;
            return $str;
        else :
            return '';
        endif;
    }
}

#-- funsi nama bulan romawi
if (! function_exists('BulanRomawi')) {
    function BulanRomawi($str)
    {
        if ($str == '1' or $str == '01') $str = 'I';
        elseif ($str == '2' or $str == '02') $str = 'II';
        elseif ($str == '3' or $str == '03') $str = 'III';
        elseif ($str == '4' or $str == '04') $str = 'IV';
        elseif ($str == '5' or $str == '05') $str = 'V';
        elseif ($str == '6' or $str == '06') $str = 'VI';
        elseif ($str == '7' or $str == '07') $str = 'VII';
        elseif ($str == '8' or $str == '08') $str = 'VIII';
        elseif ($str == '9' or $str == '09') $str = 'IX';
        elseif ($str == '10') $str = 'X';
        elseif ($str == '11') $str = 'XI';
        elseif ($str == '12') $str = 'XII';
        return $str;
    }
}
