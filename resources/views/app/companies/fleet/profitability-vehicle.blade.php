@extends('app.layouts.app')

@section('pageSubtitle')
Rentabilité – {{ $vehicle->plate }} – {{ $company->name }}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.fleet.profitability.index', $company) }}?period={{ request('period') }}&from={{ request('from') }}&to={{ request('to') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Rentabilité flotte</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">{{ $vehicle->brand }} {{ $vehicle->model }}</h1>
            <p class="mt-1 text-sm text-slate-400">Plaque {{ $vehicle->plate }} · Période du {{ $data['from']->format('d/m/Y') }} au {{ $data['to']->format('d/m/Y') }}</p>
        </div>
    </header>

    @php $d = $data; @endphp

    {{-- Revenue breakdown --}}
    <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
        <h2 class="text-base font-extrabold text-white/90">Revenus</h2>
        <p class="mt-1 text-sm text-white/60">Réservations terminées ou en cours sur la période.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Réservations</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ $d['total_reservations'] }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Revenus total</p>
                <p class="mt-1 text-2xl font-bold text-emerald-400">{{ number_format($d['total_revenue'], 0, ',', ' ') }} MAD</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Durée moyenne location</p>
                <p class="mt-1 text-2xl font-bold text-[#93C5FD]">{{ $d['avg_rental_duration'] }} jours</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Taux d'utilisation</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ $d['utilization_rate'] }} %</p>
            </div>
        </div>
    </div>

    {{-- Cost breakdown --}}
    <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
        <h2 class="text-base font-extrabold text-white/90">Coûts</h2>
        <p class="mt-1 text-sm text-white/60">Financement, assurance, vignette, maintenance et autres dépenses (période).</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Financement</p>
                <p class="mt-1 text-xl font-bold text-amber-400">{{ number_format($d['cost_financing'], 0, ',', ' ') }} MAD</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Assurance</p>
                <p class="mt-1 text-xl font-bold text-amber-400">{{ number_format($d['cost_insurance'], 0, ',', ' ') }} MAD</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Vignette</p>
                <p class="mt-1 text-xl font-bold text-amber-400">{{ number_format($d['cost_vignette'], 0, ',', ' ') }} MAD</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Maintenance</p>
                <p class="mt-1 text-xl font-bold text-amber-400">{{ number_format($d['cost_maintenance'], 0, ',', ' ') }} MAD</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Autres dépenses</p>
                <p class="mt-1 text-xl font-bold text-amber-400">{{ number_format($d['cost_other'], 0, ',', ' ') }} MAD</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-amber-500/10 p-4">
                <p class="text-sm text-slate-400">Coût total</p>
                <p class="mt-1 text-xl font-bold text-amber-400">{{ number_format($d['total_cost'], 0, ',', ' ') }} MAD</p>
            </div>
        </div>
    </div>

    {{-- Profit analysis --}}
    <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
        <h2 class="text-base font-extrabold text-white/90">Analyse profit</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Profit net</p>
                <p class="mt-1 text-2xl font-bold {{ $d['net_profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ number_format($d['net_profit'], 0, ',', ' ') }} MAD</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Profit moyen / mois</p>
                <p class="mt-1 text-2xl font-bold text-[#93C5FD]">{{ number_format($d['monthly_profit_avg'], 0, ',', ' ') }} MAD</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Seuil de rentabilité</p>
                <p class="mt-1 text-xl font-bold text-white">{{ $d['break_even_days'] ? $d['break_even_days'] . ' jours pour couvrir le coût mensuel' : '–' }}</p>
            </div>
        </div>
    </div>

    {{-- Bar chart: Revenue vs Cost --}}
    <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
        <h2 class="text-base font-extrabold text-white/90">Revenus vs Coûts</h2>
        <p class="mt-1 text-sm text-white/60">Comparaison sur la période (MAD).</p>
        <div class="mt-6 h-64">
            <canvas id="chartRevenueCost" role="img" aria-label="Revenus vs Coûts"></canvas>
        </div>
    </div>
</div>

<script>
(function() {
    const fontColor = 'rgba(226, 232, 240, 0.9)';
    const gridColor = 'rgba(255, 255, 255, 0.06)';
    const revenue = @json($d['chart_revenue']);
    const cost = @json($d['chart_cost']);
    if (document.getElementById('chartRevenueCost')) {
        new Chart(document.getElementById('chartRevenueCost'), {
            type: 'bar',
            data: {
                labels: ['Revenus', 'Coûts'],
                datasets: [{
                    label: 'MAD',
                    data: [revenue, cost],
                    backgroundColor: ['rgba(16, 185, 129, 0.5)', 'rgba(245, 158, 11, 0.5)'],
                    borderColor: ['rgba(16, 185, 129, 0.8)', 'rgba(245, 158, 11, 0.8)'],
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        ticks: { color: fontColor },
                        grid: { color: gridColor },
                    },
                    y: {
                        ticks: { color: fontColor },
                        grid: { color: gridColor },
                    },
                },
            },
        });
    }
})();
</script>
@endsection
