<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\ReservationStartingTodayNotification;
use App\Notifications\VehicleDocumentExpiringNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendScheduledNotificationsCommand extends Command
{
    protected $signature = 'notifications:send-scheduled';

    protected $description = 'Send document expiring and reservation starting today notifications.';

    public function handle(): int
    {
        $this->sendDocumentExpiringNotifications();
        $this->sendReservationStartingTodayNotifications();

        return self::SUCCESS;
    }

    private function sendDocumentExpiringNotifications(): void
    {
        $companies = Company::where('status', 'active')->get();
        $today = now()->startOfDay();
        $threshold = now()->addDays(30)->format('Y-m-d');

        foreach ($companies as $company) {
            $vehicles = $company->vehicles()->with('branch')->get();
            foreach ($vehicles as $vehicle) {
                $vehicleUrl = route('app.companies.vehicles.show', [$company, $vehicle]);
                $vehicleName = $vehicle->brand . ' ' . $vehicle->model . ($vehicle->year ? ' (' . $vehicle->year . ')' : '');

                if ($vehicle->insurance_end_date && $vehicle->insurance_end_date <= $threshold) {
                    $daysLeft = (int) $today->diffInDays(Carbon::parse($vehicle->insurance_end_date)->startOfDay(), false);
                    if ($daysLeft <= 30) {
                        $company->users()->where('role', 'company_admin')->get()->each(function (User $u) use ($vehicle, $vehicleName, $vehicleUrl) {
                            $u->notify(new VehicleDocumentExpiringNotification(
                                'Assurance',
                                $vehicle->plate,
                                $vehicleName,
                                $vehicle->insurance_end_date->format('d/m/Y'),
                                max(0, (int) now()->startOfDay()->diffInDays(Carbon::parse($vehicle->insurance_end_date)->startOfDay(), false)),
                                $vehicleUrl
                            ));
                        });
                    }
                }
                if ($vehicle->visite_expiry_date && $vehicle->visite_expiry_date->format('Y-m-d') <= $threshold) {
                    $daysLeft = (int) $today->diffInDays($vehicle->visite_expiry_date->startOfDay(), false);
                    if ($daysLeft <= 30) {
                        $company->users()->where('role', 'company_admin')->get()->each(function (User $u) use ($vehicle, $vehicleName, $vehicleUrl) {
                            $u->notify(new VehicleDocumentExpiringNotification(
                                'Visite technique',
                                $vehicle->plate,
                                $vehicleName,
                                $vehicle->visite_expiry_date->format('d/m/Y'),
                                max(0, (int) now()->startOfDay()->diffInDays($vehicle->visite_expiry_date->startOfDay(), false)),
                                $vehicleUrl
                            ));
                        });
                    }
                }
                if ($vehicle->vignette_year) {
                    $endOfYear = Carbon::createFromFormat('Y', (string) $vehicle->vignette_year)->endOfYear();
                    if ($endOfYear->format('Y-m-d') <= $threshold) {
                        $daysLeft = (int) $today->diffInDays($endOfYear, false);
                        if ($daysLeft <= 30) {
                            $company->users()->where('role', 'company_admin')->get()->each(function (User $u) use ($vehicle, $vehicleName, $vehicleUrl, $endOfYear) {
                                $u->notify(new VehicleDocumentExpiringNotification(
                                    'Vignette',
                                    $vehicle->plate,
                                    $vehicleName,
                                    $endOfYear->format('d/m/Y'),
                                    max(0, (int) now()->startOfDay()->diffInDays($endOfYear, false)),
                                    $vehicleUrl
                                ));
                            });
                        }
                    }
                }
            }
        }
    }

    private function sendReservationStartingTodayNotifications(): void
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $reservations = Reservation::with(['company', 'customer', 'vehicle'])
            ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
            ->whereBetween('start_at', [$todayStart, $todayEnd])
            ->get();

        foreach ($reservations as $reservation) {
            $company = $reservation->company;
            if (! $company) {
                continue;
            }
            $reservationUrl = route('app.companies.reservations.show', [$company, $reservation]);
            $company->users()->where('role', 'company_admin')->get()->each(function (User $u) use ($reservation, $reservationUrl) {
                $u->notify(new ReservationStartingTodayNotification($reservation, $reservationUrl));
            });
        }
    }
}
