<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    protected $fillable = [
        'employee_id', 'submitted_by', 'approver_id', 'reviewed_by',
        'title', 'description', 'category', 'amount',
        'status', 'rejection_reason', 'reviewed_at', 'payment_date',
        'transfer_proof_path',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'reviewed_at' => 'datetime',
        'payment_date' => 'date',
    ];

    public static array $categories = [
        'transport'   => 'Transport',
        'makan'       => 'Makan & Minum',
        'kesehatan'   => 'Kesehatan',
        'pelatihan'   => 'Pelatihan / Training',
        'akomodasi'   => 'Akomodasi',
        'komunikasi'  => 'Komunikasi',
        'lainnya'     => 'Lainnya',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents()
    {
        return $this->hasMany(ReimbursementDocument::class)->latest();
    }

    public function isPending(): bool  { return $this->status === 'pending';  }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function hasTransferProof(): bool
    {
        return !empty($this->transfer_proof_path);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => ucfirst($this->status),
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

    public function categoryLabel(): string
    {
        return self::$categories[$this->category] ?? ucfirst($this->category ?? '-');
    }
}
