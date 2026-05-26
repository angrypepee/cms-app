<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'code', 'client_id', 'company_id', 'name', 'description',
        'start_date', 'end_date', 'budget', 'status', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'budget'     => 'decimal:2',
    ];

    public function client()     { return $this->belongsTo(Client::class); }
    public function company()    { return $this->belongsTo(Company::class); }
    public function quotations() { return $this->hasMany(Quotation::class); }
    public function invoices()   { return $this->hasMany(Invoice::class); }

    public static function generateCode(): string
    {
        $prefix = 'PRJ-' . now()->format('Ym');
        $last   = static::where('code', 'like', $prefix . '-%')->orderByDesc('id')->first();
        $seq    = $last ? (int) substr($last->code, -4) + 1 : 1;
        return $prefix . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'planning'  => ['Perencanaan', 'secondary'],
            'active'    => ['Berjalan', 'primary'],
            'on_hold'   => ['Ditunda', 'warning'],
            'completed' => ['Selesai', 'success'],
            'cancelled' => ['Dibatalkan', 'danger'],
            default     => [ucfirst($this->status), 'secondary'],
        };
    }
}
