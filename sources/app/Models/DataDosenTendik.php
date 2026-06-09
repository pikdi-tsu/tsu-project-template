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

    protected function namaLengkap(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $depan = !empty($attributes['gelar_depan']) ? trim($attributes['gelar_depan']) . ' ' : '';
                $belakang = !empty($attributes['gelar_belakang']) ? ', ' . trim($attributes['gelar_belakang']) : '';
                
                return $depan . $attributes['nama'] . $belakang;
            },
        );
    }

    public static function getFormConfig()
    {
        return [
            // TAB 1: PROFIL & IDENTITAS
            'tab_profil' => [
                'label' => 'Profil & Identitas',
                'fields' => [
                    ['name' => 'gelar_depan', 'label' => 'Gelar Depan', 'type' => 'text', 'col_size' => 3],
                    ['name' => 'nama', 'label' => 'Nama Lengkap', 'type' => 'text', 'col_size' => 6, 'required' => true],
                    ['name' => 'gelar_belakang', 'label' => 'Gelar Belakang', 'type' => 'text', 'col_size' => 3],

                    ['name' => 'nik', 'label' => 'NIK (Identitas Utama)', 'type' => 'text', 'col_size' => 4, 'required' => true],
                    ['name' => 'nidn', 'label' => 'NIDN', 'type' => 'text', 'col_size' => 4],
                    ['name' => 'nip', 'label' => 'NIP', 'type' => 'text', 'col_size' => 4],
                ]
            ],

            // TAB 2: KONTAK & KEPEGAWAIAN
            'tab_kepegawaian' => [
                'label' => 'Kontak & Kepegawaian',
                'fields' => [
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'col_size' => 6, 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
                    ['name' => 'no_hp', 'label' => 'No. HP / WhatsApp', 'type' => 'text', 'col_size' => 6, 'prefix' => 'wa.me/', 'placeholder' => 'Contoh: 628123456789'],

                    ['name' => 'unit', 'label' => 'Unit / Departemen', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'status_karyawan', 'label' => 'Status Kepegawaian', 'type' => 'text', 'col_size' => 6, 'readonly' => true],
                    ['name' => 'is_active', 'label' => 'Akun Aktif', 'type' => 'select', 'col_size' => 12, 'options' => [1 => 'AKTIF', 0 => 'NON-AKTIF'], 'readonly' => true],
                ]
            ],

            // TAB 3: KEPANGKATAN
            'tab_kepangkatan' => [
                'label' => 'Kepangkatan',
                'fields' => [
                    // --- Struktural ---
                    ['name' => 'jabatan_struktural', 'label' => 'Jabatan Struktural', 'type' => 'text', 'col_size' => 12],

                    // --- Fungsional ---
                    ['name' => 'jabatan_fungsional', 'label' => 'Jabatan Fungsional', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'pangkat_jabatan_fungsional', 'label' => 'Pangkat / Golongan', 'type' => 'text', 'col_size' => 6],
                ]
            ]
        ];
    }
}
