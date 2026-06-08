<?php

namespace App\Enums;

enum StatusKaryawanEnum: string
{
    case TETAP = 'TETAP';
    case KONTRAK = 'KONTRAK';

    public function label(): string
    {
        return match($this) {
            self::TETAP => 'Tetap',
            self::KONTRAK => 'Kontrak',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::TETAP => 'badge-primary',
            self::KONTRAK => 'badge-info',
        };
    }
}
