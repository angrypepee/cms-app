<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'title', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
            'is_active'         => 'boolean',
        ];
    }

    // ── Role helpers ────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    public function isStaff(): bool
    {
        return !$this->isEmployee();
    }

    public function isSignatureAdmin(): bool
    {
        return in_array($this->role, [UserRole::SignatureAdmin, UserRole::Admin]);
    }

    public function canSign(): bool
    {
        return $this->role?->canSign() ?? false;
    }

    public function canManageUsers(): bool
    {
        return $this->role?->canManageUsers() ?? false;
    }

    // ── Relationships ────────────────────────────────────────
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function signedSlips()
    {
        return $this->hasMany(PayrollSlip::class, 'signed_by');
    }

    public function assignedReimbursements()
    {
        return $this->hasMany(Reimbursement::class, 'approver_id');
    }
}
