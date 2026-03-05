@extends('app.layouts.app')

@section('pageSubtitle')
Rapports – {{ $company->name }}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Rapports & analyses</h1>
            <p class="mt-1 text-sm text-slate-400">Revenus, utilisation flotte, réservations et coûts sur la période choisie.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @php
                $exportUrl = route('app.companies.reports.export-csv', $company) . '?from=' . $from->format('Y-m-d') . '&to=' . $to->format('Y-m-d');
            @endphp
            <a href="{{ $exportUrl }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white/85 hover:bg-white/10 transition no-underline">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Exporter CSV
            </a>
        </div>
    </header>

    {{-- Date range filter --}}
    <form method="get" action="{{ route('app.companies.reports.index', $company) }}" class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)] flex flex-wrap items-end gap-4">
        <div>
            <label for="from" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Du</label>
            <input type="date" id="from" name="from" value="{{ $from->format('Y-m-d') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
        </div>
        <div>
            <label for="to" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Au</label>
            <input type="date" id="to" name="to" value="{{ $to->format('Y-m-d') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
        </div>
        <button type="submit" class="rounded-xl bg-gradient-to-r from-[#2563EB] to-[#2563EB]/70 px-4 py-2.5 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(37,99,235,0.22)] hover:brightness-[1.03] transition">Actualiser</button>
    </form>

    @php
        $d = $data;
    @endphp

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <p class="text-sm font-semibold text-white/60">Revenus (période)</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-emerald-400">{{ number_format($d['revenue_total'], 0, ',', ' ') }} MAD</p>
            <p class="mt-1 text-xs text-slate-500">{{ $d['days_in_range'] }} jours</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <p class="text-sm font-semibold text-white/60">Taux d'utilisation flotte</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-[#93C5FD]">{{ $d['utilization_rate'] }} %</p>
            <p class="mt-1 text-xs text-slate-500">{{ $d['total_days_rented'] }} j loués / {{ $d['vehicle_count'] }} véhicules</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <p class="text-sm font-semibold text-white/60">Réservations</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-white/95">{{ $d['reservation_stats']['total'] }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $d['reservation_stats']['completed'] }} terminées</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <p class="text-sm font-semibold text-white/60">Coûts (financement + assurance)</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-amber-400">{{ number_format($d['cost_total'], 0, ',', ' ') }} MAD</p>
            <p class="mt-1 text-xs text-slate-500">Fin. {{ number_format($d['cost_financing'], 0, ',', ' ') }} · Ass. {{ number_format($d['cost_insurance'], 0, ',', ' ') }}</p>
        </div>
    </div>

    {{-- Revenue overview (monthly) + Revenue by vehicle --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <h2 class="text-base font-extrabold text-white/90">Revenus par mois</h2>
            <p class="mt-1 text-sm text-white/60">Répartition mensuelle (MAD).</p>
            <div class="mt-6 h-64">
                <canvas id="chartRevenueMonthly" role="img" aria-label="Revenus par mois"></canvas>
            </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <h2 class="text-base font-extrabold text-white/90">Revenus par véhicule (top 5)</h2>
            <p class="mt-1 text-sm text-white/60">Sur la période.</p>
            <div class="mt-4 h-64">
                <canvas id="chartRevenueByVehicle" role="img" aria-label="Revenus par véhicule"></canvas>
            </div>
        </div>
    </div>

    {{-- Most / Least rented + Reservation stats --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <h2 class="text-base font-extrabold text-white/90">Plus loués</h2>
            <p class="mt-1 text-sm text-white/60">Jours loués sur la période.</p>
            <ul class="mt-4 space-y-2">
                @forelse ($d['most_rented'] as $r)
                    <li class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-3 py-2">
                        <span class="font-medium text-white truncate">{{ $r['vehicle']->plate ?? '–' }} {{ $r['vehicle']->brand ?? '' }} {{ $r['vehicle']->model ?? '' }}</span>
                        <span class="text-sm text-[#93C5FD] font-semibold">{{ $r['days_rented'] }} j · {{ number_format($r['revenue'], 0, ',', ' ') }} MAD</span>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">Aucune location sur la période.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <h2 class="text-base font-extrabold text-white/90">Moins loués</h2>
            <p class="mt-1 text-sm text-white/60">À surveiller.</p>
            <ul class="mt-4 space-y-2">
                @forelse (array_slice($d['least_rented'], 0, 5) as $r)
                    <li class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-3 py-2">
                        <span class="font-medium text-white truncate">{{ $r['vehicle']->plate ?? '–' }} {{ $r['vehicle']->brand ?? '' }} {{ $r['vehicle']->model ?? '' }}</span>
                        <span class="text-sm text-slate-400">{{ $r['days_rented'] }} j</span>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">Aucun véhicule.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <h2 class="text-base font-extrabold text-white/90">Réservations par statut</h2>
            <p class="mt-1 text-sm text-white/60">Sur la période.</p>
            <ul class="mt-4 space-y-2">
                <li class="flex justify-between rounded-xl border border-white/10 bg-emerald-500/10 px-3 py-2"><span class="text-white/90">Terminées</span><span class="font-bold text-emerald-400">{{ $d['reservation_stats']['completed'] }}</span></li>
                <li class="flex justify-between rounded-xl border border-white/10 bg-white/5 px-3 py-2"><span class="text-white/90">En cours</span><span class="font-semibold text-white/80">{{ $d['reservation_stats']['in_progress'] }}</span></li>
                <li class="flex justify-between rounded-xl border border-white/10 bg-white/5 px-3 py-2"><span class="text-white/90">Confirmées</span><span class="font-semibold text-white/80">{{ $d['reservation_stats']['confirmed'] }}</span></li>
                <li class="flex justify-between rounded-xl border border-white/10 bg-white/5 px-3 py-2"><span class="text-white/90">Brouillons</span><span class="text-slate-400">{{ $d['reservation_stats']['draft'] }}</span></li>
                <li class="flex justify-between rounded-xl border border-white/10 bg-red-500/10 px-3 py-2"><span class="text-white/90">Annulées</span><span class="font-semibold text-red-400">{{ $d['reservation_stats']['cancelled'] }}</span></li>
            </ul>
        </div>
    </div>

    {{-- Cost overview detail --}}
    <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
        <h2 class="text-base font-extrabold text-white/90">Coûts (période)</h2>
        <p class="mt-1 text-sm text-white/60">Financement et assurance proratisés sur la plage de dates.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Financement (mensualités proratisées)</p>
                <p class="mt-1 text-2xl font-bold text-amber-400">{{ number_format($d['cost_financing'], 0, ',', ' ') }} MAD</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                <p class="text-sm text-slate-400">Assurance (annuelle proratisée)</p>
                <p class="mt-1 text-2xl font-bold text-amber-400">{{ number_format($d['cost_insurance'], 0, ',', ' ') }} MAD</p>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const fontColor = 'rgba(226, 232, 240, 0.9)';
    const gridColor = 'rgba(255, 255, 255, 0.06)';

    // Monthly revenue
    const monthlyLabels = @json(array_keys($d['revenue_by_month']));
    const monthlyData = @json(array_values($d['revenue_by_month']));
    if (document.getElementById('chartRevenueMonthly') && monthlyLabels.length) {
        new Chart(document.getElementById('chartRevenueMonthly'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Revenus (MAD)',
                    data: monthlyData,
                    backgroundColor: 'rgba(37, 99, 235, 0.4)',
                    borderColor: 'rgba(37, 99, 235, 0.8)',
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
                        ticks: { color: fontColor, maxRotation: 45 },
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

    // Revenue by vehicle (top 5)
    @php
        $topVehicles = array_slice($d['revenue_by_vehicle_list'], 0, 5);
        $vehicleLabels = array_map(fn ($x) => ($x['vehicle']->plate ?? '') . ' ' . ($x['vehicle']->model ?? ''), $topVehicles);
        $vehicleData = array_map(fn ($x) => $x['revenue'], $topVehicles);
    @endphp
    const vehicleLabels = @json($vehicleLabels);
    const vehicleData = @json($vehicleData);
    if (document.getElementById('chartRevenueByVehicle') && vehicleLabels.length) {
        new Chart(document.getElementById('chartRevenueByVehicle'), {
            type: 'bar',
            data: {
                labels: vehicleLabels,
                datasets: [{
                    label: 'MAD',
                    data: vehicleData,
                    backgroundColor: 'rgba(16, 185, 129, 0.4)',
                    borderColor: 'rgba(16, 185, 129, 0.8)',
                    borderWidth: 1,
                }],
            },
            options: {
                indexAxis: 'y',
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
