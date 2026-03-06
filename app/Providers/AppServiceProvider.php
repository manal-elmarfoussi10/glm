<?php

namespace App\Providers;

use App\Contracts\DocumentExtractorInterface;
use App\Http\Responses\AppLoginResponse;
use App\Http\Responses\Auth\PendingApprovalRegistrationResponse;
use App\Services\DocumentExtractionService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            RegistrationResponse::class,
            PendingApprovalRegistrationResponse::class
        );

        $this->app->bind(LoginResponseContract::class, AppLoginResponse::class);
        $this->app->bind(DocumentExtractorInterface::class, DocumentExtractionService::class);

        // When the app is deployed with everything in document root (no "public" subfolder),
        // e.g. using index.document_root.php, make public_path() point to base_path() so
        // assets (build/, Filament CSS/JS) load correctly.
        if (! is_dir(base_path('public')) && file_exists(base_path('build/manifest.json'))) {
            $this->app->instance('path.public', base_path());
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Reservation::observe(\App\Observers\ReservationObserver::class);
        \App\Models\ReservationPayment::observe(\App\Observers\PaymentObserver::class);
        \App\Models\Vehicle::observe(\App\Observers\VehicleObserver::class);
        \App\Models\Expense::observe(\App\Observers\ExpenseObserver::class);
    }
}
