<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationInspection extends Model
{
    public const TYPE_OUT = 'out';
    public const TYPE_IN = 'in';

    public const FUEL_LEVELS = ['vide' => 'Vide', '1/4' => '1/4', '1/2' => '1/2', '3/4' => '3/4', 'plein' => 'Plein'];

    public const DEPOSIT_REFUND_PENDING = 'pending';
    public const DEPOSIT_REFUND_REFUNDED = 'refunded';
    public const DEPOSIT_REFUND_RETAINED = 'retained';
    public const DEPOSIT_REFUND_PARTIAL = 'partial';

    protected $fillable = [
        'reservation_id',
        'type',
        'inspected_at',
        'mileage',
        'fuel_level',
        'notes',
        'damage_checklist',
        'new_damages',
        'extra_fees',
        'deposit_refund_status',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
        'mileage' => 'integer',
        'damage_checklist' => 'array',
        'extra_fees' => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function photos()
    {
        return $this->hasMany(ReservationInspectionPhoto::class, 'reservation_inspection_id');
    }

    public function isOut(): bool
    {
        return $this->type === self::TYPE_OUT;
    }

    public function isIn(): bool
    {
        return $this->type === self::TYPE_IN;
    }
}
