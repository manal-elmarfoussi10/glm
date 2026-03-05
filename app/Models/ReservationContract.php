<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationContract extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_GENERATED = 'generated';
    public const STATUS_SIGNED = 'signed';

    protected $fillable = [
        'reservation_id',
        'contract_template_id',
        'snapshot_html',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function contractTemplate()
    {
        return $this->belongsTo(ContractTemplate::class, 'contract_template_id');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
