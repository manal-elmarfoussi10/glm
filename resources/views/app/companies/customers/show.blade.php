@extends('app.layouts.app')

@section('pageSubtitle')
{{ $customer->name }} – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in" x-data="{ tab: 'profil' }">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.companies.customers.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Clients · {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">{{ $customer->name }}</h1>
            <p class="mt-1 text-sm text-slate-400">CIN {{ $customer->cin }}@if($customer->city) · {{ $customer->city }}@endif</p>
        </div>
        <a href="{{ route('app.companies.customers.edit', [$company, $customer]) }}" class="glm-btn-primary inline-flex no-underline">Modifier</a>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-white/10">
        <nav class="flex gap-1" aria-label="Onglets">
            <button type="button" @click="tab = 'profil'" :class="tab === 'profil' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Profil</button>
            <button type="button" @click="tab = 'documents'" :class="tab === 'documents' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Documents</button>
            <button type="button" @click="tab = 'reservations'" :class="tab === 'reservations' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Réservations</button>
            <button type="button" @click="tab = 'notes'" :class="tab === 'notes' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Notes internes</button>
        </nav>
    </div>

    {{-- Tab: Profil --}}
    <div x-show="tab === 'profil'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Identité & permis</h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Nom complet</dt><dd class="mt-0.5 text-slate-200">{{ $customer->name }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">CIN</dt><dd class="mt-0.5 text-slate-200">{{ $customer->cin }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Téléphone</dt><dd class="mt-0.5 text-slate-200">{{ $customer->phone ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Email</dt><dd class="mt-0.5 text-slate-200">{{ $customer->email ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Ville</dt><dd class="mt-0.5 text-slate-200">{{ $customer->city ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Adresse</dt><dd class="mt-0.5 text-slate-200">{{ $customer->address ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">N° permis</dt><dd class="mt-0.5 text-slate-200">{{ $customer->driving_license_number ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Expiration permis</dt><dd class="mt-0.5 text-slate-200">{{ $customer->driving_license_expiry ? $customer->driving_license_expiry->format('d/m/Y') : '–' }}</dd></div>
        </dl>
    </div>

    {{-- Tab: Documents --}}
    <div x-show="tab === 'documents'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Documents (CIN & permis)</h2>
        <ul class="space-y-3">
            @if ($customer->cin_front_path)
                <li>
                    <a href="{{ asset('storage/' . $customer->cin_front_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-[#93C5FD] hover:text-white no-underline">
                        <span>CIN recto</span>
                        <span class="text-slate-500 text-sm">({{ basename($customer->cin_front_path) }})</span>
                    </a>
                </li>
            @else
                <li class="text-slate-500 text-sm">CIN recto : non déposé</li>
            @endif
            @if ($customer->cin_back_path)
                <li>
                    <a href="{{ asset('storage/' . $customer->cin_back_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-[#93C5FD] hover:text-white no-underline">
                        <span>CIN verso</span>
                        <span class="text-slate-500 text-sm">({{ basename($customer->cin_back_path) }})</span>
                    </a>
                </li>
            @else
                <li class="text-slate-500 text-sm">CIN verso : non déposé</li>
            @endif
            @if ($customer->license_document_path)
                <li>
                    <a href="{{ asset('storage/' . $customer->license_document_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-[#93C5FD] hover:text-white no-underline">
                        <span>Permis</span>
                        <span class="text-slate-500 text-sm">({{ basename($customer->license_document_path) }})</span>
                    </a>
                </li>
            @else
                <li class="text-slate-500 text-sm">Permis : non déposé</li>
            @endif
        </ul>
    </div>

    {{-- Tab: Réservations --}}
    <div x-show="tab === 'reservations'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Réservations</h2>
        <p class="text-slate-400 text-sm mb-4">Réservations de ce client.</p>
        <a href="{{ route('app.companies.reservations.index', ['company' => $company, 'customer_id' => $customer->id]) }}" class="glm-btn-primary no-underline">Voir les réservations</a>
    </div>

    {{-- Tab: Notes internes --}}
    <div x-show="tab === 'notes'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Notes internes</h2>
        @if ($customer->is_flagged)
            <p class="mb-4"><span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400">Client signalé</span></p>
        @endif
        <div class="rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-slate-200 whitespace-pre-wrap">{{ $customer->internal_notes ?: 'Aucune note interne.' }}</div>
    </div>
</div>

<style>[x-cloak]{display:none!important}</style>
@endsection
