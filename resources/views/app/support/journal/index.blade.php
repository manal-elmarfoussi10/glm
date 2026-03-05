@extends('app.layouts.app')

@section('pageSubtitle')
Journal d’actions (lecture seule)
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight glm-text">Journal d’actions</h1>
            <p class="mt-2 glm-muted">Historique des actions (approbations, refus, suspensions, tickets…). Lecture seule.</p>
        </div>
        <a href="{{ route('app.dashboard') }}" class="glm-btn-secondary no-underline">Dashboard</a>
    </header>

    <form method="get" action="{{ route('app.journal.index') }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div>
            <label for="filter-action" class="mb-1.5 block text-xs font-medium uppercase tracking-wider glm-muted">Action</label>
            <input type="text" id="filter-action" name="action" value="{{ request('action') }}" placeholder="ex: registration.approved" class="glm-input rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50">
        </div>
        <div>
            <label for="filter-from" class="mb-1.5 block text-xs font-medium uppercase tracking-wider glm-muted">Du</label>
            <input type="date" id="filter-from" name="from" value="{{ request('from') }}" class="glm-input rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50">
        </div>
        <div>
            <label for="filter-to" class="mb-1.5 block text-xs font-medium uppercase tracking-wider glm-muted">Au</label>
            <input type="date" id="filter-to" name="to" value="{{ request('to') }}" class="glm-input rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50">
        </div>
        <button type="submit" class="glm-btn-primary">Filtrer</button>
        @if (request()->hasAny(['action', 'from', 'to']))
            <a href="{{ route('app.journal.index') }}" class="glm-btn-secondary no-underline">Effacer</a>
        @endif
    </form>

    <div class="glm-table-wrap overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="sticky top-0 z-[5]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Sujet</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="transition-colors">
                            <td class="px-6 py-4 text-sm glm-text whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-6 py-4 text-sm glm-text">{{ $log->user?->name ?? '–' }}</td>
                            <td class="px-6 py-4">
                                <code class="rounded bg-[color:var(--surface-2)] px-2 py-0.5 text-xs text-[color:var(--primary)]">{{ $log->action }}</code>
                            </td>
                            <td class="px-6 py-4 text-sm glm-muted">{{ $log->subject_type }} #{{ $log->subject_id }}</td>
                            <td class="px-6 py-4 text-sm glm-muted max-w-xs">
                                @if ($log->new_values)
                                    <pre class="text-xs overflow-x-auto whitespace-pre-wrap glm-text">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    –
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center glm-muted">Aucune entrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="border-t border-[color:var(--glm-border)] px-6 py-4">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
