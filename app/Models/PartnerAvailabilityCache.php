<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerAvailabilityCache extends Model
{
    protected $table = 'partner_availability_cache';

    protected $fillable = [
        'company_id',
        'branch_id',
        'category',
        'date',
        'available_count',
        'price_min',
        'price_max',
    ];

    protected $casts = [
        'date' => 'date',
        'available_count' => 'integer',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
