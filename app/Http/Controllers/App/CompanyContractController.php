<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ReservationContract;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyContractController extends Controller
{
    public function index(Request $request, Company $company): View
    {
        $query = ReservationContract::query()
            ->with(['reservation.vehicle', 'reservation.customer'])
            ->whereHas('reservation', fn ($q) => $q->where('company_id', $company->id));

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->whereHas('reservation', function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('cin', 'like', $term))
                    ->orWhereHas('vehicle', fn ($v) => $v->where('plate', 'like', $term));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('generated_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('generated_at', '<=', $request->date_to . ' 23:59:59');
        }

        $contracts = $query->orderByDesc('generated_at')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('app.companies.contracts.index', [
            'title' => 'Contrats – ' . $company->name,
            'company' => $company,
            'contracts' => $contracts,
        ]);
    }
}
