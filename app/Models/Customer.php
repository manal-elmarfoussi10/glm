<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'cin',
        'phone',
        'email',
        'city',
        'address',
        'driving_license_number',
        'driving_license_expiry',
        'cin_front_path',
        'cin_back_path',
        'license_document_path',
        'internal_notes',
        'is_flagged',
    ];

    protected $casts = [
        'driving_license_expiry' => 'date',
        'is_flagged' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
