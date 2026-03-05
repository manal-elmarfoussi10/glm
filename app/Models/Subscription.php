<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'started_at',
        'next_renewal_at',
        'notes',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'started_at' => 'datetime',
        'next_renewal_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Sync subscription state to company (plan_id, subscription_status, trial_ends_at, etc.)
     */
    public function syncToCompany(): void
    {
        $this->company->update([
            'plan_id' => $this->plan_id,
            'subscription_status' => $this->status,
            'trial_ends_at' => $this->trial_ends_at,
            'subscription_started_at' => $this->started_at,
            'next_billing_date' => $this->next_renewal_at,
        ]);
    }
}
