<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppreciationClaim extends Model
{
    protected $fillable = [
        'appreciation_budget_id', 'submitted_by', 'reviewed_by',
        'title', 'description', 'amount',
        'status', 'rejection_reason', 'reviewed_at', 'payment_date',
        'transfer_proof_path',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'reviewed_at'  => 'datetime',
        'payment_date' => 'date',
    ];

    // ── Relationships ────────────────────────────────────────
    public function budget()
    {
        return $this->belongsTo(AppreciationBudget::class, 'appreciation_budget_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents()
    {
        return $this->hasMany(AppreciationClaimDocument::class)->latest();
    }

    // ── Helpers ──────────────────────────────────────────────
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function hasTransferProof(): bool { return !empty($this->transfer_proof_path); }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'pending'  => 'badge-pending',
            'approved' => 'badge-approved',
            'rejected' => 'badge-rejected',
            default    => 'bg-secondary',
        };
    }
}
