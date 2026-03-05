<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAlertDismissal extends Model
{
    public const ACTION_DONE = 'done';
    public const ACTION_SNOOZE = 'snooze';

    protected $fillable = [
        'company_id',
        'identifier',
        'action',
        'snooze_until',
        'dismissed_at',
        'user_id',
    ];

    protected $casts = [
        'snooze_until' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
