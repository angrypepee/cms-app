<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_number', 'client_id', 'project_id', 'company_id',
        'issue_date', 'valid_until', 'subject',
        'subtotal', 'discount', 'tax_percent', 'tax_amount', 'total',
        'status', 'share_token', 'sent_at', 'viewed_at',
        'notes', 'terms', 'created_by',
    ];

    protected $casts = [
        'issue_date'   => 'date',
        'valid_until'  => 'date',
        'sent_at'      => 'datetime',
        'viewed_at'    => 'datetime',
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax_percent'  => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total'        => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $q) {
            if (empty($q->share_token)) {
                $q->share_token = Str::random(40);
            }
        });
    }

    public function client()  { return $this->belongsTo(Client::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function items()   { return $this->hasMany(QuotationItem::class)->orderBy('sort_order'); }
    public function invoices(){ return $this->hasMany(Invoice::class); }

    public static function generateNumber(): string
    {
        $prefix = 'QUO-' . now()->format('Ym');
        $last   = static::where('quotation_number', 'like', $prefix . '-%')->orderByDesc('id')->first();
        $seq    = $last ? (int) substr($last->quotation_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function recalculate(): void
    {
        $subtotal   = $this->items()->sum('amount');
        $afterDisc  = max(0, $subtotal - (float) $this->discount);
        $taxAmount  = round($afterDisc * ((float) $this->tax_percent / 100), 2);
        $this->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'total'      => $afterDisc + $taxAmount,
        ]);
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'draft'     => ['Draft', 'secondary'],
            'sent'      => ['Terkirim', 'primary'],
            'accepted'  => ['Disetujui', 'success'],
            'rejected'  => ['Ditolak', 'danger'],
            'expired'   => ['Kadaluarsa', 'warning'],
            'converted' => ['Jadi Invoice', 'info'],
            default     => [ucfirst($this->status), 'secondary'],
        };
    }

    public function publicUrl(): string
    {
        return route('public.quotation', $this->share_token);
    }
}
