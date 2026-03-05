@extends('app.layouts.app')

@section('pageSubtitle')
{{ $reservation->reference }} – {{ $company->name }}
@endsection

@section('content')
@php
    $statusLabels = [
        'draft' => 'Brouillon',
        'confirmed' => 'Confirmée',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
    ];
    $paymentLabels = ['unpaid' => 'Non payé', 'partial' => 'Partiel', 'paid' => 'Payé'];
    $r = $reservation;
@endphp
<div class="space-y-8 glm-fade-in" x-data="{ tab: (function(){ var p = new URLSearchParams(window.location.search).get('tab'); return p && ['summary','client','vehicle','payments','contract','inspections','logs'].includes(p) ? p : 'summary'; })() }">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200 flex items-center justify-between" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ session('success') }}
            <button type="button" @click="show = false" class="text-emerald-300 hover:text-white ml-2">×</button>
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    {{-- Header + actions --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.companies.reservations.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Réservations · {{ $company->name }}</a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-white">{{ $r->reference }}</h1>
                <span class="rounded-full px-3 py-1 text-sm font-semibold
                    {{ $r->status === 'draft' ? 'bg-slate-500/20 text-slate-300' : '' }}
                    {{ $r->status === 'confirmed' ? 'bg-blue-500/20 text-blue-300' : '' }}
                    {{ $r->status === 'in_progress' ? 'bg-amber-500/20 text-amber-400' : '' }}
                    {{ $r->status === 'completed' ? 'bg-emerald-500/20 text-emerald-300' : '' }}
                    {{ $r->status === 'cancelled' ? 'bg-red-500/20 text-red-400' : '' }}
                ">{{ $statusLabels[$r->status] ?? $r->status }}</span>
            </div>
            <p class="mt-1 text-sm text-slate-400">{{ $r->customer->name }} · {{ $r->vehicle->plate }} · {{ $r->start_at->format('d/m/Y') }} → {{ $r->end_at->format('d/m/Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($r->status === 'draft')
                <form action="{{ route('app.companies.reservations.confirm', [$company, $r]) }}" method="post" class="inline">@csrf <button type="submit" class="glm-btn-primary">Confirmer</button></form>
                @if (auth()->user()?->role !== 'agent')
                <form action="{{ route('app.companies.reservations.cancel', [$company, $r]) }}" method="post" class="inline" onsubmit="return confirm('Annuler cette réservation ?');">@csrf <button type="submit" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm font-medium text-red-300 hover:bg-red-500/20">Annuler</button></form>
                @endif
            @endif
            @if (in_array($r->status, ['confirmed', 'in_progress', 'completed']) && $r->payment_status !== 'paid')
                <form action="{{ route('app.companies.reservations.mark-paid', [$company, $r]) }}" method="post" class="inline">@csrf <button type="submit" class="glm-btn-secondary">Marquer payé</button></form>
            @endif
            @if ($r->status === 'confirmed')
                <form action="{{ route('app.companies.reservations.start', [$company, $r]) }}" method="post" class="inline">@csrf <button type="submit" class="glm-btn-primary">Démarrer la location</button></form>
                @if (auth()->user()?->role !== 'agent')
                <form action="{{ route('app.companies.reservations.cancel', [$company, $r]) }}" method="post" class="inline" onsubmit="return confirm('Annuler cette réservation ?');">@csrf <button type="submit" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm font-medium text-red-300 hover:bg-red-500/20">Annuler</button></form>
                @endif
            @endif
            @if ($r->status === 'in_progress')
                <form action="{{ route('app.companies.reservations.complete', [$company, $r]) }}" method="post" class="inline">@csrf <button type="submit" class="glm-btn-primary">Clôturer la location</button></form>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-white/10">
        <nav class="flex gap-1 flex-wrap" aria-label="Onglets">
            <button type="button" @click="tab = 'summary'" :class="tab === 'summary' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Résumé</button>
            <button type="button" @click="tab = 'client'" :class="tab === 'client' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Client</button>
            <button type="button" @click="tab = 'vehicle'" :class="tab === 'vehicle' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Véhicule</button>
            <button type="button" @click="tab = 'payments'" :class="tab === 'payments' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Paiements</button>
            <button type="button" @click="tab = 'contract'" :class="tab === 'contract' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Contrat</button>
            <button type="button" @click="tab = 'inspections'" :class="tab === 'inspections' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">État des lieux</button>
            <button type="button" @click="tab = 'logs'" :class="tab === 'logs' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Journal</button>
        </nav>
    </div>

    {{-- Tab: Summary --}}
    <div x-show="tab === 'summary'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Résumé</h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Référence</dt><dd class="mt-0.5 font-mono text-white">{{ $r->reference }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Statut</dt><dd class="mt-0.5"><span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                {{ $r->status === 'draft' ? 'bg-slate-500/20 text-slate-400' : '' }}
                {{ $r->status === 'confirmed' ? 'bg-blue-500/20 text-blue-300' : '' }}
                {{ $r->status === 'in_progress' ? 'bg-amber-500/20 text-amber-400' : '' }}
                {{ $r->status === 'completed' ? 'glm-badge-approved' : '' }}
                {{ $r->status === 'cancelled' ? 'bg-red-500/20 text-red-400' : '' }}
            ">{{ $statusLabels[$r->status] ?? $r->status }}</span></dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Période</dt><dd class="mt-0.5 text-slate-200">{{ $r->start_at->format('d/m/Y H:i') }} → {{ $r->end_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Jours</dt><dd class="mt-0.5 text-slate-200">{{ $r->days }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Montant total</dt><dd class="mt-0.5 text-white font-semibold">{{ number_format($r->total_price, 2, ',', ' ') }} MAD</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Paiement</dt><dd class="mt-0.5"><span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $r->payment_status === 'paid' ? 'glm-badge-approved' : 'bg-slate-500/20 text-slate-400' }}">{{ $paymentLabels[$r->payment_status] ?? $r->payment_status }}</span></dd></div>
            @if ($r->notes)
                <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Notes</dt><dd class="mt-0.5 text-slate-200 whitespace-pre-wrap">{{ $r->notes }}</dd></div>
            @endif
        </dl>
    </div>

    {{-- Tab: Client --}}
    <div x-show="tab === 'client'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Client</h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Nom</dt><dd class="mt-0.5 text-slate-200">{{ $r->customer->name }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">CIN</dt><dd class="mt-0.5 text-slate-200">{{ $r->customer->cin }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Téléphone</dt><dd class="mt-0.5 text-slate-200">{{ $r->customer->phone ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Email</dt><dd class="mt-0.5 text-slate-200">{{ $r->customer->email ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Ville</dt><dd class="mt-0.5 text-slate-200">{{ $r->customer->city ?? '–' }}</dd></div>
            @if ($r->customer->is_flagged)
                <div class="sm:col-span-2"><span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400">Client signalé</span></div>
            @endif
        </dl>
        <p class="mt-4"><a href="{{ route('app.companies.customers.show', [$company, $r->customer]) }}" class="text-[#93C5FD] hover:text-white no-underline">Voir la fiche client →</a></p>
    </div>

    {{-- Tab: Vehicle --}}
    <div x-show="tab === 'vehicle'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Véhicule</h2>
        @if ($r->vehicle->image_path)
            <div class="mb-4">
                <img src="{{ asset('storage/' . $r->vehicle->image_path) }}" alt="{{ $r->vehicle->plate }}" class="rounded-xl border border-white/10 object-cover max-h-56 w-full">
            </div>
        @endif
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Plaque</dt><dd class="mt-0.5 text-slate-200 font-mono">{{ $r->vehicle->plate }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Marque · Modèle</dt><dd class="mt-0.5 text-slate-200">{{ $r->vehicle->brand }} {{ $r->vehicle->model }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Agence</dt><dd class="mt-0.5 text-slate-200">{{ $r->vehicle->branch->name ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Tarif journalier</dt><dd class="mt-0.5 text-slate-200">{{ number_format($r->vehicle->daily_price ?? 0, 2, ',', ' ') }} MAD</dd></div>
        </dl>
        <p class="mt-4"><a href="{{ route('app.companies.vehicles.show', [$company, $r->vehicle]) }}" class="text-[#93C5FD] hover:text-white no-underline">Voir la fiche véhicule →</a></p>
    </div>

    {{-- Tab: Paiements --}}
    @php
        $paymentMethods = ['cash' => 'Espèces', 'virement' => 'Virement', 'TPE' => 'TPE', 'cheque' => 'Chèque'];
        $paymentTypes = ['deposit' => 'Caution', 'rental' => 'Location', 'fee' => 'Frais', 'refund' => 'Remboursement'];
    @endphp
    <div x-show="tab === 'payments'" x-cloak class="glm-card-static p-6 space-y-6">
        <h2 class="text-lg font-semibold text-white">Paiements</h2>
        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Total location</dt><dd class="mt-0.5 text-white font-semibold">{{ number_format($r->total_price, 2, ',', ' ') }} MAD</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Caution (véhicule)</dt><dd class="mt-0.5 text-slate-200">{{ number_format($r->deposit_expected, 2, ',', ' ') }} MAD</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Encaissé</dt><dd class="mt-0.5 text-white">{{ number_format($r->paid_amount, 2, ',', ' ') }} MAD</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Reste à payer</dt><dd class="mt-0.5 font-semibold {{ $r->remaining_amount > 0 ? 'text-amber-400' : 'text-emerald-400' }}">{{ number_format($r->remaining_amount, 2, ',', ' ') }} MAD</dd></div>
        </dl>
        <p>Statut : <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $r->payment_status === 'paid' ? 'glm-badge-approved' : ($r->payment_status === 'partial' ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-500/20 text-slate-400') }}">{{ $paymentLabels[$r->payment_status] ?? $r->payment_status }}</span></p>

        <div class="flex flex-wrap gap-2">
            <button type="button" @click="$refs.paymentModal.classList.remove('hidden')" class="glm-btn-primary">Ajouter un paiement</button>
            @if ($r->remaining_amount > 0 && !in_array($r->status, ['cancelled'], true))
                <form action="{{ route('app.companies.reservations.mark-paid', [$company, $r]) }}" method="post" class="inline">@csrf <button type="submit" class="glm-btn-secondary">Marquer comme payé (solde)</button></form>
            @endif
            @if ($r->deposit_expected > 0 && !in_array($r->status, ['cancelled'], true))
                <form action="{{ route('app.companies.reservations.refund-deposit', [$company, $r]) }}" method="post" class="inline" onsubmit="return confirm('Enregistrer le remboursement de la caution ?');">@csrf <button type="submit" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-sm font-medium text-emerald-300 hover:bg-emerald-500/20">Rembourser la caution</button></form>
            @endif
            <a href="{{ route('app.companies.reservations.receipt', [$company, $r]) }}" target="_blank" rel="noopener" class="glm-btn-secondary no-underline">Imprimer le reçu</a>
        </div>

        {{-- Payment list --}}
        <div>
            <h3 class="text-sm font-semibold text-white mb-2">Historique des paiements</h3>
            @if ($r->payments->isEmpty())
                <p class="text-slate-400 text-sm">Aucun paiement enregistré.</p>
            @else
                <div class="overflow-hidden rounded-xl border border-white/10">
                    <table class="min-w-full border-collapse text-sm">
                        <thead class="bg-white/5"><tr><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Date</th><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Type</th><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Moyen</th><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Réf.</th><th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-400">Montant</th><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Reçu</th></tr></thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach ($r->payments->sortByDesc('paid_at') as $p)
                                <tr>
                                    <td class="px-4 py-3 text-slate-200">{{ $p->paid_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs {{ $p->type === 'refund' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300' }}">{{ $paymentTypes[$p->type] ?? $p->type }}</span></td>
                                    <td class="px-4 py-3 text-slate-300">{{ $paymentMethods[$p->method] ?? $p->method }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $p->reference ?? '–' }}</td>
                                    <td class="px-4 py-3 text-right font-medium {{ $p->isRefund() ? 'text-emerald-400' : 'text-white' }}">{{ $p->isRefund() ? '-' : '' }}{{ number_format($p->amount, 2, ',', ' ') }} MAD</td>
                                    <td class="px-4 py-3">@if($p->receipt_path)<a href="{{ asset('storage/' . $p->receipt_path) }}" target="_blank" class="text-[#93C5FD] hover:underline">Voir</a>@else – @endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Add payment modal --}}
        <div x-ref="paymentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click="$refs.paymentModal.classList.add('hidden')" @keydown.escape.window="$refs.paymentModal.classList.add('hidden')">
            <div class="glm-card-static max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                <h3 class="text-lg font-semibold text-white mb-4">Ajouter un paiement</h3>
                <form action="{{ route('app.companies.reservations.payments.store', [$company, $r]) }}" method="post" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="pay_amount" class="mb-1 block text-sm font-medium text-slate-300">Montant (MAD) *</label>
                            <input type="number" id="pay_amount" name="amount" required min="0.01" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                        </div>
                        <div>
                            <label for="pay_paid_at" class="mb-1 block text-sm font-medium text-slate-300">Date *</label>
                            <input type="date" id="pay_paid_at" name="paid_at" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                        </div>
                        <div>
                            <label for="pay_method" class="mb-1 block text-sm font-medium text-slate-300">Moyen de paiement *</label>
                            <select id="pay_method" name="method" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                                @foreach ($paymentMethods as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="pay_type" class="mb-1 block text-sm font-medium text-slate-300">Type *</label>
                            <select id="pay_type" name="type" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                                @foreach ($paymentTypes as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="pay_reference" class="mb-1 block text-sm font-medium text-slate-300">Référence</label>
                        <input type="text" id="pay_reference" name="reference" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="N° chèque, virement…">
                    </div>
                    <div>
                        <label for="pay_note" class="mb-1 block text-sm font-medium text-slate-300">Note</label>
                        <textarea id="pay_note" name="note" rows="2" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></textarea>
                    </div>
                    <div>
                        <label for="pay_receipt" class="mb-1 block text-sm font-medium text-slate-300">Justificatif (PDF / image)</label>
                        <input type="file" id="pay_receipt" name="receipt" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="glm-btn-primary">Enregistrer</button>
                        <button type="button" @click="$refs.paymentModal.classList.add('hidden')" class="glm-btn-secondary">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tab: Contrat --}}
    @php
        $rc = $r->reservationContract;
        $contractStatusLabels = ['draft' => 'Brouillon', 'generated' => 'Généré', 'signed' => 'Signé'];
        $hasGenerated = $r->contract_status === 'generated' || ($rc && $rc->status === 'generated');
        $displayContractStatus = $r->contract_status === 'signed' ? 'signed' : ($hasGenerated ? 'generated' : ($r->contract_status ?: 'draft'));
        $defaultId = $company->default_contract_template_id;
    @endphp
    <div x-show="tab === 'contract'" x-cloak class="glm-card-static p-6 space-y-6" x-data="contractTab({
        hasSigned: @json((bool) $r->contract_signed_path),
        replaceConfirm: false
    })">
        <h2 class="text-lg font-semibold text-white">Contrat</h2>

        <div class="flex flex-wrap items-center gap-3">
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                {{ $displayContractStatus === 'draft' ? 'bg-slate-500/20 text-slate-400' : '' }}
                {{ $displayContractStatus === 'generated' ? 'glm-badge-approved' : '' }}
                {{ $displayContractStatus === 'signed' ? 'bg-emerald-500/20 text-emerald-300' : '' }}
            ">{{ $contractStatusLabels[$displayContractStatus] ?? $displayContractStatus }}</span>
            @if ($rc && $rc->generated_at)
                <span class="text-xs text-slate-500">Généré le {{ $rc->generated_at->format('d/m/Y H:i') }}</span>
            @endif
            @if ($r->contract_signed_at)
                <span class="text-xs text-slate-500">Signé le {{ $r->contract_signed_at->format('d/m/Y H:i') }}</span>
            @endif
        </div>

        <div>
            <label for="contract_template_id" class="mb-1 block text-sm font-medium text-slate-300">Modèle de contrat</label>
            <select id="contract_template_id" name="template_id" data-preview-url="{{ route('app.companies.reservations.contract-preview', [$company, $r]) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white w-full max-w-md" onchange="var u = this.dataset.previewUrl; document.getElementById('contract-preview-frame').src = u + (this.value ? '?template_id=' + this.value : '');">
                <option value="" {{ !$defaultId ? 'selected' : '' }}>— Modèle par défaut ({{ $company->defaultContractTemplate?->name ?? 'non défini' }}) —</option>
                @foreach ($contractTemplates as $t)
                    <option value="{{ $t->id }}" {{ $defaultId == $t->id ? 'selected' : '' }}>{{ $t->name }} (v{{ $t->version }})</option>
                @endforeach
                @foreach ($globalTemplates as $t)
                    <option value="{{ $t->id }}" {{ $defaultId == $t->id ? 'selected' : '' }}>{{ $t->name }} (global, v{{ $t->version }})</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Changer le modèle met à jour l’aperçu. « Générer » enregistre un instantané figé.</p>
        </div>

        <div>
            <p class="mb-2 text-sm font-medium text-slate-300">Aperçu</p>
            <div class="rounded-xl border border-white/10 bg-white overflow-hidden max-h-[85vh] flex flex-col">
                <iframe id="contract-preview-frame" src="{{ route('app.companies.reservations.contract-preview', [$company, $r]) }}{{ $defaultId ? '?template_id=' . $defaultId : '' }}" class="w-full flex-1 min-h-[420px]" style="height: 65vh;" title="Aperçu du contrat"></iframe>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <form action="{{ route('app.companies.reservations.contract-generate', [$company, $r]) }}" method="post" class="inline">
                @csrf
                <input type="hidden" name="template_id" value="{{ $defaultId ?? '' }}">
                <button type="submit" class="glm-btn-primary" onclick="this.form.querySelector('input[name=template_id]').value = document.getElementById('contract_template_id').value;">{{ $hasGenerated ? 'Régénérer' : 'Générer' }}</button>
            </form>
            @if ($hasGenerated)
                <a href="{{ route('app.companies.reservations.contract-print', [$company, $r]) }}" target="_blank" rel="noopener" class="glm-btn-secondary no-underline">Télécharger le contrat généré</a>
            @endif
            <a href="{{ route('app.companies.reservations.contract-print', [$company, $r]) }}" target="_blank" rel="noopener" class="glm-btn-secondary no-underline">Aperçu</a>
            <a href="{{ route('app.companies.reservations.contract-print', [$company, $r]) }}?auto=print" target="_blank" rel="noopener" class="glm-btn-secondary no-underline">Imprimer / PDF</a>
        </div>

        {{-- Signed contract upload & download --}}
        <div class="border-t border-white/10 pt-6 mt-6">
            <h3 class="text-base font-semibold text-white mb-3">Contrat signé (upload manuel)</h3>
            @if ($r->contract_signed_path)
                <div class="rounded-xl border border-white/10 bg-white/5 p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </span>
                        <div>
                            <p class="text-sm font-medium text-white">contrat-signe-{{ $r->reference }}.pdf</p>
                            <p class="text-xs text-slate-500">Enregistré le {{ $r->contract_signed_at->format('d/m/Y H:i') }}</p>
                            @if ($r->contract_signed_notes)
                                <p class="text-xs text-slate-400 mt-1">{{ $r->contract_signed_notes }}</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('app.companies.reservations.contract-signed.download', [$company, $r]) }}" class="glm-btn-secondary no-underline text-sm py-2">Télécharger contrat signé</a>
                </div>
            @endif

            <form id="contract-signed-form" action="{{ route('app.companies.reservations.contract-signed.store', [$company, $r]) }}" method="post" enctype="multipart/form-data" @submit="if (hasSigned && !replaceConfirm) { $event.preventDefault(); $refs.replaceModal.classList.remove('hidden'); }">
                @csrf
                <input type="hidden" name="replace_confirm" value="0" x-ref="replaceInput">
                <div class="rounded-xl border border-dashed border-white/20 bg-white/5 p-6 mb-4 transition-colors" @dragover.prevent="$refs.signedFile && $refs.signedFile.classList.add('ring-2', 'ring-[#2563EB]')" @dragleave.prevent="$refs.signedFile && $refs.signedFile.classList.remove('ring-2', 'ring-[#2563EB]')" @drop.prevent="const f = $event.dataTransfer.files[0]; if (f && f.type === 'application/pdf') { $refs.signedFile.files = $event.dataTransfer.files; $refs.signedLabel.textContent = f.name }">
                    <label for="signed_pdf" class="block cursor-pointer">
                        <input type="file" id="signed_pdf" name="signed_pdf" accept=".pdf,application/pdf" class="sr-only" x-ref="signedFile" @change="if ($el.files[0]) { $refs.signedLabel.textContent = $el.files[0].name }">
                        <span class="flex flex-col items-center justify-center gap-2 text-slate-400 hover:text-white" x-ref="signedLabel">Glissez un PDF ici ou cliquez pour choisir — PDF uniquement, max. 10 Mo</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label for="contract_signed_notes" class="mb-1 block text-sm font-medium text-slate-300">Notes (optionnel)</label>
                    <textarea id="contract_signed_notes" name="contract_signed_notes" rows="2" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Ex. Signé en agence le ...">{{ old('contract_signed_notes', $r->contract_signed_notes) }}</textarea>
                </div>
                <button type="submit" class="glm-btn-primary">Enregistrer</button>
            </form>

            {{-- Replace confirmation modal --}}
            <div x-ref="replaceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-black/60" @click="$refs.replaceModal.classList.add('hidden')"></div>
                <div class="glm-dark-bg relative z-10 w-full max-w-sm rounded-2xl border border-white/10 bg-slate-800 p-6 shadow-xl">
                    <p class="text-sm text-slate-200 mb-4">Un contrat signé existe déjà. Remplacer par le nouveau fichier ?</p>
                    <div class="flex gap-3 justify-end">
                        <button type="button" class="glm-btn-secondary" @click="$refs.replaceModal.classList.add('hidden')">Annuler</button>
                        <button type="button" class="glm-btn-primary" @click="replaceConfirm = true; $refs.replaceInput.value = '1'; $refs.replaceModal.classList.add('hidden'); document.getElementById('contract-signed-form').submit();">Remplacer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab: État des lieux --}}
    @php
        $inspectionOut = $r->inspectionOut;
        $inspectionIn = $r->inspectionIn;
        $fuelLevels = \App\Models\ReservationInspection::FUEL_LEVELS;
        $depositStatusLabels = ['pending' => 'En attente', 'refunded' => 'Remboursé', 'retained' => 'Retenu', 'partial' => 'Partiel'];
    @endphp
    <div x-show="tab === 'inspections'" x-cloak class="space-y-8">
        {{-- Départ (out) --}}
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Départ (état des lieux sortie)</h2>
            @if ($inspectionOut)
                <dl class="grid gap-3 sm:grid-cols-2 mb-6 text-sm">
                    <div><dt class="text-slate-500">Date / heure</dt><dd class="text-slate-200">{{ $inspectionOut->inspected_at ? $inspectionOut->inspected_at->format('d/m/Y H:i') : '–' }}</dd></div>
                    <div><dt class="text-slate-500">Kilométrage</dt><dd class="text-slate-200">{{ $inspectionOut?->mileage ?? '–' }}</dd></div>
                    <div><dt class="text-slate-500">Niveau carburant</dt><dd class="text-slate-200">{{ $fuelLevels[$inspectionOut->fuel_level ?? ''] ?? $inspectionOut?->fuel_level ?? '–' }}</dd></div>
                    @if ($inspectionOut?->notes)<div class="sm:col-span-2"><dt class="text-slate-500">Notes</dt><dd class="text-slate-200 whitespace-pre-wrap">{{ $inspectionOut->notes }}</dd></div>@endif
                    @if (!empty($inspectionOut->damage_checklist))
                        <div class="sm:col-span-2"><dt class="text-slate-500">Dégâts constatés</dt>
                            <dd class="mt-1"><ul class="list-disc list-inside text-slate-200 space-y-0.5">
                                @foreach ($inspectionOut->damage_checklist as $d)
                                    <li><strong>{{ $d['area'] ?? '–' }}</strong>: {{ $d['description'] ?? '' }}</li>
                                @endforeach
                            </ul></dd></div>
                    @endif
                    @if ($inspectionOut->photos->isNotEmpty())
                        <div class="sm:col-span-2"><dt class="text-slate-500">Photos</dt>
                            <dd class="mt-2 flex flex-wrap gap-2">
                                @foreach ($inspectionOut->photos as $ph)
                                    <div class="relative group">
                                        <a href="{{ asset('storage/' . $ph->path) }}" target="_blank" class="block"><img src="{{ asset('storage/' . $ph->path) }}" alt="" class="h-20 w-20 object-cover rounded-lg border border-white/10"></a>
                                        <form action="{{ route('app.companies.reservations.inspection-photos.destroy', [$company, $r, $ph]) }}" method="post" class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition">@csrf @method('DELETE')<button type="submit" class="rounded bg-red-500/90 text-white p-0.5" title="Supprimer">×</button></form>
                                    </div>
                                @endforeach
                            </dd></div>
                    @endif
                </dl>
            @endif
            <form action="{{ route('app.companies.reservations.inspections.store', [$company, $r]) }}" method="post" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="type" value="out">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="out_inspected_at" class="mb-1 block text-sm font-medium text-slate-300">Date / heure</label>
                        <input type="datetime-local" id="out_inspected_at" name="inspected_at" value="{{ $inspectionOut?->inspected_at?->format('Y-m-d\TH:i') ?? '' }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label for="out_mileage" class="mb-1 block text-sm font-medium text-slate-300">Kilométrage</label>
                        <input type="number" id="out_mileage" name="mileage" value="{{ $inspectionOut?->mileage ?? '' }}" min="0" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label for="out_fuel_level" class="mb-1 block text-sm font-medium text-slate-300">Niveau carburant</label>
                        <select id="out_fuel_level" name="fuel_level" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                            <option value="">–</option>
                            @foreach ($fuelLevels as $k => $v)
                                <option value="{{ $k }}" {{ ($inspectionOut?->fuel_level ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="damage-checklist-out">
                    <label class="mb-2 block text-sm font-medium text-slate-300">Dégâts constatés (zone + description)</label>
                    @php $outDamages = $inspectionOut?->damage_checklist ?? []; if (empty($outDamages)) { $outDamages = [['area' => '', 'description' => '']]; } else { $outDamages[] = ['area' => '', 'description' => '']; } @endphp
                    @foreach ($outDamages as $idx => $d)
                        <div class="flex gap-2 mb-2 damage-row">
                            <input type="text" name="damage_checklist[{{ $idx }}][area]" placeholder="Zone (ex: portière avant gauche)" class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" value="{{ $d['area'] ?? '' }}">
                            <input type="text" name="damage_checklist[{{ $idx }}][description]" placeholder="Description" class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" value="{{ $d['description'] ?? '' }}">
                        </div>
                    @endforeach
                    <button type="button" id="add-damage-row-out" class="text-sm text-[#93C5FD] hover:text-white">+ Ajouter une ligne</button>
                </div>
                <script>
                (function(){ var container = document.getElementById('damage-checklist-out'); var btn = document.getElementById('add-damage-row-out'); if (!btn) return; btn.addEventListener('click', function(){ var rows = container.querySelectorAll('.damage-row'); var idx = rows.length; var last = rows[rows.length-1]; var clone = last.cloneNode(true); clone.querySelectorAll('input').forEach(function(inp){ inp.value = ''; inp.name = inp.name.replace(/^damage_checklist\[\d+\]/, 'damage_checklist[' + idx + ']'); }); last.parentNode.insertBefore(clone, btn); }); })();
                </script>
                <div>
                    <label for="out_notes" class="mb-1 block text-sm font-medium text-slate-300">Notes</label>
                    <textarea id="out_notes" name="notes" rows="3" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Remarques…">{{ $inspectionOut?->notes ?? '' }}</textarea>
                </div>
                <div>
                    <label for="out_photos" class="mb-1 block text-sm font-medium text-slate-300">Photos</label>
                    <input type="file" id="out_photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
                </div>
                <button type="submit" class="glm-btn-primary">Enregistrer le départ</button>
            </form>
        </div>

        {{-- Retour (in) --}}
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Retour (état des lieux entrée)</h2>
            @if ($inspectionIn)
                <dl class="grid gap-3 sm:grid-cols-2 mb-6 text-sm">
                    <div><dt class="text-slate-500">Date / heure</dt><dd class="text-slate-200">{{ $inspectionIn->inspected_at ? $inspectionIn->inspected_at->format('d/m/Y H:i') : '–' }}</dd></div>
                    <div><dt class="text-slate-500">Kilométrage</dt><dd class="text-slate-200">{{ $inspectionIn?->mileage ?? '–' }}</dd></div>
                    <div><dt class="text-slate-500">Niveau carburant</dt><dd class="text-slate-200">{{ $fuelLevels[$inspectionIn->fuel_level ?? ''] ?? $inspectionIn?->fuel_level ?? '–' }}</dd></div>
                    <div><dt class="text-slate-500">Frais supplémentaires</dt><dd class="text-slate-200">{{ $inspectionIn->extra_fees !== null ? number_format($inspectionIn->extra_fees, 2, ',', ' ') . ' MAD' : '–' }}</dd></div>
                    <div><dt class="text-slate-500">Caution</dt><dd class="text-slate-200">{{ $depositStatusLabels[$inspectionIn->deposit_refund_status ?? ''] ?? $inspectionIn?->deposit_refund_status ?? '–' }}</dd></div>
                    @if ($inspectionIn?->new_damages)<div class="sm:col-span-2"><dt class="text-slate-500">Nouveaux dégâts</dt><dd class="text-slate-200 whitespace-pre-wrap">{{ $inspectionIn->new_damages }}</dd></div>@endif
                    @if ($inspectionIn?->notes)<div class="sm:col-span-2"><dt class="text-slate-500">Notes</dt><dd class="text-slate-200 whitespace-pre-wrap">{{ $inspectionIn->notes }}</dd></div>@endif
                    @if ($inspectionIn->photos->isNotEmpty())
                        <div class="sm:col-span-2"><dt class="text-slate-500">Photos</dt>
                            <dd class="mt-2 flex flex-wrap gap-2">
                                @foreach ($inspectionIn->photos as $ph)
                                    <div class="relative group">
                                        <a href="{{ asset('storage/' . $ph->path) }}" target="_blank" class="block"><img src="{{ asset('storage/' . $ph->path) }}" alt="" class="h-20 w-20 object-cover rounded-lg border border-white/10"></a>
                                        <form action="{{ route('app.companies.reservations.inspection-photos.destroy', [$company, $r, $ph]) }}" method="post" class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition">@csrf @method('DELETE')<button type="submit" class="rounded bg-red-500/90 text-white p-0.5" title="Supprimer">×</button></form>
                                    </div>
                                @endforeach
                            </dd></div>
                    @endif
                </dl>
            @endif
            <form action="{{ route('app.companies.reservations.inspections.store', [$company, $r]) }}" method="post" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="type" value="in">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="in_inspected_at" class="mb-1 block text-sm font-medium text-slate-300">Date / heure</label>
                        <input type="datetime-local" id="in_inspected_at" name="inspected_at" value="{{ $inspectionIn?->inspected_at?->format('Y-m-d\TH:i') ?? '' }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label for="in_mileage" class="mb-1 block text-sm font-medium text-slate-300">Kilométrage</label>
                        <input type="number" id="in_mileage" name="mileage" value="{{ $inspectionIn?->mileage ?? '' }}" min="0" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label for="in_fuel_level" class="mb-1 block text-sm font-medium text-slate-300">Niveau carburant</label>
                        <select id="in_fuel_level" name="fuel_level" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                            <option value="">–</option>
                            @foreach ($fuelLevels as $k => $v)
                                <option value="{{ $k }}" {{ ($inspectionIn?->fuel_level ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="in_extra_fees" class="mb-1 block text-sm font-medium text-slate-300">Frais supplémentaires (MAD)</label>
                        <input type="number" id="in_extra_fees" name="extra_fees" value="{{ $inspectionIn?->extra_fees ?? '' }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label for="in_deposit_refund_status" class="mb-1 block text-sm font-medium text-slate-300">Caution</label>
                        <select id="in_deposit_refund_status" name="deposit_refund_status" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                            <option value="">–</option>
                            @foreach ($depositStatusLabels as $k => $v)
                                <option value="{{ $k }}" {{ ($inspectionIn?->deposit_refund_status ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="in_new_damages" class="mb-1 block text-sm font-medium text-slate-300">Nouveaux dégâts</label>
                    <textarea id="in_new_damages" name="new_damages" rows="3" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Dégâts constatés au retour…">{{ $inspectionIn?->new_damages ?? '' }}</textarea>
                </div>
                <div>
                    <label for="in_notes" class="mb-1 block text-sm font-medium text-slate-300">Notes</label>
                    <textarea id="in_notes" name="notes" rows="2" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">{{ $inspectionIn?->notes ?? '' }}</textarea>
                </div>
                <div>
                    <label for="in_photos" class="mb-1 block text-sm font-medium text-slate-300">Photos</label>
                    <input type="file" id="in_photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
                </div>
                <button type="submit" class="glm-btn-primary">Enregistrer le retour</button>
            </form>
        </div>

        {{-- Link to damages list (moved from sidebar into inspection flow) --}}
        <p class="text-sm text-slate-400">
            <a href="{{ route('app.companies.damages.index', $company) }}" class="text-[#93C5FD] hover:text-white no-underline">Voir la liste des dégâts →</a>
        </p>
    </div>

    {{-- Tab: Logs --}}
    <div x-show="tab === 'logs'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Journal</h2>
        <ul class="space-y-2 text-sm text-slate-300">
            <li>Créée le {{ $r->created_at->format('d/m/Y H:i') }}</li>
            @if ($r->confirmed_at) <li>Confirmée le {{ $r->confirmed_at->format('d/m/Y H:i') }}</li> @endif
            @if ($r->started_at) <li>Location démarrée le {{ $r->started_at->format('d/m/Y H:i') }}</li> @endif
            @if ($r->completed_at) <li>Location terminée le {{ $r->completed_at->format('d/m/Y H:i') }}</li> @endif
            @if ($r->cancelled_at) <li>Annulée le {{ $r->cancelled_at->format('d/m/Y H:i') }}</li> @endif
        </ul>
        <p class="mt-4 text-slate-500 text-xs">Journal détaillé des actions (à venir).</p>
    </div>
</div>

<style>[x-cloak]{display:none!important}</style>

@push('scripts')
<script>
document.addEventListener('alpine:init', function() {
    window.contractTab = function(config) {
        return { hasSigned: !!(config && config.hasSigned), replaceConfirm: !!(config && config.replaceConfirm) };
    };
});
</script>
@endpush
@endsection
