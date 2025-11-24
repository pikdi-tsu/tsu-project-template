<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UserDosenTendik extends Authenticatable
{
    use Notifiable;

    protected $guard = 'dosen_tendik';

    protected $table = 'users_dosen_tendik';

    protected $fillable = [
        'nik',
        'name',
        'email',
        'password',
        'q1',
        'a1',
        'q2',
        'a2',
        'forgot_password_send_email',
        'created_by',
        'updated_by',
        'isactive',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'a1',
        'a2',
    ];

    /**
     * Relasi ke profil Siakad
     */
    public function profil()
    {
        // 'nim' di tabel ini, nyambung ke 'nim' di tabel siakad_mahasiswa
        return $this->hasOne(SiakadDosenTendik::class, 'nim', 'nim');
    }
}
