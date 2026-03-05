<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationInspectionPhoto extends Model
{
    protected $fillable = [
        'reservation_inspection_id',
        'path',
        'caption',
    ];

    public function reservationInspection()
    {
        return $this->belongsTo(ReservationInspection::class);
    }
}
