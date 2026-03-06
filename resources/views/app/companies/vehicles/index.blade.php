@extends('app.layouts.app')

@section('pageSubtitle')
Flotte – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Flotte</h1>
            <p class="mt-1 text-sm text-slate-400">Véhicules, conformité (assurance, vignette, visite technique) et tarifs.</p>
        </div>
        <a href="{{ route('app.companies.vehicles.create', $company) }}" class="glm-btn-primary inline-flex no-underline">Nouveau véhicule</a>
    </header>

    <form method="get" action="{{ route('app.companies.vehicles.index', $company) }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div>
            <label for="branch_id" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Agence</label>
            <select id="branch_id" name="branch_id" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" onchange="this.form.submit()">
                <option value="">Toutes</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Statut</label>
            <select id="status" name="status" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
            </select>
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="expiring_soon" value="1" {{ request('expiring_soon') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
            Expirant bientôt (assurance / vignette / visite)
        </label>
        @if (request()->hasAny(['expiring_soon', 'branch_id']))
            <a href="{{ route('app.companies.vehicles.index', $company) }}" class="text-sm text-slate-400 hover:text-white no-underline">Réinitialiser</a>
        @endif
    </form>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Plaque · Véhicule</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Agence</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Tarif jour</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Conformité</th>
                        <th class="w-0 px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($vehicles as $v)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('app.companies.vehicles.show', [$company, $v]) }}" class="shrink-0 no-underline flex items-center justify-center h-12 w-16 rounded-lg border border-white/10 bg-white/5 overflow-hidden">
                                        @if ($v->image_url)
                                            <img src="{{ $v->image_url }}" alt="" class="h-12 w-16 w-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                            <span class="hidden h-12 w-16 items-center justify-center text-slate-500"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg></span>
                                        @else
                                            <span class="text-slate-500"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg></span>
                                        @endif
                                    </a>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('app.companies.vehicles.show', [$company, $v]) }}" class="font-semibold text-white hover:text-[#93C5FD] no-underline">{{ $v->plate }}</a>
                                            @if ($v->isIncomplete())
                                                <span class="rounded px-2 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400" title="Complétez la fiche véhicule">Informations incomplètes</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-slate-400">{{ $v->brand }} {{ $v->model }} @if($v->year) ({{ $v->year }}) @endif</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $v->branch->name ?? '–' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $st = $v->status ?? 'available';
                                    $statusLabel = $st === 'available' ? 'Disponible' : ($st === 'maintenance' ? 'Maintenance' : 'Inactif');
                                    $statusClass = $st === 'available' ? 'bg-emerald-500/20 text-emerald-400' : ($st === 'maintenance' ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-500/20 text-slate-400');
                                @endphp
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $v->daily_price ? number_format($v->daily_price, 0, ',', ' ') . ' MAD' : '–' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $ins = $v->insuranceStatus();
                                        $vig = $v->vignetteStatus();
                                        $vis = $v->visiteStatus();
                                    @endphp
                                    @if ($ins === \App\Models\Vehicle::COMPLIANCE_EXPIRED)
                                        <span class="rounded px-2 py-0.5 text-xs font-medium bg-red-500/20 text-red-400" title="Assurance expirée">Assurance</span>
                                    @elseif ($ins === \App\Models\Vehicle::COMPLIANCE_EXPIRING)
                                        <span class="rounded px-2 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400" title="Assurance bientôt expirée">Assurance</span>
                                    @endif
                                    @if ($vig === \App\Models\Vehicle::COMPLIANCE_EXPIRED)
                                        <span class="rounded px-2 py-0.5 text-xs font-medium bg-red-500/20 text-red-400" title="Vignette expirée">Vignette</span>
                                    @elseif ($vig === \App\Models\Vehicle::COMPLIANCE_EXPIRING)
                                        <span class="rounded px-2 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400" title="Vignette bientôt expirée">Vignette</span>
                                    @endif
                                    @if ($vis === \App\Models\Vehicle::COMPLIANCE_EXPIRED)
                                        <span class="rounded px-2 py-0.5 text-xs font-medium bg-red-500/20 text-red-400" title="Visite technique expirée">Visite</span>
                                    @elseif ($vis === \App\Models\Vehicle::COMPLIANCE_EXPIRING)
                                        <span class="rounded px-2 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400" title="Visite technique bientôt expirée">Visite</span>
                                    @endif
                                    @if (in_array($ins, [\App\Models\Vehicle::COMPLIANCE_OK, \App\Models\Vehicle::COMPLIANCE_MISSING]) && in_array($vig, [\App\Models\Vehicle::COMPLIANCE_OK, \App\Models\Vehicle::COMPLIANCE_MISSING]) && in_array($vis, [\App\Models\Vehicle::COMPLIANCE_OK, \App\Models\Vehicle::COMPLIANCE_MISSING]))
                                        <span class="text-slate-500 text-xs">–</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('app.companies.vehicles.show', [$company, $v]) }}" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline">Voir</a>
                                    <a href="{{ route('app.companies.vehicles.duplicate', [$company, $v]) }}" class="inline-flex items-center gap-1 rounded-lg border border-white/20 bg-white/5 px-3 py-1.5 text-sm text-slate-300 hover:bg-white/10 hover:text-white no-underline" title="Dupliquer ce véhicule (nouvelle plaque)">Dupliquer</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                @if (request('expiring_soon'))
                                    Aucun véhicule avec conformité expirant sous 30 jours.
                                @else
                                    Aucun véhicule. <a href="{{ route('app.companies.vehicles.create', $company) }}" class="text-[#93C5FD] hover:underline">Ajouter un véhicule</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($vehicles->hasPages())
            <div class="border-t border-white/10 px-6 py-4">{{ $vehicles->links() }}</div>
        @endif
    </div>
</div>
@endsection
