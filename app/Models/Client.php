<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'company_id', 'name', 'contact_person', 'email', 'phone',
        'npwp', 'address', 'notes', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function company()    { return $this->belongsTo(Company::class); }
    public function projects()   { return $this->hasMany(Project::class); }
    public function quotations() { return $this->hasMany(Quotation::class); }
    public function invoices()   { return $this->hasMany(Invoice::class); }
}
