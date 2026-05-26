<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = ['quotation_id','description','quantity','unit','unit_price','amount','sort_order'];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount'     => 'decimal:2',
    ];

    public function quotation() { return $this->belongsTo(Quotation::class); }
}
