<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'author_id', 'title', 'content', 'company_id',
        'is_pinned', 'published_at', 'expires_at',
    ];

    protected $casts = [
        'is_pinned'    => 'boolean',
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lt(now());
    }

    public function isVisibleTo(Employee $employee): bool
    {
        if ($this->company_id === null) return true;
        return $this->company_id === $employee->company_id;
    }

    /** Scope: active, published, not expired */
    public function scopeVisible($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
