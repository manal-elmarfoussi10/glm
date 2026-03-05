<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyClientFlag extends Model
{
    protected $fillable = [
        'company_id',
        'client_identifier',
        'reason',
        'notes',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
