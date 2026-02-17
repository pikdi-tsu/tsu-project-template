<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, Notifiable, HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'sso_id',
        'username',
        'name',
        'email',
        'avatar_url',
        'unit',
        'isactive',
        'sso_access_token',
        'sso_refresh_token',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'sso_access_token',
        'sso_refresh_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'isactive' => 'boolean',
    ];

    public function getTable()
    {
        return config('auth.providers.users.table');
    }

    // Relasi ke Profil Mahasiswa
    public function mahasiswa()
    {
        return $this->hasOne(DataMahasiswa::class, 'user_id');
    }

    // Relasi ke Profil Dosen/Tendik
    public function dosenTendik()
    {
        return $this->hasOne(DataDosenTendik::class, 'user_id');
    }

    /**
     * Cek apakah User ini Mahasiswa
     * Cara pakai: if ($user->isMahasiswa()) { ... }
     */
    public function isMahasiswa()
    {
        return $this->hasRole('mahasiswa');
    }

    /**
     * Cek apakah User ini Dosen atau Tendik
     */
    public function isDosenTendik()
    {
        return $this->hasRole(['dosen', 'tendik', 'admin_prodi', 'dekan']);
    }

    /**
     * Magic Accessor: Ambil data profil aktif secara otomatis
     * Cara pakai: $user->profil->nim atau $user->profil->nama_lengkap
     */
    public function getProfilAttribute()
    {
        if ($this->isMahasiswa()) {
            return $this->mahasiswa;
        }

        if ($this->isDosenTendik()) {
            return $this->dosenTendik;
        }
        return null;
    }

    public function getProfilePhotoUrlAttribute()
    {
        $path = $this->avatar_url;

        if (empty($path)) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=FFFFFF&background=2d394a';
        }

        if (str_starts_with($path, 'https')) {
            return $path;
        }

        return asset('storage/' . $path);
    }

    // Sync Delete
    protected static function booted()
    {
        static::deleting(static function ($user) {
            if ($user->mahasiswa) {
                $user->mahasiswa->delete();
            }

            if ($user->dosen) {
                $user->dosen->delete();
            }
        });
    }
}
