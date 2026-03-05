@extends('app.layouts.app')

@section('pageSubtitle')
Détail et actions
@endsection

@section('content')
<div class="space-y-8 glm-fade-in" x-data>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.registration-requests.index') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block transition-colors">← Retour à la liste</a>
            <h2 class="text-xl font-semibold text-white tracking-tight">{{ $request->requested_company_name ?? $request->name }}</h2>
        </div>
        @if ($request->status === 'pending')
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                @if($plans->isEmpty())
                    <p class="text-sm text-amber-400">Aucun plan actif. Créez un plan dans <a href="{{ route('app.admin.plans.index') }}" class="underline hover:text-amber-300">Plans & tarifs</a> pour pouvoir approuver.</p>
                @else
                    <div class="flex gap-3">
                        <button type="button" @click="$dispatch('open-approve-modal')" class="glm-btn-primary">Approuver</button>
                        <button type="button" @click="$dispatch('open-reject-modal')" class="glm-btn-secondary">Refuser</button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="glm-card-static p-6">
            <h3 class="text-base font-semibold text-white">Entreprise & contact</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="font-medium text-slate-500">Entreprise</dt><dd class="mt-0.5 text-slate-200">{{ $request->requested_company_name ?? '–' }}</dd></div>
                <div><dt class="font-medium text-slate-500">ICE</dt><dd class="mt-0.5 text-slate-200">{{ $request->requested_ice ?? '–' }}</dd></div>
                <div><dt class="font-medium text-slate-500">Responsable</dt><dd class="mt-0.5 text-slate-200">{{ $request->name }}</dd></div>
                <div><dt class="font-medium text-slate-500">Email</dt><dd class="mt-0.5 text-slate-200">{{ $request->email }}</dd></div>
                <div><dt class="font-medium text-slate-500">Téléphone</dt><dd class="mt-0.5 text-slate-200">{{ $request->phone ?? '–' }}</dd></div>
                <div><dt class="font-medium text-slate-500">Pays</dt><dd class="mt-0.5 text-slate-200">{{ $request->requested_country ?? '–' }}</dd></div>
                <div><dt class="font-medium text-slate-500">Plan choisi</dt><dd class="mt-0.5 text-slate-200">{{ $request->requested_plan ?? '–' }}</dd></div>
            </dl>
        </div>
        <div class="glm-card-static p-6">
            <h3 class="text-base font-semibold text-white">Statut & notes</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="font-medium text-slate-500">Statut</dt>
                    <dd class="mt-0.5">
                        @if ($request->status === 'pending')
                            <span class="inline-flex rounded-full bg-amber-500/20 px-2.5 py-0.5 text-xs font-medium text-amber-400">En attente</span>
                        @elseif ($request->status === 'active')
                            <span class="inline-flex rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-medium text-emerald-400">Approuvé</span>
                        @else
                            <span class="inline-flex rounded-full bg-red-500/20 px-2.5 py-0.5 text-xs font-medium text-red-400">Refusé</span>
                        @endif
                    </dd>
                </div>
                @if ($request->registration_message)
                    <div><dt class="font-medium text-slate-500">Message</dt><dd class="mt-0.5 text-slate-200">{{ $request->registration_message }}</dd></div>
                @endif
                @if ($request->admin_notes)
                    <div><dt class="font-medium text-slate-500">Notes admin</dt><dd class="mt-0.5 text-slate-200">{{ $request->admin_notes }}</dd></div>
                @endif
                @if ($request->status === 'rejected' && $request->rejection_reason)
                    <div><dt class="font-medium text-slate-500">Raison du refus</dt><dd class="mt-0.5 text-slate-200">{{ $request->rejection_reason }}</dd></div>
                @endif
                <div><dt class="font-medium text-slate-500">Date d'inscription</dt><dd class="mt-0.5 text-slate-200">{{ $request->created_at->format('d/m/Y H:i') }}</dd></div>
            </dl>
        </div>
    </div>

    {{-- Approve modal – dark surface to match app (only when plans exist) --}}
    @if(!$plans->isEmpty())
    <div x-data="{ open: false }" @open-approve-modal.window="open = true" x-show="open" x-cloak class="relative z-50" role="dialog" aria-modal="true">
        <div x-show="open" x-transition class="fixed inset-0 bg-black/60"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div x-show="open" x-transition class="glm-dark-bg w-full max-w-md rounded-2xl bg-slate-800 p-6 shadow-2xl ring-1 ring-white/10">
                <h3 class="text-lg font-semibold text-white">Approuver la demande</h3>
                <form action="{{ route('app.registration-requests.approve', $request) }}" method="post" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="plan_id" class="block text-sm font-medium text-slate-400">Plan <span class="text-red-400">*</span></label>
                        <select id="plan_id" name="plan_id" required class="mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                            @foreach ($plans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} — {{ number_format($p->monthly_price, 0, ',', ' ') }} MAD/mois</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="trial_days" class="block text-sm font-medium text-slate-400">Durée de l'essai (jours)</label>
                        <select id="trial_days" name="trial_days" class="mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                            <option value="0">Aucun</option>
                            <option value="7">7 jours</option>
                            <option value="14" selected>14 jours</option>
                            <option value="30">30 jours</option>
                        </select>
                    </div>
                    <div>
                        <label for="custom_pricing" class="block text-sm font-medium text-slate-400">Tarification personnalisée (optionnel)</label>
                        <input type="text" id="custom_pricing" name="custom_pricing" class="mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50">
                    </div>
                    <div>
                        <label for="admin_notes_approve" class="block text-sm font-medium text-slate-400">Note interne admin</label>
                        <textarea id="admin_notes_approve" name="admin_notes" rows="3" class="mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = false" class="glm-btn-secondary">Annuler</button>
                        <button type="submit" class="glm-btn-primary">Approuver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Reject modal --}}
    <div x-data="{ open: false }" @open-reject-modal.window="open = true" x-show="open" x-cloak class="relative z-50" role="dialog" aria-modal="true">
        <div x-show="open" x-transition class="fixed inset-0 bg-black/60"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div x-show="open" x-transition class="glm-dark-bg w-full max-w-md rounded-2xl bg-slate-800 p-6 shadow-2xl ring-1 ring-white/10">
                <h3 class="text-lg font-semibold text-white">Refuser la demande</h3>
                <form action="{{ route('app.registration-requests.reject', $request) }}" method="post" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="rejection_reason" class="block text-sm font-medium text-slate-400">Raison du refus <span class="text-red-400">*</span></label>
                        <textarea id="rejection_reason" name="rejection_reason" required rows="3" class="mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50"></textarea>
                    </div>
                    <div>
                        <label for="admin_notes_reject" class="block text-sm font-medium text-slate-400">Note interne admin</label>
                        <textarea id="admin_notes_reject" name="admin_notes" rows="3" class="mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = false" class="glm-btn-secondary">Annuler</button>
                        <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-500 transition-colors">Refuser</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
