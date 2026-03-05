<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrustVerification extends Model
{
    protected $fillable = [
        'client_identifier',
        'verified_identity',
        'successful_rentals_count',
    ];

    protected $casts = [
        'verified_identity' => 'boolean',
    ];

    public function hasTrustBadge(): bool
    {
        return $this->verified_identity || $this->successful_rentals_count > 0;
    }
}
