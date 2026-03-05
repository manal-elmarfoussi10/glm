<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;

class PlanGateService
{
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

    public const LIMIT_VEHICLES = 'vehicles';
    public const LIMIT_USERS = 'users';
    public const LIMIT_BRANCHES = 'branches';

    /** All feature keys for menu gating. */
    public const FEATURES = [
        self::FEATURE_RESERVATIONS,
        self::FEATURE_CONTRACTS,
        self::FEATURE_DAMAGES,
        self::FEATURE_PAYMENTS,
        self::FEATURE_ALERTS,
        self::FEATURE_BRANCHES,
        self::FEATURE_REPORTS,
        self::FEATURE_FLEET,
        self::FEATURE_CUSTOMERS,
        self::FEATURE_PROFITABILITY,
        self::FEATURE_PARTNER_AVAILABILITY,
    ];

    public const FEATURE_LABELS = [
        self::FEATURE_RESERVATIONS => 'Réservations',
        self::FEATURE_CONTRACTS => 'Contrats',
        self::FEATURE_DAMAGES => 'Dégâts',
        self::FEATURE_PAYMENTS => 'Paiements',
        self::FEATURE_ALERTS => 'Alertes',
        self::FEATURE_BRANCHES => 'Agences',
        self::FEATURE_REPORTS => 'Rapports',
        self::FEATURE_FLEET => 'Flotte',
        self::FEATURE_CUSTOMERS => 'Clients',
        self::FEATURE_PROFITABILITY => 'Rentabilité flotte',
        self::FEATURE_PARTNER_AVAILABILITY => 'Disponibilité partenaires',
    ];

    public function getPlanForCompany(?Company $company): ?Plan
    {
        if (! $company) {
            return null;
        }
        $company->loadMissing('planRelation');
        if ($company->plan_id && $company->planRelation) {
            return $company->planRelation;
        }
        $sub = $company->subscription;
        if ($sub?->plan_id) {
            $sub->loadMissing('plan');
            return $sub->plan;
        }
        return null;
    }

    public function can(?Company $company, string $feature): bool
    {
        if (! $company) {
            return true;
        }
        $plan = $this->getPlanForCompany($company);
        if (! $plan) {
            return true;
        }
        return $plan->hasFeature($feature);
    }

    public function getLimit(?Company $company, string $limitKey): ?int
    {
        if (! $company) {
            return null;
        }
        $plan = $this->getPlanForCompany($company);
        if (! $plan) {
            return null;
        }
        return $plan->getLimit($limitKey);
    }

    public function getCurrentCount(?Company $company, string $limitKey): int
    {
        if (! $company) {
            return 0;
        }
        return match ($limitKey) {
            self::LIMIT_VEHICLES => $company->vehicles()->count(),
            self::LIMIT_USERS => $company->users()->count(),
            self::LIMIT_BRANCHES => $company->branches()->count(),
            default => 0,
        };
    }

    public function isLimitReached(?Company $company, string $limitKey): bool
    {
        if (! $company) {
            return false;
        }
        $limit = $this->getLimit($company, $limitKey);
        if ($limit === null || $limit <= 0) {
            return false;
        }
        return $this->getCurrentCount($company, $limitKey) >= $limit;
    }

    public function getFeatureLabel(string $feature): string
    {
        return self::FEATURE_LABELS[$feature] ?? $feature;
    }

    public function getLimitLabel(string $limitKey): string
    {
        return match ($limitKey) {
            self::LIMIT_VEHICLES => 'Véhicules',
            self::LIMIT_USERS => 'Utilisateurs',
            self::LIMIT_BRANCHES => 'Agences',
            default => $limitKey,
        };
    }
}
