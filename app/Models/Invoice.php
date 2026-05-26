<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'client_id', 'project_id', 'quotation_id', 'company_id', 'bank_account_id',
        'issue_date', 'due_date', 'subject',
        'subtotal', 'discount', 'tax_percent', 'tax_amount', 'total', 'paid_amount',
        'status', 'share_token', 'sent_at', 'viewed_at',
        'payment_date', 'payment_reference', 'payment_method',
        'notes', 'terms', 'created_by',
    ];

    protected $casts = [
        'issue_date'   => 'date',
        'due_date'     => 'date',
        'payment_date' => 'date',
        'sent_at'      => 'datetime',
        'viewed_at'    => 'datetime',
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax_percent'  => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total'        => 'decimal:2',
        'paid_amount'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $inv) {
            if (empty($inv->share_token)) {
                $inv->share_token = Str::random(40);
            }
        });
    }

    public function client()      { return $this->belongsTo(Client::class); }
    public function project()     { return $this->belongsTo(Project::class); }
    public function quotation()   { return $this->belongsTo(Quotation::class); }
    public function company()     { return $this->belongsTo(Company::class); }
    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
    public function creator()     { return $this->belongsTo(User::class, 'created_by'); }
    public function items()       { return $this->hasMany(InvoiceItem::class)->orderBy('sort_order'); }
    public function payments()    { return $this->hasMany(InvoicePayment::class)->orderByDesc('payment_date')->orderByDesc('id'); }

    public static function generateNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ym');
        $last   = static::where('invoice_number', 'like', $prefix . '-%')->orderByDesc('id')->first();
        $seq    = $last ? (int) substr($last->invoice_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /** Recompute totals + sync paid_amount from payments table + auto status. */
    public function recalculate(): void
    {
        $subtotal   = $this->items()->sum('amount');
        $afterDisc  = max(0, $subtotal - (float) $this->discount);
        $taxAmount  = round($afterDisc * ((float) $this->tax_percent / 100), 2);
        $total      = $afterDisc + $taxAmount;

        $paidFromTable = (float) $this->payments()->sum('amount');
        $paid = $paidFromTable > 0 ? $paidFromTable : (float) $this->paid_amount;

        $status = $this->status;
        if (!in_array($status, ['draft','cancelled'])) {
            if ($paid >= $total && $total > 0)                      $status = 'paid';
            elseif ($paid > 0)                                       $status = 'partial';
            elseif ($this->due_date && $this->due_date->isPast())    $status = 'overdue';
            elseif (in_array($status, ['paid','partial','overdue'])) $status = 'sent';
        }

        $this->update([
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmount,
            'total'       => $total,
            'paid_amount' => $paid,
            'status'      => $status,
        ]);
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'draft'     => ['Draft', 'secondary'],
            'sent'      => ['Terkirim', 'primary'],
            'partial'   => ['Sebagian Dibayar', 'warning'],
            'paid'      => ['Lunas', 'success'],
            'overdue'   => ['Jatuh Tempo', 'danger'],
            'cancelled' => ['Dibatalkan', 'dark'],
            default     => [ucfirst($this->status), 'secondary'],
        };
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->total - (float) $this->paid_amount;
    }

    public function publicUrl(): string
    {
        return route('public.invoice', $this->share_token);
    }
}
