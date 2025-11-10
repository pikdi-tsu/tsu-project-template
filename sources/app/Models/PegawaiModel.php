<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PegawaiModel extends Model
{
    // use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'data_backup_pegawai';
    // protected $primaryKey = 'nip';
    // protected $keyType = 'string';
    public $incrementing = false;
    // protected $fillable = [
    //     'NIP',
    //     'NAMA',
    //     'HOMBASE',
    //     'JENIS KELAMIN',
    //     'TEMPAT LAHIR',
    //     'TANGGAL LAHIR',
    //     'AGAMA',
    //     'NIDN',
    //     'GELAR DEPAN',
    //     'GELAR BELAKANG',
    //     'GOLONGAN/PANGKAT',
    //     'JABATAN FUNGSIONAL',
    //     'JABATAN STRUKTURAL',
    //     'ALAMAT RUMAH',
    //     'NO. TELEPON',
    //     'EMAIL PRIBADI',
    //     'EMAIL KAMPUS',
    // ];
}
