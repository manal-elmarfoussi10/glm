<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ReservationPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyPaymentController extends Controller
{
    public function index(Request $request, Company $company): View
    {
        $query = ReservationPayment::query()
            ->with(['reservation.vehicle', 'reservation.customer'])
            ->whereHas('reservation', fn ($q) => $q->where('company_id', $company->id));

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->whereHas('reservation', function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                    ->orWhereHas('vehicle', fn ($v) => $v->where('plate', 'like', $term))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('cin', 'like', $term));
            });
        }
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->where('paid_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('paid_at', '<=', $request->date_to);
        }
        if ($request->filled('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id)
                    ->orWhereHas('reservation', fn ($r) => $r->where('pickup_branch_id', $request->branch_id)
                        ->orWhere('return_branch_id', $request->branch_id)
                        ->orWhereHas('vehicle', fn ($v) => $v->where('branch_id', $request->branch_id)));
            });
        }

        $payments = $query->orderByDesc('paid_at')->paginate(20)->withQueryString();
        $branches = $company->branches()->orderBy('name')->get();

        return view('app.companies.payments.index', [
            'title' => 'Paiements – ' . $company->name,
            'company' => $company,
            'payments' => $payments,
            'branches' => $branches,
        ]);
    }
}
