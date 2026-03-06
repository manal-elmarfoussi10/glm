<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Support tickets for company users: create, list, show, reply.
 * Tickets are scoped to the authenticated user's company.
 */
class SupportTicketController extends Controller
{
    private function userCompanyId(): ?int
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role, ['company_admin', 'agent'], true)) {
            return null;
        }
        return $user->company_id ? (int) $user->company_id : null;
    }

    private function ensureTicketForCompany(Ticket $ticket): void
    {
        $companyId = $this->userCompanyId();
        if ($companyId === null || $ticket->company_id != $companyId) {
            abort(404);
        }
    }

    /**
     * Support page: create form + list of my company's tickets.
     */
    public function index(Request $request): View
    {
        $companyId = $this->userCompanyId();
        $tickets = collect();
        if ($companyId !== null) {
            $tickets = Ticket::where('company_id', $companyId)
                ->with('user')
                ->orderByRaw("CASE WHEN status IN ('new','open') THEN 0 ELSE 1 END")
                ->orderByDesc('updated_at')
                ->paginate(10, ['*'], 'tickets_page')
                ->withQueryString();
        }

        $canCreateTicket = $companyId !== null;
        $isPlatformStaff = Auth::user() && in_array(Auth::user()->role, ['super_admin', 'support'], true);

        return view('app.support.index', [
            'title' => 'Support',
            'tickets' => $tickets,
            'canCreateTicket' => $canCreateTicket,
            'isPlatformStaff' => $isPlatformStaff,
        ]);
    }

    /**
     * Create a ticket (company user).
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->userCompanyId();
        if ($companyId === null) {
            return redirect()->route('app.support.index')->with('error', 'Action non autorisée.');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
        ]);

        $ticket = Ticket::create([
            'subject' => $validated['subject'],
            'company_id' => $companyId,
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'status' => 'new',
            'assigned_to' => null,
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
            'is_internal' => false,
        ]);

        $ticket->load('company');
        $ticketUrl = route('app.inbox.show', $ticket);
        User::whereIn('role', ['super_admin', 'support'])->get()->each(function ($u) use ($ticket, $ticketUrl) {
            $u->notify(new \App\Notifications\TicketCreatedNotification($ticket, $ticketUrl));
        });

        return redirect()->route('app.support.tickets.show', $ticket)->with('success', 'Votre demande a été envoyée. Nous vous répondrons sous 24 à 48 h.');
    }

    /**
     * Show one ticket and reply form (company user).
     */
    public function show(Ticket $ticket): View
    {
        $this->ensureTicketForCompany($ticket);
        $ticket->load(['replies' => fn ($q) => $q->where('is_internal', false)->with('user')->orderBy('created_at')]);

        return view('app.support.tickets.show', [
            'title' => $ticket->subject,
            'ticket' => $ticket,
        ]);
    }

    /**
     * Reply to a ticket (company user). Only non-internal replies.
     */
    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->ensureTicketForCompany($ticket);

        $validated = $request->validate([
            'body' => 'required|string|max:10000',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
            'is_internal' => false,
        ]);

        if ($ticket->status === 'new') {
            $ticket->update(['status' => 'open']);
        }

        $ticket->load(['company', 'user']);
        $ticketUrl = route('app.inbox.show', $ticket);
        $replierName = Auth::user()->name ?? Auth::user()->email;
        User::whereIn('role', ['super_admin', 'support'])->get()->each(function ($u) use ($ticket, $ticketUrl, $replierName) {
            $u->notify(new \App\Notifications\TicketReplyNotification($ticket, $replierName, $ticketUrl));
        });

        return back()->with('success', 'Réponse envoyée.');
    }
}
