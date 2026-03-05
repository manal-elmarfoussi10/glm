<?php

namespace App\Http\Controllers\App\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', '%' . $request->action . '%'))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to));

        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('app.admin.audit-logs.index', [
            'title' => 'Journal d’audit',
            'logs' => $logs,
        ]);
    }
}
