<?php

namespace App\Enums;

enum EmployeeCategory: string
{
    case Tetap       = 'tetap';
    case Kontrak     = 'kontrak';
    case Proyek      = 'proyek';
    case Probasi     = 'probasi';
    case Magang      = 'magang';
    case ParuhWaktu  = 'paruh_waktu';

    public function label(): string
    {
        return match($this) {
            self::Tetap      => 'Karyawan Tetap',
            self::Kontrak    => 'Kontrak',
            self::Proyek     => 'Berdasarkan Proyek',
            self::Probasi    => 'Masa Percobaan',
            self::Magang     => 'Magang',
            self::ParuhWaktu => 'Paruh Waktu',
        };
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::Tetap      => 'success',
            self::Kontrak    => 'warning',
            self::Proyek     => 'info',
            self::Probasi    => 'secondary',
            self::Magang     => 'primary',
            self::ParuhWaktu => 'dark',
        };
    }
}
