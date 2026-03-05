<?php

namespace App\Http\Controllers\App\Support;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\ReplyTemplate;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ticket::query()->with(['company', 'assignedTo', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('assigned')) {
            if ($request->assigned === 'me') {
                $query->where('assigned_to', auth()->id());
            } elseif ($request->assigned === 'unassigned') {
                $query->whereNull('assigned_to');
            }
        }

        $tickets = $query->orderByRaw("CASE WHEN status IN ('new','open') THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'new' => Ticket::where('status', 'new')->count(),
            'open' => Ticket::where('status', 'open')->count(),
            'waiting' => Ticket::where('status', 'waiting')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
        ];

        return view('app.support.inbox.index', [
            'title' => 'Inbox / Messages',
            'tickets' => $tickets,
            'counts' => $counts,
        ]);
    }

    public function create(): View
    {
        $companies = Company::orderBy('name')->get();
        $agents = User::whereIn('role', ['super_admin', 'support'])->orderBy('name')->get();

        return view('app.support.inbox.create', [
            'title' => 'Nouveau ticket',
            'companies' => $companies,
            'agents' => $agents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email',
            'body' => 'required|string|max:10000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket = Ticket::create([
            'subject' => $validated['subject'],
            'company_id' => $validated['company_id'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'email' => $validated['email'] ?? null,
            'status' => 'new',
            'assigned_to' => $validated['assigned_to'] ?? null,
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'is_internal' => false,
        ]);

        AuditLog::log('ticket.created', Ticket::class, (int) $ticket->id, null, ['subject' => $ticket->subject]);

        return redirect()->route('app.inbox.show', $ticket)->with('success', 'Ticket créé.');
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['replies.user', 'company', 'user', 'assignedTo']);
        $agents = User::whereIn('role', ['super_admin', 'support'])->orderBy('name')->get();
        $templates = ReplyTemplate::orderBy('name')->get();

        return view('app.support.inbox.show', [
            'title' => $ticket->subject,
            'ticket' => $ticket,
            'agents' => $agents,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:new,open,waiting,resolved',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (isset($validated['status'])) {
            $ticket->update(['status' => $validated['status']]);
        }
        if (array_key_exists('assigned_to', $validated)) {
            $ticket->update(['assigned_to' => $validated['assigned_to'] ?: null]);
        }

        return back()->with('success', 'Ticket mis à jour.');
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:10000',
            'is_internal' => 'boolean',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'is_internal' => (bool) ($validated['is_internal'] ?? false),
        ]);

        if ($ticket->status === 'new') {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Réponse enregistrée.');
    }
}
