<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanyaanKeamanan extends Model
{
    protected $table = 'pertanyaan_keamanans';
    // Sesuai kolom di DB kamu
    protected $fillable = ['jenis', 'pertanyaan'];
}
