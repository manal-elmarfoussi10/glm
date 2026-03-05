<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function index(Request $request, Company $company): View
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->subDays(30)->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            $from = $to->copy()->subDays(30)->startOfDay();
        }

        $data = $this->reportService->forCompany($company, $from, $to);

        return view('app.companies.reports.index', [
            'title' => 'Rapports & analyses – ' . $company->name,
            'company' => $company,
            'from' => $from,
            'to' => $to,
            'data' => $data,
        ]);
    }

    public function exportCsv(Request $request, Company $company): StreamedResponse
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->subDays(30)->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();
        if ($from->gt($to)) {
            $from = $to->copy()->subDays(30)->startOfDay();
        }

        $rows = $this->reportService->exportCsvRows($company, $from, $to);

        $filename = 'rapport-' . $company->id . '-' . $from->format('Y-m-d') . '-' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
