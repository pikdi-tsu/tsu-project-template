<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DataDosenTendik extends Authenticatable
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tgl_lahir' => 'date',
    ];

    public function getTable()
    {
        return config('app.module.name') . '_data_dosen_tendiks';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getFormConfig()
    {
        return [
            'tab_pribadi' => [
                'label' => '1. Data Pribadi',
                'fields' => [
                    ['name' => 'nik', 'label' => 'NIK', 'type' => 'text', 'col_size' => 6, 'readonly' => true],
                    ['name' => 'nidn', 'label' => 'NIDN', 'type' => 'text', 'col_size' => 6, 'readonly' => true],
                    ['name' => 'gelar_depan', 'label' => 'Gelar Depan', 'type' => 'text', 'col_size' => 4],
                    ['name' => 'gelar_belakang', 'label' => 'Gelar Belakang', 'type' => 'text', 'col_size' => 4],
                    ['name' => 'jabatan_fungsional', 'label' => 'Jabatan Fungsional', 'type' => 'text', 'col_size' => 4],
                    ['name' => 'nik_ktp', 'label' => 'NIK KTP', 'type' => 'text', 'col_size' => 4],
                    ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'col_size' => 4],
                    ['name' => 'tgl_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'col_size' => 4],
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'date', 'col_size' => 4],
                ]
            ],
            'tab_alamat' => [
                'label' => '2. Kontak & Alamat',
                'fields' => [
                    ['name' => 'no_hp', 'label' => 'Nomor HP', 'type' => 'number', 'col_size' => 12],
                    ['name' => 'alamat_domisili', 'label' => 'Alamat Domisili', 'type' => 'textarea', 'col_size' => 12],
                ]
            ]
        ];
    }
}
