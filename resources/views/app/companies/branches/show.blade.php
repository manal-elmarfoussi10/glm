@extends('app.layouts.app')

@section('pageSubtitle')
{{ $branch->name }} – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.branches.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Agences · {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">{{ $branch->name }}</h1>
            <p class="mt-1 text-sm text-slate-400">
                @if ($branch->city){{ $branch->city }}@endif
                @if ($branch->address) · {{ Str::limit($branch->address, 50) }}@endif
                @if ($branch->phone) · {{ $branch->phone }}@endif
            </p>
        </div>
        <a href="{{ route('app.companies.branches.edit', [$company, $branch]) }}" class="glm-btn-primary inline-flex no-underline">Modifier l'agence</a>
    </header>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Véhicules --}}
        <div class="glm-card-static p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-semibold text-white">Véhicules ({{ $branch->vehicles->count() }})</h2>
                <a href="{{ route('app.companies.vehicles.create', $company) }}?branch_id={{ $branch->id }}" class="text-sm font-bold text-[#93C5FD] hover:text-white transition">+ Ajouter</a>
            </div>
            @if ($branch->vehicles->isEmpty())
                <p class="text-sm text-slate-400">Aucun véhicule dans cette agence.</p>
            @else
                <ul class="divide-y divide-white/10">
                    @foreach ($branch->vehicles as $v)
                        <li class="py-3 first:pt-0">
                            <a href="{{ route('app.companies.vehicles.show', [$company, $v]) }}" class="font-medium text-white hover:text-[#93C5FD] transition">{{ $v->plate }}</a>
                            <span class="text-slate-400"> · {{ $v->brand }} {{ $v->model }}</span>
                            @if ($v->status ?? null)
                                @php
                                    $statusLabel = match($v->status) {
                                        'available' => 'Disponible',
                                        'maintenance' => 'Maintenance',
                                        'inactive' => 'Inactif',
                                        default => $v->status,
                                    };
                                    $statusClass = match($v->status) {
                                        'available' => 'bg-emerald-500/20 text-emerald-400',
                                        'maintenance' => 'bg-amber-500/20 text-amber-400',
                                        'inactive' => 'bg-slate-500/20 text-slate-400',
                                        default => 'bg-white/10 text-white/70',
                                    };
                                @endphp
                                <span class="ml-2 rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Utilisateurs --}}
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Utilisateurs ({{ $branch->users->count() }})</h2>
            @if ($branch->users->isEmpty())
                <p class="text-sm text-slate-400">Aucun utilisateur assigné à cette agence.</p>
            @else
                <ul class="divide-y divide-white/10">
                    @foreach ($branch->users as $u)
                        <li class="py-3 first:pt-0 flex items-center justify-between gap-2">
                            <div>
                                <span class="font-medium text-white">{{ $u->name }}</span>
                                <span class="text-slate-400"> · {{ $u->email }}</span>
                                @if ($branch->manager_id == $u->id)
                                    <span class="ml-2 rounded-full px-2 py-0.5 text-xs font-medium bg-[#2563EB]/20 text-[#93C5FD]">Responsable</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
