<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyActivityController extends Controller
{
    /**
     * Requirement: Timeline view for company_admin.
     */
    public function index(Company $company, Request $request): View
    {
        $query = ActivityLog::query()
            ->where('company_id', $company->id)
            ->with('user')
            ->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(30);
        $users = $company->users()->pluck('name', 'id');
        
        $actions = [
            'reservation_created' => 'Réservations',
            'reservation_status_changed' => 'Changements statut',
            'payment_created' => 'Paiements',
            'vehicle_created' => 'Véhicules',
            'expense_created' => 'Dépenses',
            'csv_export' => 'Exports CSV',
            'document_download' => 'Téléchargements',
        ];

        return view('app.companies.activity.index', [
            'title' => 'Journal d\'activité',
            'company' => $company,
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions,
        ]);
    }
}
