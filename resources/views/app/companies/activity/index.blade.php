@extends('app.layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center mb-8">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold text-white">Journal d'activité</h1>
            <p class="mt-2 text-sm text-slate-400">
                Historique complet des actions effectuées au sein de {{ $company->name }}.
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-6 rounded-2xl border border-white/10 bg-slate-900/50 p-4">
        <form method="GET" action="{{ route('app.companies.activity.index', $company) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label for="user_id" class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Utilisateur</label>
                <select name="user_id" id="user_id" class="block w-full rounded-xl border border-white/10 bg-white/5 py-2 px-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="">Tous les utilisateurs</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="action" class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Type d'action</label>
                <select name="action" id="action" class="block w-full rounded-xl border border-white/10 bg-white/5 py-2 px-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="">Toutes les actions</option>
                    @foreach($actions as $slug => $label)
                        <option value="{{ $slug }}" {{ request('action') == $slug ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="from" class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Du</label>
                <input type="date" name="from" id="from" value="{{ request('from') }}" class="block w-full rounded-xl border border-white/10 bg-white/5 py-2 px-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]/50" />
            </div>
            <div>
                <label for="to" class="block text-xs font-medium text-slate-400 mb-1.5 uppercase tracking-wider">Au</label>
                <input type="date" name="to" id="to" value="{{ request('to') }}" class="block w-full rounded-xl border border-white/10 bg-white/5 py-2 px-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]/50" />
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition ring-offset-slate-900 focus:ring-2 focus:ring-[#2563EB]">
                    Filtrer
                </button>
                <a href="{{ route('app.companies.activity.index', $company) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Timeline Content --}}
    <div class="rounded-2xl border border-white/10 bg-slate-900/55 shadow-xl overflow-hidden">
        <table class="min-w-full divide-y divide-white/5">
            <thead>
                <tr class="bg-white/2">
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Date & Heure</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Utilisateur</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Action</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 bg-transparent">
                @forelse($logs as $log)
                    <tr class="hover:bg-white/2 transition">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-300">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center">
                                <span class="h-7 w-7 flex items-center justify-center rounded-lg bg-[#2563EB]/10 text-xs font-semibold text-[#60A5FA] mr-2">
                                    {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                </span>
                                <span class="text-sm font-medium text-white">{{ $log->user->name ?? 'Système' }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @php
                                $badgeColor = match($log->action) {
                                    'reservation_created', 'vehicle_created', 'payment_created', 'expense_created' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'reservation_status_changed', 'vehicle_updated' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'vehicle_deleted' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                    'csv_export', 'document_download' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    default => 'bg-slate-500/10 text-slate-400 border-slate-500/20'
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-lg border px-2 py-0.5 text-[11px] font-semibold {{ $badgeColor }}">
                                {{ $actions[$log->action] ?? str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-300">
                            {{ $log->description }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-400">
                            Aucune activité trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-white/5">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
