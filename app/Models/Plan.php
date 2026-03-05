<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'monthly_price',
        'yearly_price',
        'trial_days',
        'limit_vehicles',
        'limit_users',
        'limit_branches',
        'ai_access',
        'custom_contracts',
        'is_active',
        'features_limits',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'ai_access' => 'boolean',
        'custom_contracts' => 'boolean',
        'is_active' => 'boolean',
        'features_limits' => 'array',
    ];

    /** Feature keys used in sidebar and gates (must match PlanGateService::FEATURE_*). */
    public const FEATURE_REPORTS = 'reports';
    public const FEATURE_CONTRACTS = 'contracts';
    public const FEATURE_DAMAGES = 'damages';
    public const FEATURE_PAYMENTS = 'payments';
    public const FEATURE_ALERTS = 'alerts';
    public const FEATURE_BRANCHES = 'branches';
    public const FEATURE_RESERVATIONS = 'reservations';
    public const FEATURE_FLEET = 'fleet';
    public const FEATURE_CUSTOMERS = 'customers';
    public const FEATURE_PROFITABILITY = 'profitability';
    public const FEATURE_PARTNER_AVAILABILITY = 'partner_availability';

    /** Limit keys. */
    public const LIMIT_VEHICLES = 'vehicles';
    public const LIMIT_USERS = 'users';
    public const LIMIT_BRANCHES = 'branches';

    /**
     * Whether this plan has a feature enabled. Uses features_limits.features[key] or defaults to true for backward compat.
     */
    public function hasFeature(string $key): bool
    {
        $fl = $this->features_limits ?? [];
        $features = $fl['features'] ?? [];
        if (array_key_exists($key, $features)) {
            return (bool) $features[$key];
        }
        return true;
    }

    /**
     * Get limit value for a key. Uses features_limits.limits[key] or existing limit_* columns.
     */
    public function getLimit(string $key): ?int
    {
        $fl = $this->features_limits ?? [];
        $limits = $fl['limits'] ?? [];
        if (array_key_exists($key, $limits)) {
            $v = $limits[$key];
            return $v === null ? null : (int) $v;
        }
        return match ($key) {
            self::LIMIT_VEHICLES => $this->limit_vehicles,
            self::LIMIT_USERS => $this->limit_users,
            self::LIMIT_BRANCHES => $this->limit_branches,
            default => null,
        };
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
