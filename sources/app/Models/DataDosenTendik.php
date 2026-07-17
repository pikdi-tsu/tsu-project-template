<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Enums\StatusKaryawanEnum;

class DataDosenTendik extends Authenticatable
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tgl_lahir' => 'date',
        'status_karyawan' => StatusKaryawanEnum::class,
    ];

    public function getTable()
    {
        return config('app.table.data_dosen_tendiks');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getFormConfig()
    {
        return [
            // TAB 1: PROFIL & IDENTITAS
            'tab_profil' => [
                'label' => 'Profil & Identitas',
                'fields' => [
                    ['name' => 'nik', 'label' => 'NIK (Identitas Utama)', 'type' => 'text', 'col_size' => 4, 'required' => true],
                    ['name' => 'nama_lengkap', 'label' => 'Nama Lengkap', 'type' => 'text', 'col_size' => 8, 'required' => true],
                ]
            ],

            // TAB 2: KONTAK & KEPEGAWAIAN
            'tab_kepegawaian' => [
                'label' => 'Kontak & Kepegawaian',
                'fields' => [
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'col_size' => 6, 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
                    ['name' => 'no_hp', 'label' => 'No. HP / WhatsApp', 'type' => 'text', 'col_size' => 6, 'prefix' => 'wa.me/', 'placeholder' => 'Contoh: 628123456789'],

                    ['name' => 'unit_kerja', 'label' => 'Unit / Departemen', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'jabatan_aktif', 'label' => 'Jabatan Aktif', 'type' => 'text', 'col_size' => 6],
                    
                    ['name' => 'status_karyawan', 'label' => 'Status Kepegawaian', 'type' => 'text', 'col_size' => 6, 'readonly' => true],
                    ['name' => 'is_active', 'label' => 'Akun Aktif', 'type' => 'select', 'col_size' => 6, 'options' => [1 => 'AKTIF', 0 => 'NON-AKTIF'], 'readonly' => true],
                ]
            ]
        ];
    }
}
