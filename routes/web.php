<?php

use App\Http\Controllers\App\Admin\ContractTemplateController;
use App\Http\Controllers\App\Admin\PlanController;
use App\Http\Controllers\App\Admin\PlatformUserController;
use App\Http\Controllers\App\AppSearchController;
use App\Http\Controllers\App\UserProfileController;
use App\Http\Controllers\App\CompanyAlertController;
use App\Http\Controllers\App\CompanyBranchController;
use App\Http\Controllers\App\CompanyContractController;
use App\Http\Controllers\App\CompanyContractTemplateController;
use App\Http\Controllers\App\CompanyDamageController;
use App\Http\Controllers\App\CompanyExpenseController;
use App\Http\Controllers\App\CompanyPaymentController;
use App\Http\Controllers\App\CompanyController;
use App\Http\Controllers\App\CompanyActivityController;
use App\Http\Controllers\App\CompanyCustomerController;
use App\Http\Controllers\App\CompanySubscriptionController;
use App\Http\Controllers\App\CompanyUpgradeController;
use App\Http\Controllers\App\CompanyTrustController;
use App\Http\Controllers\App\CompanyUserController;
use App\Http\Controllers\App\CompanyPartnerSettingController;
use App\Http\Controllers\App\CompanyReportController;
use App\Http\Controllers\App\CompanyReservationController;
use App\Http\Controllers\App\FleetProfitabilityController;
use App\Http\Controllers\App\PartnerRequestController;
use App\Http\Controllers\App\PartnerSearchController;
use App\Http\Controllers\App\CompanyVehicleController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\RegistrationRequestController;
use App\Http\Controllers\App\Support\AuditLogController;
use App\Http\Controllers\App\Support\SupportSearchController;
use App\Http\Controllers\App\Support\SupportSubscriptionController;
use App\Http\Controllers\App\Support\TicketController;
use App\Http\Controllers\App\Support\UpgradeRequestController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('app.dashboard') : redirect('/admin/login');
});

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'show'])->name('register.show');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store'])->name('register.store');

Route::get('/admin/register', function () {
    return redirect()->route('register.show');
})->name('filament.admin.auth.register');

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/pending-approval', function () {
    return view('filament.pages.auth.pending-approval');
})->name('auth.pending-approval');

Route::get('/_test-mail', function () {
    $to = config('mail.test_to') ?: config('mail.from.address');
    if (! $to) {
        return response('Error: Set MAIL_TEST_TO or MAIL_FROM_ADDRESS in .env', 400);
    }
    try {
        Mail::raw('GLM SMTP test OK ✅', function ($m) use ($to) {
            $m->to($to)->subject('GLM SMTP Test');
        });
        return response('OK: mail sent to ' . $to . '. Check inbox/spam.', 200);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Test mail failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        $message = app()->environment('production')
            ? 'Error: mail could not be sent.'
            : 'Error: ' . $e->getMessage();
        return response($message, 500);
    }
})->withoutMiddleware(['auth']);

/*
|--------------------------------------------------------------------------
| App area (custom admin dashboard – replaces Filament UI for main app)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'web'])->prefix('app')->name('app.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', fn () => redirect()->route('app.dashboard'));

    // First-login onboarding wizard (company_admin / agent)
    Route::get('/onboarding', [\App\Http\Controllers\App\OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding/step/1', [\App\Http\Controllers\App\OnboardingController::class, 'storeStep1'])->name('onboarding.store.step1');
    Route::post('/onboarding/step/2', [\App\Http\Controllers\App\OnboardingController::class, 'storeStep2'])->name('onboarding.store.step2');
    Route::post('/onboarding/step/3', [\App\Http\Controllers\App\OnboardingController::class, 'storeStep3'])->name('onboarding.store.step3');
    Route::post('/onboarding/step/4', [\App\Http\Controllers\App\OnboardingController::class, 'storeStep4'])->name('onboarding.store.step4');

    Route::get('/test-mail', function () {
        $to = config('mail.test_to') ?: config('mail.from.address');
        if (! $to) {
            return response('Error: Set MAIL_TEST_TO or MAIL_FROM_ADDRESS in .env', 400);
        }
        try {
            Mail::raw('GLM SMTP test OK ✅ (from authenticated user)', function ($m) use ($to) {
                $m->to($to)->subject('GLM SMTP Test');
            });
            return response('OK: mail sent to ' . $to . '. Check inbox/spam.', 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Test mail failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $message = app()->environment('production')
                ? 'Error: mail could not be sent.'
                : 'Error: ' . $e->getMessage();
            return response($message, 500);
        }
    })->name('test-mail');

    // Platform only: demandes d'inscription
    Route::middleware('platform_staff')->group(function () {
        Route::get('/registration-requests', [RegistrationRequestController::class, 'index'])->name('registration-requests.index');
        Route::get('/registration-requests/{user}', [RegistrationRequestController::class, 'show'])->name('registration-requests.show');
        Route::post('/registration-requests/{user}/approve', [RegistrationRequestController::class, 'approve'])->name('registration-requests.approve');
        Route::post('/registration-requests/{user}/reject', [RegistrationRequestController::class, 'reject'])->name('registration-requests.reject');
        Route::post('/registration-requests/{user}/ask-info', [RegistrationRequestController::class, 'askInfo'])->name('registration-requests.ask-info');
    });

    // Companies list: platform staff see list; company_admin/agent redirect to their company
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::middleware('super_admin')->group(function () {
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    });
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show')->middleware('company_access');
    Route::middleware(['company_access', 'company_admin_only'])->group(function () {
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::get('/companies/{company}/users', [CompanyUserController::class, 'index'])->name('companies.users.index');
        Route::get('/companies/{company}/users/create', [CompanyUserController::class, 'create'])->name('companies.users.create');
        Route::post('/companies/{company}/users', [CompanyUserController::class, 'store'])->name('companies.users.store');
    });

// Company operational (fleet, reservations, alerts, etc.): support + company_admin/agent for own company only. super_admin denied.
    Route::middleware('company_operational')->group(function () {
        Route::get('/companies/{company}/upgrade', [CompanyUpgradeController::class, 'show'])->name('companies.upgrade');
        Route::post('/companies/{company}/upgrade-request', [CompanyUpgradeController::class, 'store'])->name('companies.upgrade-request.store');
        Route::get('/companies/{company}/branches', [CompanyBranchController::class, 'index'])->name('companies.branches.index');
        Route::get('/companies/{company}/branches/create', [CompanyBranchController::class, 'create'])->name('companies.branches.create');
        Route::post('/companies/{company}/branches', [CompanyBranchController::class, 'store'])->name('companies.branches.store');
        Route::get('/companies/{company}/branches/{branch}', [CompanyBranchController::class, 'show'])->name('companies.branches.show');
        Route::get('/companies/{company}/branches/{branch}/edit', [CompanyBranchController::class, 'edit'])->name('companies.branches.edit');
        Route::put('/companies/{company}/branches/{branch}', [CompanyBranchController::class, 'update'])->name('companies.branches.update');

        Route::get('/companies/{company}/trust', [CompanyTrustController::class, 'index'])->name('companies.trust.index');
        Route::get('/companies/{company}/trust/flags', [CompanyTrustController::class, 'flags'])->name('companies.trust.flags');
        Route::post('/companies/{company}/trust/flag', [CompanyTrustController::class, 'flag'])->name('companies.trust.flag');
        Route::post('/companies/{company}/trust/unflag', [CompanyTrustController::class, 'unflag'])->name('companies.trust.unflag');
        Route::post('/companies/{company}/trust/record-success', [CompanyTrustController::class, 'recordSuccess'])->name('companies.trust.record-success');
        Route::post('/companies/{company}/trust/set-verified', [CompanyTrustController::class, 'setVerified'])->name('companies.trust.set-verified');

        // Fleet (Vehicles)
        Route::get('/companies/{company}/vehicles', [CompanyVehicleController::class, 'index'])->name('companies.vehicles.index');
        Route::get('/companies/{company}/fleet/profitability', [FleetProfitabilityController::class, 'index'])->name('companies.fleet.profitability.index');
        Route::get('/companies/{company}/fleet/profitability/vehicle/{vehicle}', [FleetProfitabilityController::class, 'show'])->name('companies.fleet.profitability.show');

        // Expenses (index: company_operational for read-only agents; write routes below)
        Route::get('/companies/{company}/expenses', [CompanyExpenseController::class, 'index'])->name('companies.expenses.index');

        // Partner availability (Pro+): search + requests
        Route::get('/companies/{company}/partners/search', [PartnerSearchController::class, 'index'])->name('companies.partners.search');
        Route::get('/companies/{company}/partner-requests', [PartnerRequestController::class, 'index'])->name('companies.partner-requests.index');
        Route::post('/companies/{company}/partner-requests', [PartnerRequestController::class, 'store'])->name('companies.partner-requests.store');
    Route::get('/companies/{company}/vehicles/create', [CompanyVehicleController::class, 'create'])->name('companies.vehicles.create');
        Route::post('/companies/{company}/vehicles', [CompanyVehicleController::class, 'store'])->name('companies.vehicles.store');
        Route::get('/companies/{company}/vehicles/{vehicle}', [CompanyVehicleController::class, 'show'])->name('companies.vehicles.show');
        Route::get('/companies/{company}/vehicles/{vehicle}/edit', [CompanyVehicleController::class, 'edit'])->name('companies.vehicles.edit');
        Route::put('/companies/{company}/vehicles/{vehicle}', [CompanyVehicleController::class, 'update'])->name('companies.vehicles.update');
        Route::delete('/companies/{company}/vehicles/{vehicle}', [CompanyVehicleController::class, 'destroy'])->name('companies.vehicles.destroy');
        Route::get('/companies/{company}/vehicles/{vehicle}/duplicate', [CompanyVehicleController::class, 'duplicate'])->name('companies.vehicles.duplicate');
        Route::post('/companies/{company}/vehicles/{vehicle}/duplicate', [CompanyVehicleController::class, 'storeDuplicate'])->name('companies.vehicles.store-duplicate');

        // Customers (Clients)
        Route::get('/companies/{company}/customers', [CompanyCustomerController::class, 'index'])->name('companies.customers.index');
        Route::get('/companies/{company}/customers/lookup-by-cin', [CompanyCustomerController::class, 'lookupByCin'])->name('companies.customers.lookup-by-cin');
    Route::get('/companies/{company}/customers/create', [CompanyCustomerController::class, 'create'])->name('companies.customers.create');
        Route::post('/companies/{company}/customers', [CompanyCustomerController::class, 'store'])->name('companies.customers.store');
        Route::get('/companies/{company}/customers/{customer}', [CompanyCustomerController::class, 'show'])->name('companies.customers.show');
        Route::get('/companies/{company}/customers/{customer}/edit', [CompanyCustomerController::class, 'edit'])->name('companies.customers.edit');
        Route::put('/companies/{company}/customers/{customer}', [CompanyCustomerController::class, 'update'])->name('companies.customers.update');
        Route::delete('/companies/{company}/customers/{customer}', [CompanyCustomerController::class, 'destroy'])->name('companies.customers.destroy');

        // Reservations
        Route::get('/companies/{company}/reservations/vehicle-availability/{vehicle}', [CompanyReservationController::class, 'vehicleAvailability'])->name('companies.reservations.vehicle-availability');
    Route::get('/companies/{company}/reservations', [CompanyReservationController::class, 'index'])->name('companies.reservations.index');
        Route::get('/companies/{company}/reservations/create', [CompanyReservationController::class, 'create'])->name('companies.reservations.create');
        Route::post('/companies/{company}/reservations', [CompanyReservationController::class, 'store'])->name('companies.reservations.store');
        Route::get('/companies/{company}/reservations/{reservation}', [CompanyReservationController::class, 'show'])->name('companies.reservations.show');
        Route::post('/companies/{company}/reservations/{reservation}/confirm', [CompanyReservationController::class, 'confirm'])->name('companies.reservations.confirm');
        Route::post('/companies/{company}/reservations/{reservation}/cancel', [CompanyReservationController::class, 'cancel'])->name('companies.reservations.cancel');
        Route::post('/companies/{company}/reservations/{reservation}/mark-paid', [CompanyReservationController::class, 'markPaid'])->name('companies.reservations.mark-paid');
        Route::post('/companies/{company}/reservations/{reservation}/payments', [CompanyReservationController::class, 'storePayment'])->name('companies.reservations.payments.store');
        Route::post('/companies/{company}/reservations/{reservation}/refund-deposit', [CompanyReservationController::class, 'refundDeposit'])->name('companies.reservations.refund-deposit');
        Route::get('/companies/{company}/reservations/{reservation}/receipt', [CompanyReservationController::class, 'receipt'])->name('companies.reservations.receipt');
        Route::post('/companies/{company}/reservations/{reservation}/start', [CompanyReservationController::class, 'startRental'])->name('companies.reservations.start');
        Route::post('/companies/{company}/reservations/{reservation}/complete', [CompanyReservationController::class, 'completeRental'])->name('companies.reservations.complete');
        Route::get('/companies/{company}/reservations/{reservation}/contract-preview', [CompanyReservationController::class, 'contractPreview'])->name('companies.reservations.contract-preview');
        Route::post('/companies/{company}/reservations/{reservation}/contract-generate', [CompanyReservationController::class, 'contractGenerate'])->name('companies.reservations.contract-generate');
        Route::get('/companies/{company}/reservations/{reservation}/contract-print', [CompanyReservationController::class, 'contractPrint'])->name('companies.reservations.contract-print');
        Route::post('/companies/{company}/reservations/{reservation}/contract-signed', [CompanyReservationController::class, 'storeSignedContract'])->name('companies.reservations.contract-signed.store');
        Route::get('/companies/{company}/reservations/{reservation}/contract-signed/download', [CompanyReservationController::class, 'downloadSignedContract'])->name('companies.reservations.contract-signed.download');
        Route::post('/companies/{company}/reservations/{reservation}/inspections', [CompanyReservationController::class, 'storeInspection'])->name('companies.reservations.inspections.store');
        Route::delete('/companies/{company}/reservations/{reservation}/inspection-photos/{photo}', [CompanyReservationController::class, 'deleteInspectionPhoto'])->name('companies.reservations.inspection-photos.destroy');

        // Contracts list
        Route::get('/companies/{company}/contracts', [CompanyContractController::class, 'index'])->name('companies.contracts.index');

        // Damages list
        Route::get('/companies/{company}/damages', [CompanyDamageController::class, 'index'])->name('companies.damages.index');

        // Payments list
        Route::get('/companies/{company}/payments', [CompanyPaymentController::class, 'index'])->name('companies.payments.index');

    // Alerts
        Route::get('/companies/{company}/alerts', [CompanyAlertController::class, 'index'])->name('companies.alerts.index');
        Route::post('/companies/{company}/alerts/mark-done', [CompanyAlertController::class, 'markDone'])->name('companies.alerts.mark-done');
        Route::post('/companies/{company}/alerts/snooze', [CompanyAlertController::class, 'snooze'])->name('companies.alerts.snooze');

        // Activity Log (Journal d'activité)
        Route::get('/companies/{company}/activity', [CompanyActivityController::class, 'index'])->name('companies.activity.index');
    });

    // Global Search
    Route::get('/search', [AppSearchController::class, 'index'])->name('search');
    Route::get('/search/ajax', [AppSearchController::class, 'ajax'])->name('search.ajax');

    // Profile & Settings
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/settings', [UserProfileController::class, 'settings'])->name('profile.settings');

    // Notifications (in-app)
    Route::get('/notifications', [\App\Http\Controllers\App\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\App\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\App\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Reports & Analytics: company_admin only (agents have limited operational access, no reports)
    Route::middleware(['company_operational', 'company_admin_only'])->group(function () {
        Route::get('/companies/{company}/reports', [CompanyReportController::class, 'index'])->name('companies.reports.index');
        Route::get('/companies/{company}/reports/export-csv', [CompanyReportController::class, 'exportCsv'])->name('companies.reports.export-csv');
    });

    // Expenses create/edit/delete: company_admin only (agents read-only on index)
    Route::middleware(['company_operational', 'company_admin_only'])->group(function () {
        Route::get('/companies/{company}/expenses/create', [CompanyExpenseController::class, 'create'])->name('companies.expenses.create');
        Route::post('/companies/{company}/expenses', [CompanyExpenseController::class, 'store'])->name('companies.expenses.store');
        Route::get('/companies/{company}/expenses/{expense}/edit', [CompanyExpenseController::class, 'edit'])->name('companies.expenses.edit');
        Route::put('/companies/{company}/expenses/{expense}', [CompanyExpenseController::class, 'update'])->name('companies.expenses.update');
        Route::delete('/companies/{company}/expenses/{expense}', [CompanyExpenseController::class, 'destroy'])->name('companies.expenses.destroy');
    });

    // Partner availability: settings + accept/reject (company_admin only)
    Route::middleware(['company_operational', 'company_admin_only'])->group(function () {
        Route::get('/companies/{company}/partner-settings', [CompanyPartnerSettingController::class, 'edit'])->name('companies.partner-settings.edit');
        Route::put('/companies/{company}/partner-settings', [CompanyPartnerSettingController::class, 'update'])->name('companies.partner-settings.update');
        Route::patch('/companies/{company}/partner-requests/{partnerRequest}/accept', [PartnerRequestController::class, 'accept'])->name('companies.partner-requests.accept');
        Route::patch('/companies/{company}/partner-requests/{partnerRequest}/reject', [PartnerRequestController::class, 'reject'])->name('companies.partner-requests.reject');
    });

    // Short URL: /app/contracts redirects to current user's company contracts
    Route::get('/contracts', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.contracts.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise pour voir les contrats.');
    })->name('contracts.redirect');

    // Short URL: /app/damages redirects to current user's company damages
    Route::get('/damages', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.damages.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise pour voir les dégâts.');
    })->name('damages.redirect');

    // Short URL: /app/alerts redirects to current user's company alerts
    Route::get('/alerts', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.alerts.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise pour voir les alertes.');
    })->name('alerts.redirect');

    // Short URL: /app/reports redirects to current user's company reports
    Route::get('/reports', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.reports.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise pour voir les rapports.');
    })->name('reports.redirect');

    // Short URL: /app/payments redirects to current user's company payments
    Route::get('/payments', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.payments.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise pour voir les paiements.');
    })->name('payments.redirect');

    // Short URL: /app/reservations redirects to current user's company reservations
    Route::get('/reservations', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.reservations.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise pour gérer les réservations.');
    })->name('reservations.redirect');

    // Short URL: /app/customers redirects to current user's company customers (when they have company_id)
    Route::get('/customers', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.customers.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise pour gérer les clients.');
    })->name('customers.redirect');

    // Short URL: /app/branches redirects to current user's company branches
    Route::get('/branches', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.branches.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise pour gérer les agences.');
    })->name('branches.redirect');

    // Short URL: /app/fleet/profitability
    Route::get('/fleet/profitability', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.fleet.profitability.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise.');
    })->name('fleet.profitability.redirect');

    // Short URL: /app/expenses
    Route::get('/expenses', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.expenses.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise.');
    })->name('expenses.redirect');

    // Short URL: /app/activity
    Route::get('/activity', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.activity.index', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise.');
    })->name('activity.redirect');

    // Short URL: /app/partners (partner availability search)
    Route::get('/partners', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            return redirect()->route('app.companies.partners.search', \App\Models\Company::find($companyId));
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise.');
    })->name('partners.redirect');

    // Short URL: /app/upgrade – upgrade / plan page for current user's company
    Route::get('/upgrade', function () {
        $companyId = auth()->user()?->company_id;
        if ($companyId) {
            $company = \App\Models\Company::find($companyId);
            $query = request()->query('limit') ? ['limit' => request()->query('limit')] : [];
            return redirect()->route('app.companies.upgrade', [$company] + $query);
        }
        return redirect()->route('app.dashboard')->with('info', 'Sélectionnez une entreprise.');
    })->name('upgrade.redirect');

// Support page for company users (admin / agent) – contact support
    Route::get('/support', function () {
        return view('app.support.index', ['title' => 'Support']);
    })->name('support.index');

    // Support & platform admin – super_admin + support
        Route::middleware('platform_staff')->group(function () {
            Route::get('/support-search', [SupportSearchController::class, 'index'])->name('search.index');
            Route::get('/subscriptions', [SupportSubscriptionController::class, 'index'])->name('subscriptions.index');
            Route::post('/subscriptions/{company}/extend-trial', [SupportSubscriptionController::class, 'extendTrial'])->name('subscriptions.extend-trial');
            Route::post('/subscriptions/{company}/status', [SupportSubscriptionController::class, 'updateStatus'])->name('subscriptions.update-status');
            Route::post('/subscriptions/{company}/notes', [SupportSubscriptionController::class, 'updateNotes'])->name('subscriptions.update-notes');
            Route::get('/inbox', [TicketController::class, 'index'])->name('inbox.index');
            Route::get('/inbox/create', [TicketController::class, 'create'])->name('inbox.create');
            Route::post('/inbox', [TicketController::class, 'store'])->name('inbox.store');
            Route::get('/inbox/{ticket}', [TicketController::class, 'show'])->name('inbox.show');
            Route::put('/inbox/{ticket}', [TicketController::class, 'update'])->name('inbox.update');
            Route::post('/inbox/{ticket}/reply', [TicketController::class, 'reply'])->name('inbox.reply');
            Route::get('/journal', [AuditLogController::class, 'index'])->name('journal.index');
        });

        Route::middleware('platform_staff')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/upgrade-requests', [UpgradeRequestController::class, 'index'])->name('upgrade-requests.index');
            Route::get('/upgrade-requests/{upgradeRequest}', [UpgradeRequestController::class, 'show'])->name('upgrade-requests.show');
            Route::put('/upgrade-requests/{upgradeRequest}', [UpgradeRequestController::class, 'update'])->name('upgrade-requests.update');
            Route::post('/upgrade-requests/{upgradeRequest}/approve', [UpgradeRequestController::class, 'approve'])->name('upgrade-requests.approve');
            Route::post('/upgrade-requests/{upgradeRequest}/reject', [UpgradeRequestController::class, 'reject'])->name('upgrade-requests.reject');
            Route::get('/users', [PlatformUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [PlatformUserController::class, 'create'])->name('users.create');
        Route::post('/users', [PlatformUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [PlatformUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [PlatformUserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/activate', [PlatformUserController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/suspend', [PlatformUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/force-password-reset', [PlatformUserController::class, 'forcePasswordReset'])->name('users.force-password-reset');
        Route::delete('/users/{user}', [PlatformUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/contract-templates', [ContractTemplateController::class, 'index'])->name('contract-templates.index');
        Route::get('/contract-templates/create', [ContractTemplateController::class, 'create'])->name('contract-templates.create');
        Route::post('/contract-templates', [ContractTemplateController::class, 'store'])->name('contract-templates.store');
        Route::get('/contract-templates/{contractTemplate}', [ContractTemplateController::class, 'show'])->name('contract-templates.show');
        Route::get('/contract-templates/{contractTemplate}/preview-frame', [ContractTemplateController::class, 'previewFrame'])->name('contract-templates.preview-frame');
        Route::get('/contract-templates/{contractTemplate}/edit', [ContractTemplateController::class, 'edit'])->name('contract-templates.edit');
        Route::put('/contract-templates/{contractTemplate}', [ContractTemplateController::class, 'update'])->name('contract-templates.update');
        Route::delete('/contract-templates/{contractTemplate}', [ContractTemplateController::class, 'destroy'])->name('contract-templates.destroy');

        // Plans & Platform Settings – super_admin only
        Route::middleware('super_admin')->group(function () {
            Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
            Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
            Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
            Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
            Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
            Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
            Route::get('/settings', [\App\Http\Controllers\App\Admin\PlatformSettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [\App\Http\Controllers\App\Admin\PlatformSettingsController::class, 'update'])->name('settings.update');
        });
    });

    // Company contract-templates & subscription (operational; subscription actions = company_admin only)
    Route::middleware('company_operational')->group(function () {
        Route::get('/companies/{company}/contract-templates', [CompanyContractTemplateController::class, 'index'])->name('companies.contract-templates.index');
        Route::get('/companies/{company}/contract-templates/create', [CompanyContractTemplateController::class, 'create'])->name('companies.contract-templates.create');
        Route::post('/companies/{company}/contract-templates', [CompanyContractTemplateController::class, 'store'])->name('companies.contract-templates.store');
        Route::get('/companies/{company}/contract-templates/{contractTemplate}/edit', [CompanyContractTemplateController::class, 'edit'])->name('companies.contract-templates.edit');
        Route::put('/companies/{company}/contract-templates/{contractTemplate}', [CompanyContractTemplateController::class, 'update'])->name('companies.contract-templates.update');
        Route::delete('/companies/{company}/contract-templates/{contractTemplate}', [CompanyContractTemplateController::class, 'destroy'])->name('companies.contract-templates.destroy');
        Route::post('/companies/{company}/contract-templates/set-default', [CompanyContractTemplateController::class, 'setDefault'])->name('companies.contract-templates.set-default');
    });
    Route::middleware(['company_access', 'company_admin_only'])->group(function () {
        Route::get('/companies/{company}/subscription/change-plan', [CompanySubscriptionController::class, 'changePlan'])->name('companies.subscription.change-plan');
        Route::post('/companies/{company}/subscription/change-plan', [CompanySubscriptionController::class, 'updatePlan'])->name('companies.subscription.update-plan');
        Route::post('/companies/{company}/subscription/activate', [CompanySubscriptionController::class, 'activate'])->name('companies.subscription.activate');
        Route::post('/companies/{company}/subscription/suspend', [CompanySubscriptionController::class, 'suspend'])->name('companies.subscription.suspend');
        Route::post('/companies/{company}/subscription/extend-trial', [CompanySubscriptionController::class, 'extendTrial'])->name('companies.subscription.extend-trial');
    });
});

// Dev-only: email previews (local environment)
if (app()->environment('local')) {
    Route::get('/dev/mail-preview/{type}', function (string $type) {
        $types = ['welcome', 'reset-password', 'ticket-created', 'document-expiring'];
        if (! in_array($type, $types, true)) {
            abort(404, 'Preview type must be: ' . implode(', ', $types));
        }
        $user = new \App\Models\User(['name' => 'Jean Dupont', 'email' => 'jean@example.com']);
        $baseUrl = config('app.url');
        if ($type === 'welcome') {
            $mailable = new \App\Mail\WelcomeMail($user, $baseUrl . '/app');
            return $mailable->render();
        }
        if ($type === 'reset-password') {
            $mailable = new \App\Mail\ResetPasswordMail('Jean Dupont', $baseUrl . '/admin/reset-password?token=abc123&email=jean@example.com', 60);
            return $mailable->render();
        }
        if ($type === 'ticket-created') {
            $ticket = new \App\Models\Ticket(['subject' => 'Problème de connexion', 'company_id' => 1]);
            $ticket->id = 42;
            $ticket->setRelation('company', new \App\Models\Company(['name' => 'Location Auto SARL']));
            $mailable = new \App\Mail\TicketCreatedMail($ticket, 'Support GLM', $baseUrl . '/app/inbox/42');
            return $mailable->render();
        }
        if ($type === 'document-expiring') {
            $mailable = new \App\Mail\DocumentExpiringMail(
                'Jean Dupont',
                'Assurance',
                '12345-A-1',
                'Renault Clio (2022)',
                now()->addDays(15)->format('d/m/Y'),
                15,
                $baseUrl . '/app/companies/1/vehicles/1'
            );
            return $mailable->render();
        }
        abort(404);
    })->name('dev.mail-preview');
}
