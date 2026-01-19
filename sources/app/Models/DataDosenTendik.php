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
}
