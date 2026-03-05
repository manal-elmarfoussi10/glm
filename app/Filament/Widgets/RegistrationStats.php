<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RegistrationStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Demandes', User::where('role', 'company_admin')->count())
                ->description('Volume global de demandes')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info')
                ->icon('heroicon-o-user-group'),
            Stat::make('En Attente', User::where('role', 'company_admin')->where('status', 'pending')->count())
                ->description('Requires validation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-list'),
            Stat::make('Approuvées', User::where('role', 'company_admin')->where('status', 'active')->count())
                ->description('Accounts activated')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->icon('heroicon-o-shield-check'),
            Stat::make('Refusées', User::where('role', 'company_admin')->where('status', 'rejected')->count())
                ->description('Non retained')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->icon('heroicon-o-no-symbol'),
        ];
    }
}
