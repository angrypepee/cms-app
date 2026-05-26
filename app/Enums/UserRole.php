<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin          = 'admin';
    case HR             = 'hr';
    case SignatureAdmin = 'signature_admin';
    case Employee       = 'employee';

    public function label(): string
    {
        return match($this) {
            self::Admin          => 'Administrator',
            self::HR             => 'HRD / Staff',
            self::SignatureAdmin => 'Signature Admin',
            self::Employee       => 'Karyawan',
        };
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::Admin          => 'danger',
            self::HR             => 'primary',
            self::SignatureAdmin => 'success',
            self::Employee       => 'secondary',
        };
    }

    public function canSign(): bool
    {
        return in_array($this, [self::Admin, self::SignatureAdmin]);
    }

    public function canManageUsers(): bool
    {
        return $this === self::Admin;
    }
}
