<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataMahasiswa extends Model
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
        return config('app.module.name') . '_data_mahasiswas';
    }

    // Ke User (Induk)
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
                    ['name' => 'nim', 'label' => 'NIM', 'type' => 'text', 'required' => true, 'col_size' => 6, 'readonly' => true],
                    ['name' => 'nik_ktp', 'label' => 'NIK (KTP)', 'type' => 'number', 'col_size' => 6],
                    ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'tgl_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'col_size' => 6],
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan'], 'col_size' => 6],
                    ['name' => 'agama', 'label' => 'Agama', 'type' => 'select', 'options' => ['Islam' => 'Islam', 'Kristen' => 'Kristen', 'Katolik' => 'Katolik'], 'col_size' => 6],
                ]
            ],
            'tab_alamat' => [
                'label' => '2. Kontak & Keluarga',
                'fields' => [
                    ['name' => 'no_hp', 'label' => 'No HP WA', 'type' => 'number', 'col_size' => 6],
                    ['name' => 'email_pribadi', 'label' => 'Email Pribadi', 'type' => 'email', 'col_size' => 6],
                    ['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'no_hp_ortu', 'label' => 'No HP WA Ortu', 'type' => 'number', 'col_size' => 6],
                    ['name' => 'alamat_lengkap', 'label' => 'Alamat Lengkap', 'type' => 'textarea', 'col_size' => 12],
                ]
            ]
        ];
    }
}
