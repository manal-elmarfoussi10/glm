@extends('app.layouts.app')

@section('pageSubtitle')
Rentabilité flotte – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Rentabilité flotte</h1>
            <p class="mt-1 text-sm text-slate-400">Revenus, coûts et profit par véhicule sur la période choisie.</p>
        </div>
    </header>

    @php $d = $data; @endphp

    {{-- Date filter: This month, Last month, Custom --}}
    <form method="get" action="{{ route('app.companies.fleet.profitability.index', $company) }}" class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)] flex flex-wrap items-end gap-4">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <div class="flex flex-wrap items-center gap-3">
            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                <input type="radio" name="period" value="this_month" {{ request('period') === 'this_month' || (!request('period') && !request('from')) ? 'checked' : '' }} onchange="this.form.submit()">
                Ce mois
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                <input type="radio" name="period" value="last_month" {{ request('period') === 'last_month' ? 'checked' : '' }} onchange="this.form.submit()">
                Mois dernier
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                <input type="radio" name="period" value="custom" {{ request('from') ? 'checked' : '' }} id="period_custom">
                Plage personnalisée
            </label>
        </div>
        <div id="custom_range" class="flex flex-wrap items-end gap-4 {{ request('from') ? '' : 'hidden' }}">
            <div>
                <label for="from" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Du</label>
                <input type="date" id="from" name="from" value="{{ $from->format('Y-m-d') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
            </div>
            <div>
                <label for="to" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Au</label>
                <input type="date" id="to" name="to" value="{{ $to->format('Y-m-d') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
            </div>
            <button type="submit" class="rounded-xl bg-gradient-to-r from-[#2563EB] to-[#2563EB]/70 px-4 py-2.5 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(37,99,235,0.22)] hover:brightness-[1.03] transition">Actualiser</button>
        </div>
    </form>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <p class="text-sm font-semibold text-white/60">Revenus flotte (période)</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-emerald-400">{{ number_format($d['revenue_total'], 0, ',', ' ') }} MAD</p>
            <p class="mt-1 text-xs text-slate-500">{{ $d['days_in_range'] }} jours</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <p class="text-sm font-semibold text-white/60">Coûts flotte (période)</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-amber-400">{{ number_format($d['cost_total'], 0, ',', ' ') }} MAD</p>
            <p class="mt-1 text-xs text-slate-500">Fin. · Ass. · Vignette · Dépenses</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <p class="text-sm font-semibold text-white/60">Profit net</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight {{ $d['net_profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ number_format($d['net_profit'], 0, ',', ' ') }} MAD</p>
            <p class="mt-1 text-xs text-slate-500">Revenus − coûts</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <p class="text-sm font-semibold text-white/60">Profit moyen / véhicule</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-[#93C5FD]">{{ number_format($d['avg_profit_per_vehicle'], 0, ',', ' ') }} MAD</p>
            <p class="mt-1 text-xs text-slate-500">{{ $d['vehicle_count'] }} véhicules</p>
        </div>
    </div>

    {{-- Vehicle profitability table --}}
    <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden">
        <div class="p-4 border-b border-white/10 flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-base font-extrabold text-white/90">Rentabilité par véhicule</h2>
            <form method="get" action="{{ route('app.companies.fleet.profitability.index', $company) }}" class="flex items-center gap-2" id="sortForm">
                @if (request('period'))<input type="hidden" name="period" value="{{ request('period') }}">@endif
                @if (request('from'))<input type="hidden" name="from" value="{{ request('from') }}">@endif
                @if (request('to'))<input type="hidden" name="to" value="{{ request('to') }}">@endif
                <label for="sort" class="text-sm text-slate-400">Trier par</label>
                <select name="sort" id="sort" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white">
                    <option value="profit_desc" {{ $sort === 'profit_desc' ? 'selected' : '' }}>Plus rentable</option>
                    <option value="profit_asc" {{ $sort === 'profit_asc' ? 'selected' : '' }}>Moins rentable</option>
                    <option value="cost_desc" {{ $sort === 'cost_desc' ? 'selected' : '' }}>Coût le plus élevé</option>
                    <option value="cost_asc" {{ $sort === 'cost_asc' ? 'selected' : '' }}>Coût le plus faible</option>
                    <option value="utilization_desc" {{ $sort === 'utilization_desc' ? 'selected' : '' }}>Utilisation haute</option>
                    <option value="utilization_asc" {{ $sort === 'utilization_asc' ? 'selected' : '' }}>Utilisation basse</option>
                </select>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Véhicule</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Revenus</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Financement</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Assurance</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Maintenance</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Coût total</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Profit net</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Marge %</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">ROI %</th>
                        <th class="w-0 px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($d['vehicle_rows'] as $row)
                        @php $v = $row['vehicle']; @endphp
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.fleet.profitability.show', [$company, $v]) }}?period={{ request('period') }}&from={{ request('from') }}&to={{ request('to') }}" class="font-semibold text-white hover:text-[#93C5FD] no-underline">{{ $v->brand }} {{ $v->model }}</a>
                                <p class="text-sm text-slate-400">{{ $v->plate }}</p>
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-emerald-400">{{ number_format($row['revenue'], 0, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4 text-right text-sm text-slate-300">{{ number_format($row['financing_cost'], 0, ',', ' ') }}</td>
                            <td class="px-6 py-4 text-right text-sm text-slate-300">{{ number_format($row['insurance_cost'], 0, ',', ' ') }}</td>
                            <td class="px-6 py-4 text-right text-sm text-slate-300">{{ number_format($row['maintenance_cost'], 0, ',', ' ') }}</td>
                            <td class="px-6 py-4 text-right text-sm text-amber-400">{{ number_format($row['total_cost'], 0, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4 text-right text-sm font-semibold {{ $row['net_profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ number_format($row['net_profit'], 0, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4 text-right text-sm {{ $row['profit_margin'] >= 0 ? 'text-slate-300' : 'text-red-400' }}">{{ $row['profit_margin'] }} %</td>
                            <td class="px-6 py-4 text-right text-sm {{ $row['roi'] >= 0 ? 'text-slate-300' : 'text-red-400' }}">{{ $row['roi'] }} %</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.fleet.profitability.show', [$company, $v]) }}?period={{ request('period') }}&from={{ request('from') }}&to={{ request('to') }}" class="text-sm font-medium text-[#2563EB] hover:text-[#93C5FD] no-underline">Détail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-slate-500">Aucun véhicule dans la flotte.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="period"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        if (this.value === 'custom') {
            document.getElementById('custom_range').classList.remove('hidden');
        } else {
            document.getElementById('custom_range').classList.add('hidden');
        }
    });
});
if (document.getElementById('period_custom')?.checked) {
    document.getElementById('custom_range').classList.remove('hidden');
}
</script>
@endsection
