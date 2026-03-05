<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyAlertDismissal;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AlertService
{
    public const TYPE_VEHICLE_COMPLIANCE = 'vehicle_compliance';
    public const TYPE_RESERVATION_START = 'reservation_start';
    public const TYPE_RESERVATION_RETURN = 'reservation_return';
    public const TYPE_RESERVATION_LATE = 'reservation_late';
    public const TYPE_PAYMENT_DUE = 'payment_due';
    public const TYPE_TRIAL_ENDING = 'trial_ending';
    public const TYPE_SUBSCRIPTION_EXPIRED = 'subscription_expired';

    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_URGENT = 'urgent';

    private const COMPLIANCE_DAYS = [30, 15, 7];

    /**
     * @return int
     */
    public function countUnread(?Company $company = null): int
    {
        if ($company) {
            return count($this->forCompany($company));
        }
        return count($this->platformAlerts());
    }

    /**
     * @return array<int, array{identifier: string, type: string, severity: string, title: string, body: string, due_at: ?Carbon, related_type: string, related_id: int|string, related_url: ?string, meta: array}>
     */
    public function forCompany(Company $company): array
    {
        $alerts = [];
        $dismissals = $company->alertDismissals()->get()->keyBy('identifier');

        foreach ($this->vehicleComplianceAlerts($company) as $a) {
            if ($this->isDismissed($a['identifier'], $dismissals)) {
                continue;
            }
            $alerts[] = $a;
        }
        foreach ($this->reservationAlerts($company) as $a) {
            if ($this->isDismissed($a['identifier'], $dismissals)) {
                continue;
            }
            $alerts[] = $a;
        }
        foreach ($this->paymentAlerts($company) as $a) {
            if ($this->isDismissed($a['identifier'], $dismissals)) {
                continue;
            }
            $alerts[] = $a;
        }

        return $alerts;
    }

    /**
     * Platform alerts for super_admin (trials ending, expired subscriptions).
     * @return array<int, array{identifier: string, type: string, severity: string, title: string, body: string, due_at: ?Carbon, related_type: string, related_id: int|string, related_url: ?string, meta: array}>
     */
    public function platformAlerts(): array
    {
        $alerts = [];
        $trialsEnding = Company::where('subscription_status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(14)])
            ->orderBy('trial_ends_at')
            ->get();
        foreach ($trialsEnding as $c) {
            $days = now()->diffInDays($c->trial_ends_at, false);
            $alerts[] = [
                'identifier' => 'company_' . $c->id . '_trial_ending',
                'type' => self::TYPE_TRIAL_ENDING,
                'severity' => $days <= 3 ? self::SEVERITY_URGENT : self::SEVERITY_WARNING,
                'title' => 'Essai se termine bientôt',
                'body' => $c->name . ' – fin d’essai le ' . $c->trial_ends_at->format('d/m/Y'),
                'due_at' => $c->trial_ends_at,
                'related_type' => 'company',
                'related_id' => $c->id,
                'related_url' => route('app.companies.show', $c),
                'meta' => [],
            ];
        }
        $expired = Company::where('subscription_status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->get();
        foreach ($expired as $c) {
            $alerts[] = [
                'identifier' => 'company_' . $c->id . '_trial_expired',
                'type' => self::TYPE_SUBSCRIPTION_EXPIRED,
                'severity' => self::SEVERITY_URGENT,
                'title' => 'Essai expiré',
                'body' => $c->name . ' – essai expiré le ' . $c->trial_ends_at->format('d/m/Y'),
                'due_at' => $c->trial_ends_at,
                'related_type' => 'company',
                'related_id' => $c->id,
                'related_url' => route('app.companies.show', $c),
                'meta' => [],
            ];
        }
        return $alerts;
    }

    private function isDismissed(string $identifier, Collection $dismissals): bool
    {
        $d = $dismissals->get($identifier);
        if (!$d) {
            return false;
        }
        if ($d->action === CompanyAlertDismissal::ACTION_DONE) {
            return true;
        }
        if ($d->action === CompanyAlertDismissal::ACTION_SNOOZE && $d->snooze_until && $d->snooze_until->isFuture()) {
            return true;
        }
        return false;
    }

    private function vehicleComplianceAlerts(Company $company): array
    {
        $alerts = [];
        $vehicles = $company->vehicles()->with('branch')->get();
        $today = now()->startOfDay();

        foreach ($vehicles as $v) {
            if ($v->insurance_end_date) {
                $daysLeft = $today->diffInDays(Carbon::parse($v->insurance_end_date)->startOfDay(), false);
                $severity = $this->severityForDays($daysLeft);
                if ($severity) {
                    $alerts[] = [
                        'identifier' => 'vehicle_' . $v->id . '_insurance',
                        'type' => self::TYPE_VEHICLE_COMPLIANCE,
                        'severity' => $severity,
                        'title' => 'Assurance – ' . $v->plate,
                        'body' => ($daysLeft < 0 ? 'Expirée depuis ' . abs($daysLeft) . ' j' : 'Expire dans ' . $daysLeft . ' j') . ' – ' . $v->insurance_end_date->format('d/m/Y'),
                        'due_at' => $v->insurance_end_date,
                        'related_type' => 'vehicle',
                        'related_id' => $v->id,
                        'related_url' => route('app.companies.vehicles.show', [$company, $v]),
                        'meta' => ['vehicle_id' => $v->id, 'compliance_type' => 'insurance'],
                    ];
                }
            }
            if ($v->visite_expiry_date) {
                $daysLeft = $today->diffInDays(Carbon::parse($v->visite_expiry_date)->startOfDay(), false);
                $severity = $this->severityForDays($daysLeft);
                if ($severity) {
                    $alerts[] = [
                        'identifier' => 'vehicle_' . $v->id . '_visite',
                        'type' => self::TYPE_VEHICLE_COMPLIANCE,
                        'severity' => $severity,
                        'title' => 'Visite technique – ' . $v->plate,
                        'body' => ($daysLeft < 0 ? 'Expirée depuis ' . abs($daysLeft) . ' j' : 'Expire dans ' . $daysLeft . ' j') . ' – ' . $v->visite_expiry_date->format('d/m/Y'),
                        'due_at' => $v->visite_expiry_date,
                        'related_type' => 'vehicle',
                        'related_id' => $v->id,
                        'related_url' => route('app.companies.vehicles.show', [$company, $v]),
                        'meta' => ['vehicle_id' => $v->id, 'compliance_type' => 'visite'],
                    ];
                }
            }
            if ($v->vignette_year) {
                $endOfYear = Carbon::createFromFormat('Y', (string) $v->vignette_year)->endOfYear();
                $daysLeft = $today->diffInDays($endOfYear, false);
                $severity = $this->severityForDays($daysLeft);
                if ($severity) {
                    $alerts[] = [
                        'identifier' => 'vehicle_' . $v->id . '_vignette',
                        'type' => self::TYPE_VEHICLE_COMPLIANCE,
                        'severity' => $severity,
                        'title' => 'Vignette – ' . $v->plate,
                        'body' => ($daysLeft < 0 ? 'Expirée' : 'Expire fin ' . $v->vignette_year) . ' – ' . $endOfYear->format('d/m/Y'),
                        'due_at' => $endOfYear,
                        'related_type' => 'vehicle',
                        'related_id' => $v->id,
                        'related_url' => route('app.companies.vehicles.show', [$company, $v]),
                        'meta' => ['vehicle_id' => $v->id, 'compliance_type' => 'vignette'],
                    ];
                }
            }
        }
        return $alerts;
    }

    private function severityForDays(int $daysLeft): ?string
    {
        if ($daysLeft < 0) {
            return self::SEVERITY_URGENT;
        }
        if ($daysLeft <= 7) {
            return self::SEVERITY_URGENT;
        }
        if ($daysLeft <= 15) {
            return self::SEVERITY_WARNING;
        }
        if ($daysLeft <= 30) {
            return self::SEVERITY_INFO;
        }
        return null;
    }

    private function reservationAlerts(Company $company): array
    {
        $alerts = [];
        $tomorrow = now()->addDay()->startOfDay();
        $todayEnd = now()->endOfDay();
        $todayStart = now()->startOfDay();

        $reservations = $company->reservations()
            ->with(['vehicle', 'customer'])
            ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
            ->get();

        foreach ($reservations as $r) {
            $start = $r->start_at->startOfDay();
            $end = $r->end_at->startOfDay();

            if ($start->isSameDay($tomorrow)) {
                $alerts[] = [
                    'identifier' => 'reservation_' . $r->id . '_starts_tomorrow',
                    'type' => self::TYPE_RESERVATION_START,
                    'severity' => self::SEVERITY_INFO,
                    'title' => 'Départ demain – ' . $r->reference,
                    'body' => $r->customer->name . ' · ' . $r->vehicle->plate . ' – ' . $r->start_at->format('d/m/Y H:i'),
                    'due_at' => $r->start_at,
                    'related_type' => 'reservation',
                    'related_id' => $r->id,
                    'related_url' => route('app.companies.reservations.show', [$company, $r]),
                    'meta' => ['phone' => $r->customer->phone],
                ];
            }
            if ($end->isSameDay($todayStart)) {
                $alerts[] = [
                    'identifier' => 'reservation_' . $r->id . '_return_today',
                    'type' => self::TYPE_RESERVATION_RETURN,
                    'severity' => self::SEVERITY_WARNING,
                    'title' => 'Retour aujourd’hui – ' . $r->reference,
                    'body' => $r->customer->name . ' · ' . $r->vehicle->plate,
                    'due_at' => $r->end_at,
                    'related_type' => 'reservation',
                    'related_id' => $r->id,
                    'related_url' => route('app.companies.reservations.show', [$company, $r]),
                    'meta' => ['phone' => $r->customer->phone],
                ];
            }
            if ($r->status === Reservation::STATUS_IN_PROGRESS && $r->end_at->isPast()) {
                $alerts[] = [
                    'identifier' => 'reservation_' . $r->id . '_late',
                    'type' => self::TYPE_RESERVATION_LATE,
                    'severity' => self::SEVERITY_URGENT,
                    'title' => 'Retour en retard – ' . $r->reference,
                    'body' => $r->customer->name . ' · ' . $r->vehicle->plate . ' – prévu ' . $r->end_at->format('d/m/Y H:i'),
                    'due_at' => $r->end_at,
                    'related_type' => 'reservation',
                    'related_id' => $r->id,
                    'related_url' => route('app.companies.reservations.show', [$company, $r]),
                    'meta' => ['phone' => $r->customer->phone],
                ];
            }
        }
        return $alerts;
    }

    private function paymentAlerts(Company $company): array
    {
        $alerts = [];
        $reservations = $company->reservations()
            ->with(['vehicle', 'customer'])
            ->whereNotIn('status', [Reservation::STATUS_CANCELLED])
            ->get();

        foreach ($reservations as $r) {
            $remaining = $r->remaining_amount;
            if ($remaining > 0) {
                $alerts[] = [
                    'identifier' => 'reservation_' . $r->id . '_payment',
                    'type' => self::TYPE_PAYMENT_DUE,
                    'severity' => $remaining >= (float) $r->total_price ? self::SEVERITY_WARNING : self::SEVERITY_INFO,
                    'title' => 'Solde à régler – ' . $r->reference,
                    'body' => $r->customer->name . ' · ' . number_format($remaining, 2, ',', ' ') . ' MAD restants',
                    'due_at' => null,
                    'related_type' => 'reservation',
                    'related_id' => $r->id,
                    'related_url' => route('app.companies.reservations.show', [$company, $r]) . '?tab=payments',
                    'meta' => [],
                ];
            }
        }
        return $alerts;
    }

    /** Alerts for a single vehicle (compliance only). */
    public function forVehicle(Company $company, Vehicle $vehicle): array
    {
        $alerts = [];
        $today = now()->startOfDay();

        if ($vehicle->insurance_end_date) {
            $daysLeft = $today->diffInDays(Carbon::parse($vehicle->insurance_end_date)->startOfDay(), false);
            $s = $this->severityForDays($daysLeft);
            if ($s) {
                $alerts[] = ['type' => 'Assurance', 'severity' => $s, 'body' => ($daysLeft < 0 ? 'Expirée' : 'Expire dans ' . $daysLeft . ' j') . ' – ' . $vehicle->insurance_end_date->format('d/m/Y')];
            }
        }
        if ($vehicle->visite_expiry_date) {
            $daysLeft = $today->diffInDays(Carbon::parse($vehicle->visite_expiry_date)->startOfDay(), false);
            $s = $this->severityForDays($daysLeft);
            if ($s) {
                $alerts[] = ['type' => 'Visite technique', 'severity' => $s, 'body' => ($daysLeft < 0 ? 'Expirée' : 'Expire dans ' . $daysLeft . ' j') . ' – ' . $vehicle->visite_expiry_date->format('d/m/Y')];
            }
        }
        if ($vehicle->vignette_year) {
            $endOfYear = Carbon::createFromFormat('Y', (string) $vehicle->vignette_year)->endOfYear();
            $daysLeft = $today->diffInDays($endOfYear, false);
            $s = $this->severityForDays($daysLeft);
            if ($s) {
                $alerts[] = ['type' => 'Vignette', 'severity' => $s, 'body' => ($daysLeft < 0 ? 'Expirée' : 'Expire fin ' . $vehicle->vignette_year)];
            }
        }
        return $alerts;
    }
}
