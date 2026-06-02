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
        return config('app.table.data_dosen_tendiks');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getFormConfig()
    {
        return [
            // TAB 1: PROFIL & KAMPUS
            'tab_profil' => [
                'label' => 'Profil & Kampus',
                'fields' => [
                    ['name' => 'gelar_depan', 'label' => 'Gelar Depan', 'type' => 'text', 'col_size' => 3],
                    ['name' => 'nama', 'label' => 'Nama Lengkap', 'type' => 'text', 'col_size' => 6, 'required' => true],
                    ['name' => 'gelar_belakang', 'label' => 'Gelar Belakang', 'type' => 'text', 'col_size' => 3],

                    ['name' => 'nik', 'label' => 'NIK (Identitas Utama)', 'type' => 'text', 'col_size' => 6, 'required' => true],
                    ['name' => 'nip', 'label' => 'NIP', 'type' => 'text', 'col_size' => 6],

                    ['name' => 'nidn', 'label' => 'NIDN', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'nuptk', 'label' => 'NUPTK', 'type' => 'text', 'col_size' => 6],

                    ['name' => 'keilmuan_inti', 'label' => 'Keilmuan Inti', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'status_karyawan', 'label' => 'Status Karyawan', 'type' => 'text', 'col_size' => 6, 'readonly' => true, 'default' => 'AKTIF'],
                ]
            ],

            // TAB 2: PRIBADI & KONTAK
            'tab_pribadi' => [
                'label' => 'Pribadi & Kontak',
                'fields' => [
                    ['name' => 'nik_ktp', 'label' => 'NIK KTP Fisik', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'no_npwp', 'label' => 'No. NPWP', 'type' => 'text', 'col_size' => 6],

                    ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'tgl_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'col_size' => 6],

                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'col_size' => 4, 'options' => [
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan'
                    ]],
                    ['name' => 'status_perkawinan', 'label' => 'Status Perkawinan', 'type' => 'select', 'col_size' => 4, 'options' => [
                        'Belum Menikah' => 'Belum Menikah',
                        'Menikah' => 'Menikah',
                        'Cerai Hidup' => 'Cerai Hidup',
                        'Cerai Mati' => 'Cerai Mati'
                    ]],
                    ['name' => 'jumlah_anak', 'label' => 'Jumlah Anak', 'type' => 'number', 'col_size' => 4],

                    ['name' => 'no_hp', 'label' => 'No. HP / WhatsApp', 'type' => 'text', 'col_size' => 12, 'prefix' => 'wa.me/', 'placeholder' => 'Contoh: 628123456789 (Gunakan 62, bukan 0)'],

                    ['name' => 'alamat_lengkap', 'label' => 'Alamat Sesuai KTP', 'type' => 'textarea', 'col_size' => 6],
                    ['name' => 'alamat_domisili', 'label' => 'Alamat Domisili Saat Ini', 'type' => 'textarea', 'col_size' => 6],
                ]
            ],

            // TAB 3: KEPANGKATAN
            'tab_kepangkatan' => [
                'label' => 'Kepangkatan',
                'fields' => [
                    // --- Struktural ---
                    ['name' => 'jabatan_struktural', 'label' => 'Jabatan Struktural', 'type' => 'text', 'col_size' => 12],
                    ['name' => 'tgl_mulai_jabatan_struktural', 'label' => 'Tgl Mulai Struktural', 'type' => 'date', 'col_size' => 6],
                    ['name' => 'periode_jabatan_struktural', 'label' => 'Periode Struktural (Cth: 2024-2028)', 'type' => 'text', 'col_size' => 6],

                    // --- Fungsional ---
                    ['name' => 'jabatan_fungsional', 'label' => 'Jabatan Fungsional', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'pangkat_jabatan_fungsional', 'label' => 'Pangkat / Golongan', 'type' => 'text', 'col_size' => 6],

                    ['name' => 'sk_jabatan_fungsional', 'label' => 'Nomor SK Fungsional', 'type' => 'text', 'col_size' => 6],
                    ['name' => 'tmt_jabatan_fungsional', 'label' => 'TMT Fungsional', 'type' => 'date', 'col_size' => 6],
                ]
            ],

            // TAB 4: DOKUMEN
            'tab_dokumen' => [
                'label' => 'Dokumen Berkas',
                'fields' => [
                    ['name' => 'scan_ktp', 'label' => 'Link Scan KTP (URL)', 'type' => 'text', 'col_size' => 12],
                    ['name' => 'scan_kk', 'label' => 'Link Scan KK (URL)', 'type' => 'text', 'col_size' => 12],
                    ['name' => 'scan_npwp', 'label' => 'Link Scan NPWP (URL)', 'type' => 'text', 'col_size' => 12],
                    ['name' => 'scan_ijazah', 'label' => 'Link Scan Ijazah (URL)', 'type' => 'text', 'col_size' => 12],
                ]
            ]
        ];
    }
}
