<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'company_id', 'bank_name', 'account_name', 'account_number',
        'branch', 'swift_code', 'is_default', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function company()  { return $this->belongsTo(Company::class); }
    public function payments() { return $this->hasMany(InvoicePayment::class); }

    public function label(): string
    {
        return "{$this->bank_name} {$this->account_number} a.n. {$this->account_name}";
    }
}
