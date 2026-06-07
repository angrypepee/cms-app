<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractDocument extends Model
{
    protected $fillable = [
        'employee_id', 'status', 'rejection_reason', 'rejected_at', 'rejected_by',
        'created_by', 'contract_number', 'contract_date', 'location',
        'first_party_name', 'first_party_position', 'first_party_company', 'first_party_address',
        'penandatangan_p1_name', 'penandatangan_p1_position',
        'second_party_name', 'second_party_address', 'second_party_ktp',
        'project_name', 'scope_of_work', 'duration_text', 'start_date', 'end_date',
        'contract_value', 'contract_value_text', 'payment_method', 'payment_terms',
        'base_salary', 'salary_components',
        'rights_obligations', 'hki_terms', 'nda_terms', 'sanctions_terms', 'dispute_terms',
        'bank_name', 'bank_account', 'bank_account_name',
        'signed_by', 'signed_at',
        'signature_number', 'signature_qr_data_uri',
        'signed_by_employee', 'signed_at_employee',
        'signature_number_employee', 'signature_qr_employee',
        'file_path', 'original_name', 'mime_type', 'file_size', 'notes',
    ];

    protected $casts = [
        'contract_date'          => 'date',
        'start_date'             => 'date',
        'end_date'               => 'date',
        'contract_value'         => 'decimal:2',
        'base_salary'            => 'decimal:2',
        'salary_components'      => 'array',
        'signed_at'              => 'datetime',
        'signed_at_employee'     => 'datetime',
        'rejected_at'            => 'datetime',
        'file_size'              => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function signerEmployee()
    {
        return $this->belongsTo(User::class, 'signed_by_employee');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function statusBadge(): array
    {
        return match($this->status ?? 'active') {
            'cancelled' => ['Dibatalkan', 'danger'],
            'rejected'  => ['Ditolak', 'warning'],
            default     => $this->isFullySigned()
                ? ['Selesai', 'success']
                : ($this->isSigned() ? ['Menunggu Tanda Tangan Karyawan', 'primary'] : ['Aktif', 'secondary']),
        };
    }

    public function isSigned(): bool
    {
        return $this->signed_by !== null && $this->signed_at !== null;
    }

    public function isSignedByEmployee(): bool
    {
        return $this->signed_by_employee !== null && $this->signed_at_employee !== null;
    }

    public function isFullySigned(): bool
    {
        return $this->isSigned() && $this->isSignedByEmployee();
    }

    public function fileSizeFormatted(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 1) . ' MB';
        if ($bytes >= 1_024) return round($bytes / 1_024, 0) . ' KB';
        return $bytes . ' B';
    }

    public function paymentLabel(): string
    {
        return $this->payment_method ?: 'Lump Sum';
    }
}