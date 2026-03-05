<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ReservationInspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyDamageController extends Controller
{
    public function index(Request $request, Company $company): View
    {
        $query = ReservationInspection::query()
            ->with(['reservation.vehicle', 'reservation.customer', 'photos'])
            ->whereHas('reservation', fn ($q) => $q->where('company_id', $company->id))
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('type', ReservationInspection::TYPE_OUT)
                        ->whereNotNull('damage_checklist');
                    if (DB::connection()->getDriverName() === 'mysql') {
                        $q2->whereRaw('JSON_LENGTH(damage_checklist) > 0');
                    } else {
                        $q2->where('damage_checklist', '!=', '[]');
                    }
                })->orWhere(function ($q2) {
                    $q2->where('type', ReservationInspection::TYPE_IN)->whereNotNull('new_damages')->where('new_damages', '!=', '');
                });
            });

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->whereHas('reservation', function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                    ->orWhereHas('vehicle', fn ($v) => $v->where('plate', 'like', $term))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term));
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->where('inspected_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('inspected_at', '<=', $request->date_to . ' 23:59:59');
        }

        $damages = $query->orderByDesc('inspected_at')->paginate(20)->withQueryString();

        return view('app.companies.damages.index', [
            'title' => 'Dégâts – ' . $company->name,
            'company' => $company,
            'damages' => $damages,
        ]);
    }
}
