<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id', 'payment_date', 'amount', 'method', 'reference',
        'bank_account_id', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function invoice()     { return $this->belongsTo(Invoice::class); }
    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
    public function recorder()    { return $this->belongsTo(User::class, 'recorded_by'); }
}
