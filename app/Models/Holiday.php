<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['name', 'date', 'type', 'description', 'company_id', 'is_active'];

    protected $casts = [
        'date'      => 'date',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'national' => 'Hari Libur Nasional',
            'company'  => 'Hari Libur Perusahaan',
            default    => $this->type,
        };
    }

    public function typeBadgeClass(): string
    {
        return match($this->type) {
            'national' => 'badge-rejected',
            'company'  => 'badge-paid',
            default    => 'badge-pending',
        };
    }
}
