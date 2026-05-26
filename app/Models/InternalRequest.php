<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalRequest extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'subject', 'message',
        'status', 'admin_response', 'responded_by', 'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'permohonan'       => 'Permohonan',
            'pendanaan'        => 'Pendanaan',
            'surat_keterangan' => 'Surat Keterangan',
            'pengaduan'        => 'Pengaduan',
            'lainnya'          => 'Lainnya',
            default            => $this->type,
        };
    }

    public function typeBadgeClass(): string
    {
        return match($this->type) {
            'permohonan'       => 'badge-paid',
            'pendanaan'        => 'badge-published',
            'surat_keterangan' => 'badge-pending',
            'pengaduan'        => 'badge-rejected',
            default            => 'badge-pending',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'   => 'Menunggu',
            'diproses'  => 'Diproses',
            'selesai'   => 'Selesai',
            'ditolak'   => 'Ditolak',
            default     => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'pending'  => 'badge-pending',
            'diproses' => 'badge-paid',
            'selesai'  => 'badge-approved',
            'ditolak'  => 'badge-rejected',
            default    => 'badge-pending',
        };
    }
}
