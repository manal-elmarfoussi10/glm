<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppSearchController extends Controller
{
    protected $searchService;

    public function __construct(GlobalSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Handle the full results page.
     */
    public function index(Request $request): View
    {
        $query = $request->get('q', '');
        $companyId = auth()->user()->isSuperAdmin() || auth()->user()->isSupport() 
            ? null 
            : auth()->user()->company_id;

        $results = $this->searchService->search($query, $companyId);

        return view('app.search.index', [
            'title' => 'Recherche',
            'query' => $query,
            'resultsByGroup' => $results->groupBy('type'),
        ]);
    }

    /**
     * Handle AJAX search for the dropdown.
     */
    public function ajax(Request $request)
    {
        $query = $request->get('q', '');
        $companyId = auth()->user()->isSuperAdmin() || auth()->user()->isSupport() 
            ? null 
            : auth()->user()->company_id;

        $results = $this->searchService->search($query, $companyId);

        return response()->json($results->groupBy('type'));
    }
}
