@extends('app.layouts.app')

@section('pageSubtitle')
Tickets et messages
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Inbox / Messages</h1>
            <p class="mt-2 text-slate-400">Tickets : New, Open, Waiting, Resolved. Assignez un agent et répondez.</p>
        </div>
        <a href="{{ route('app.inbox.create') }}" class="glm-btn-primary inline-flex no-underline">Nouveau ticket</a>
    </header>

    {{-- Status filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('app.inbox.index', array_merge(request()->except('status'), ['status' => null])) }}" class="rounded-xl px-4 py-2 text-sm font-medium transition {{ !request('status') ? 'bg-[#2563EB] text-white' : 'bg-white/5 text-slate-400 hover:bg-white/10' }} no-underline">Tous</a>
        <a href="{{ route('app.inbox.index', array_merge(request()->except('status'), ['status' => 'new'])) }}" class="rounded-xl px-4 py-2 text-sm font-medium transition {{ request('status') === 'new' ? 'bg-blue-500/30 text-blue-300' : 'bg-white/5 text-slate-400 hover:bg-white/10' }} no-underline">New ({{ $counts['new'] }})</a>
        <a href="{{ route('app.inbox.index', array_merge(request()->except('status'), ['status' => 'open'])) }}" class="rounded-xl px-4 py-2 text-sm font-medium transition {{ request('status') === 'open' ? 'bg-amber-500/30 text-amber-300' : 'bg-white/5 text-slate-400 hover:bg-white/10' }} no-underline">Open ({{ $counts['open'] }})</a>
        <a href="{{ route('app.inbox.index', array_merge(request()->except('status'), ['status' => 'waiting'])) }}" class="rounded-xl px-4 py-2 text-sm font-medium transition {{ request('status') === 'waiting' ? 'bg-amber-500/30 text-amber-300' : 'bg-white/5 text-slate-400 hover:bg-white/10' }} no-underline">Waiting ({{ $counts['waiting'] }})</a>
        <a href="{{ route('app.inbox.index', array_merge(request()->except('status'), ['status' => 'resolved'])) }}" class="rounded-xl px-4 py-2 text-sm font-medium transition {{ request('status') === 'resolved' ? 'glm-badge-approved' : 'bg-white/5 text-slate-400 hover:bg-white/10' }} no-underline">Resolved ({{ $counts['resolved'] }})</a>
        @if (request()->has('assigned'))
            <a href="{{ route('app.inbox.index', request()->except('assigned')) }}" class="rounded-xl px-4 py-2 text-sm text-slate-400 hover:text-white no-underline">× Assigned filter</a>
        @endif
    </div>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Sujet</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Entreprise / Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Assigné à</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Mis à jour</th>
                        <th class="w-0 px-6 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($tickets as $t)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4">
                                <a href="{{ route('app.inbox.show', $t) }}" class="font-semibold text-white hover:text-[#93C5FD] no-underline">{{ Str::limit($t->subject, 50) }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $t->company?->name ?? $t->email ?? '–' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $t->status === 'new' ? 'bg-blue-500/20 text-blue-400' : ($t->status === 'resolved' ? 'glm-badge-approved' : 'bg-amber-500/20 text-amber-400') }}">{{ $t->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $t->assignedTo?->name ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $t->updated_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('app.inbox.show', $t) }}" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline">Ouvrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Aucun ticket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tickets->hasPages())
            <div class="border-t border-white/10 px-6 py-4">{{ $tickets->links() }}</div>
        @endif
    </div>
</div>
@endsection
