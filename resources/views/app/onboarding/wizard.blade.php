@extends('app.layouts.app')

@section('pageSubtitle')
Configuration de votre espace
@endsection

@section('content')
<div class="glm-fade-in max-w-2xl mx-auto">
    <header class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-white">Bienvenue sur GLM</h1>
        <p class="mt-1 text-sm text-slate-400">Configurez votre espace en quelques étapes. Vous pourrez modifier tout cela plus tard.</p>
    </header>

    {{-- Progress bar --}}
    <div class="mb-8">
        <div class="flex items-center justify-between gap-2 mb-2">
            @foreach ([1 => 'Entreprise & agence', 2 => 'Première flotte', 3 => 'Réseau partenaires', 4 => 'Récapitulatif'] as $s => $label)
                <span class="text-xs font-medium {{ $step >= $s ? 'text-[#2563EB]' : 'text-slate-500' }}">{{ $label }}</span>
            @endforeach
        </div>
        <div class="h-2 rounded-full bg-white/10 overflow-hidden flex">
            <div class="h-full bg-gradient-to-r from-[#2563EB] to-[#3B82F6] transition-all duration-300" style="width: {{ ($step / 4) * 100 }}%"></div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-200">
            <ul class="list-disc list-inside">{{ implode('', array_map(fn ($e) => '<li>' . e($e) . '</li>', $errors->all())) }}</ul>
        </div>
    @endif

    {{-- Step 1: Entreprise / première agence --}}
    @if ($step === 1)
        <div class="glm-card-static p-6 rounded-2xl">
            <h2 class="text-lg font-semibold text-white mb-2">Étape 1 — Entreprise & première agence</h2>
            <p class="text-sm text-slate-400 mb-6">Indiquez les informations de votre entreprise et créez votre première agence (point de location).</p>
            <form action="{{ route('app.onboarding.store.step1') }}" method="post" class="space-y-5">
                @csrf
                <div class="space-y-4">
                    <p class="text-sm font-medium text-slate-300">Entreprise</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="company_name" class="mb-1 block text-sm text-slate-400">Nom de l'entreprise</label>
                            <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $company->name) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Ex. Atlas Rent Car">
                        </div>
                        <div>
                            <label for="company_city" class="mb-1 block text-sm text-slate-400">Ville</label>
                            <input type="text" id="company_city" name="company_city" value="{{ old('company_city', $company->city) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Ex. Casablanca">
                        </div>
                    </div>
                </div>
                <div class="border-t border-white/10 pt-5 space-y-4">
                    <p class="text-sm font-medium text-slate-300">Première agence (point de location)</p>
                    <div>
                        <label for="branch_name" class="mb-1 block text-sm text-slate-400">Nom de l'agence *</label>
                        <input type="text" id="branch_name" name="branch_name" value="{{ old('branch_name') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Ex. Agence Centre">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="branch_city" class="mb-1 block text-sm text-slate-400">Ville</label>
                            <input type="text" id="branch_city" name="branch_city" value="{{ old('branch_city') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Casablanca">
                        </div>
                        <div>
                            <label for="branch_phone" class="mb-1 block text-sm text-slate-400">Téléphone</label>
                            <input type="text" id="branch_phone" name="branch_phone" value="{{ old('branch_phone') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="+212 5 00 00 00 00">
                        </div>
                    </div>
                    <div>
                        <label for="branch_address" class="mb-1 block text-sm text-slate-400">Adresse</label>
                        <input type="text" id="branch_address" name="branch_address" value="{{ old('branch_address') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Adresse complète">
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="glm-btn-primary">Continuer</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Step 2: Première flotte --}}
    @if ($step === 2)
        <div class="glm-card-static p-6 rounded-2xl">
            <h2 class="text-lg font-semibold text-white mb-2">Étape 2 — Première flotte</h2>
            <p class="text-sm text-slate-400 mb-6">Ajoutez un ou plusieurs véhicules maintenant, ou passez cette étape et ajoutez-les plus tard.</p>
            <form action="{{ route('app.onboarding.store.step2') }}" method="post" class="space-y-6" x-data="{ count: 1, skip: false }">
                @csrf
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="skip_vehicles" value="0" x-ref="skipHidden">
                    <input type="checkbox" name="skip_vehicles" value="1" x-model="skip" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                    <span class="text-slate-300">Passer pour l'instant (j'ajouterai des véhicules plus tard)</span>
                </label>
                <div x-show="!skip" x-cloak class="space-y-4">
                    <p class="text-sm text-slate-400">Combien de véhicules souhaitez-vous ajouter ?</p>
                    <select x-model.number="count" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }} véhicule(s)</option>
                        @endfor
                    </select>
                    <div class="space-y-4 pt-2">
                        @for ($i = 0; $i < 5; $i++)
                            <div class="rounded-xl border border-white/10 bg-white/5 p-4 space-y-3 vehicle-row" data-index="{{ $i }}" x-show="{{ $i }} < count" x-cloak>
                                <p class="text-xs font-medium text-slate-400">Véhicule {{ $i + 1 }}</p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <input type="text" name="vehicles[{{ $i }}][plate]" placeholder="Plaque *" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white vehicle-plate">
                                    <input type="text" name="vehicles[{{ $i }}][brand]" placeholder="Marque *" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                                    <input type="text" name="vehicles[{{ $i }}][model]" placeholder="Modèle *" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                                    <input type="number" step="0.01" min="0" name="vehicles[{{ $i }}][daily_price]" placeholder="Prix/jour (MAD)" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                                </div>
                            </div>
                        @endfor
                    </div>
                    <p class="text-xs text-slate-500">Seuls les véhicules dont la plaque, marque et modèle sont renseignés seront créés.</p>
                </div>
                <div class="flex justify-between pt-2">
                    <a href="{{ route('app.onboarding.show', ['step' => 1]) }}" class="glm-btn-secondary no-underline">Retour</a>
                    <button type="submit" class="glm-btn-primary">Continuer</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Step 3: Réseau partenaires --}}
    @if ($step === 3)
        <div class="glm-card-static p-6 rounded-2xl">
            <h2 class="text-lg font-semibold text-white mb-2">Étape 3 — Réseau partenaires GLM</h2>
            <p class="text-sm text-slate-400 mb-6">Rejoignez le réseau pour que d'autres agences puissent voir votre disponibilité et vous contacter si besoin.</p>
            <form action="{{ route('app.onboarding.store.step3') }}" method="post" class="space-y-6" x-data="{ join: false }">
                @csrf
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="join_network" value="0">
                    <input type="checkbox" name="join_network" value="1" x-model="join" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                    <span class="font-medium text-white">Rejoindre le réseau partenaires GLM</span>
                </label>
                <p class="text-sm text-slate-400">Les autres agences pourront voir votre disponibilité (ville, catégories de véhicules). Aucune donnée de réservation ni plaque n'est partagée.</p>
                <div x-show="join" x-cloak class="space-y-4 pl-6 border-l-2 border-[#2563EB]/30">
                    <p class="text-sm font-medium text-slate-300">Informations visibles par les partenaires</p>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="show_company_name" value="0">
                            <input type="checkbox" name="show_company_name" value="1" checked class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                            <span class="text-slate-300">Nom de l'entreprise</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="show_city" value="0">
                            <input type="checkbox" name="show_city" value="1" checked class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                            <span class="text-slate-300">Ville</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="show_phone" value="0">
                            <input type="checkbox" name="show_phone" value="1" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                            <span class="text-slate-300">Téléphone</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="show_email" value="0">
                            <input type="checkbox" name="show_email" value="1" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                            <span class="text-slate-300">Email</span>
                        </label>
                    </div>
                    <p class="text-sm text-slate-400">Catégories de véhicules à partager</p>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($categories as $key => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="shared_categories[]" value="{{ $key }}" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                                <span class="text-slate-300 text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="allow_contact_requests" value="0">
                        <input type="checkbox" name="allow_contact_requests" value="1" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                        <span class="text-slate-300">Autoriser d'autres agences à me contacter</span>
                    </label>
                </div>
                <div class="flex justify-between pt-2">
                    <a href="{{ route('app.onboarding.show', ['step' => 2]) }}" class="glm-btn-secondary no-underline">Retour</a>
                    <button type="submit" class="glm-btn-primary">Continuer</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Step 4: Récapitulatif --}}
    @if ($step === 4)
        <div class="glm-card-static p-6 rounded-2xl">
            <h2 class="text-lg font-semibold text-white mb-2">Étape 4 — Récapitulatif</h2>
            <p class="text-sm text-slate-400 mb-6">Votre espace est prêt. Voici ce qui a été configuré.</p>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4 py-2 border-b border-white/10">
                    <dt class="text-slate-400">Entreprise</dt>
                    <dd class="text-white font-medium">{{ $company->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2 border-b border-white/10">
                    <dt class="text-slate-400">Agences</dt>
                    <dd class="text-white">{{ $company->branches()->count() }} agence(s)</dd>
                </div>
                <div class="flex justify-between gap-4 py-2 border-b border-white/10">
                    <dt class="text-slate-400">Véhicules</dt>
                    <dd class="text-white">{{ $company->vehicles()->count() }} véhicule(s)</dd>
                </div>
                <div class="flex justify-between gap-4 py-2 border-b border-white/10">
                    <dt class="text-slate-400">Réseau partenaires</dt>
                    <dd class="text-white">{{ $company->partnerSetting && $company->partnerSetting->share_enabled ? 'Activé' : 'Non activé' }}</dd>
                </div>
            </dl>
            <form action="{{ route('app.onboarding.store.step4') }}" method="post" class="mt-8">
                @csrf
                <button type="submit" class="glm-btn-primary w-full sm:w-auto">Accéder au tableau de bord</button>
            </form>
        </div>
    @endif
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection
