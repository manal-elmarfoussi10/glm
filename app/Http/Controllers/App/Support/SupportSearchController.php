<?php

namespace App\Http\Controllers\App\Support;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportSearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->input('q', '');
        $q = trim($q);
        $companies = collect();
        $users = collect();
        $requests = collect();
        $tickets = collect();

        if (strlen($q) >= 2) {
            $companies = Company::query()
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%' . $q . '%')
                        ->orWhere('ice', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                })
                ->limit(10)
                ->get();

            $users = User::query()
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                })
                ->limit(10)
                ->get();

            $requests = User::query()
                ->where('role', 'company_admin')
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%')
                        ->orWhere('requested_company_name', 'like', '%' . $q . '%');
                })
                ->limit(10)
                ->get();

            $tickets = Ticket::query()
                ->where('subject', 'like', '%' . $q . '%')
                ->with(['company', 'assignedTo'])
                ->limit(10)
                ->get();
        }

        return view('app.support.search', [
            'title' => 'Recherche',
            'q' => $q,
            'companies' => $companies,
            'users' => $users,
            'requests' => $requests,
            'tickets' => $tickets,
        ]);
    }
}
