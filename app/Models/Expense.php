<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    public const CATEGORY_MAINTENANCE = 'maintenance';
    public const CATEGORY_REPAIR = 'repair';
    public const CATEGORY_ACCIDENT = 'accident';
    public const CATEGORY_CLEANING = 'cleaning';
    public const CATEGORY_OTHER = 'other';

    public const CATEGORIES = [
        self::CATEGORY_MAINTENANCE => 'Entretien',
        self::CATEGORY_REPAIR => 'Réparation',
        self::CATEGORY_ACCIDENT => 'Accident',
        self::CATEGORY_CLEANING => 'Nettoyage',
        self::CATEGORY_OTHER => 'Autre',
    ];

    protected $fillable = [
        'company_id',
        'branch_id',
        'vehicle_id',
        'category',
        'amount',
        'date',
        'description',
        'attachment_path',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
