<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'ice',
        'phone',
        'email',
        'city',
        'address',
        'status',
        'onboarding_completed_at',
        'plan',
        'plan_id',
        'trial_ends_at',
        'subscription_status',
        'subscription_started_at',
        'next_billing_date',
        'default_contract_template_id',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_started_at' => 'datetime',
        'next_billing_date' => 'datetime',
        'onboarding_completed_at' => 'datetime',
    ];

    /**
     * Whether the company should see the onboarding wizard (no branch yet, no vehicle yet, or never completed).
     */
    public function needsOnboarding(): bool
    {
        if ($this->onboarding_completed_at) {
            return false;
        }
        if ($this->branches()->count() === 0) {
            return true;
        }
        if ($this->vehicles()->count() === 0) {
            return true;
        }
        return false;
    }

    /**
     * Checklist items still to do for setup (for dashboard card). Only when onboarding not completed.
     */
    public function onboardingChecklist(): array
    {
        $items = [];
        if ($this->branches()->count() === 0) {
            $items[] = ['key' => 'branch', 'label' => 'Créer votre première agence', 'route' => 'app.companies.branches.create', 'done' => false];
        } else {
            $items[] = ['key' => 'branch', 'label' => 'Première agence créée', 'route' => null, 'done' => true];
        }
        if ($this->vehicles()->count() === 0) {
            $items[] = ['key' => 'vehicle', 'label' => 'Ajouter votre premier véhicule', 'route' => 'app.companies.vehicles.create', 'done' => false];
        } else {
            $items[] = ['key' => 'vehicle', 'label' => 'Premier véhicule ajouté', 'route' => null, 'done' => true];
        }
        $partnerSetting = $this->partnerSetting;
        $partnerDone = $partnerSetting && $partnerSetting->share_enabled;
        if (! $partnerDone) {
            $items[] = ['key' => 'partner', 'label' => 'Rejoindre le réseau partenaires GLM', 'route' => 'app.companies.partner-settings.edit', 'done' => false];
        } else {
            $items[] = ['key' => 'partner', 'label' => 'Réseau partenaires activé', 'route' => null, 'done' => true];
        }
        return $items;
    }

    public function planRelation()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Get or create the current subscription (one per company). Seeds from company data if new.
     */
    public function getOrCreateSubscription(): Subscription
    {
        $sub = $this->subscription;
        if ($sub) {
            return $sub;
        }
        $sub = Subscription::firstOrCreate(
            ['company_id' => $this->id],
            [
                'plan_id' => $this->plan_id,
                'status' => $this->subscription_status ?? 'trial',
                'trial_ends_at' => $this->trial_ends_at,
                'started_at' => $this->subscription_started_at ?? $this->created_at,
                'next_renewal_at' => $this->next_billing_date,
            ]
        );
        return $sub;
    }

    public function defaultContractTemplate()
    {
        return $this->belongsTo(ContractTemplate::class, 'default_contract_template_id');
    }

    public function contractTemplates()
    {
        return $this->hasMany(ContractTemplate::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clientFlags()
    {
        return $this->hasMany(CompanyClientFlag::class, 'company_id');
    }

    public function vehicles()
    {
        return $this->hasManyThrough(Vehicle::class, Branch::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function alertDismissals()
    {
        return $this->hasMany(CompanyAlertDismissal::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(ReservationPayment::class, Reservation::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function partnerSetting()
    {
        return $this->hasOne(CompanyPartnerSetting::class);
    }

    public function partnerAvailabilityCache()
    {
        return $this->hasMany(PartnerAvailabilityCache::class);
    }

    public function partnerRequestsReceived()
    {
        return $this->hasMany(PartnerRequest::class, 'partner_company_id');
    }

    public function partnerRequestsSent()
    {
        return $this->hasMany(PartnerRequest::class, 'requester_company_id');
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
