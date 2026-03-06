@extends('app.layouts.app')

@section('pageSubtitle')
Nouvelle réservation – {{ $company->name }}
@endsection

@php
    $vehiclesJson = $vehicles->map(fn ($v) => [
        'id' => $v->id,
        'plate' => $v->plate,
        'brand' => $v->brand,
        'model' => $v->model,
        'year' => $v->year,
        'daily_price' => (float) ($v->daily_price ?? 0),
        'image_url' => $v->image_url,
        'branch_name' => $v->branch?->name ?? '–',
        'fuel' => $v->fuel ?? '–',
        'transmission' => $v->transmission ?? '–',
        'status' => $v->status ?? 'available',
        'deposit' => (float) ($v->deposit ?? 0),
    ])->values();
    $customersJson = $customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'cin' => $c->cin ?? null, 'phone' => $c->phone ?? null, 'email' => $c->email ?? null, 'is_flagged' => (bool) $c->is_flagged])->values();
    $availabilityUrlTemplate = route('app.companies.reservations.vehicle-availability', [$company, 'VEHICLE_ID']);
    $customerLookupUrl = route('app.companies.customers.lookup-by-cin', $company);
    $customerStoreUrl = route('app.companies.customers.store', $company);
    $customerExtractUrl = route('app.companies.customers.extract-documents', $company);
    $customerStoreCsrf = csrf_token();
@endphp

@section('content')
<div class="glm-fade-in" x-data="reservationWizard({{ $errors->any() ? '4' : '1' }})" x-init="init()">
    <header class="mb-6">
        <a href="{{ route('app.companies.reservations.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Réservations · {{ $company->name }}</a>
        <h1 class="text-2xl font-bold tracking-tight text-white">Nouvelle réservation</h1>
        <p class="mt-1 text-sm text-slate-400">1. Dates → 2. Véhicule → 3. Client → 4. Confirmation</p>
    </header>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-200">
            <p class="font-medium">Veuillez corriger les erreurs suivantes :</p>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('app.companies.reservations.store', $company) }}" method="post" @@submit="onSubmit" class="lg:grid lg:grid-cols-[1fr,340px] lg:gap-8">
        @csrf
        <input type="hidden" name="vehicle_id" :value="vehicleId">
        <input type="hidden" name="customer_id" :value="customerId">
        <input type="hidden" name="start_at" :value="startAt">
        <input type="hidden" name="end_at" :value="endAt">
        <input type="hidden" name="total_price" :value="totalPrice">
        <input type="hidden" name="notes" value="">
        <input type="hidden" name="internal_notes" value="">
        <input type="hidden" name="paid_now" :value="paidNow ? '1' : '0'">
        <input type="hidden" name="deposit_received" :value="depositReceived ? '1' : '0'">
        <input type="hidden" name="confirm_and_start" :value="confirmAndStart ? '1' : '0'">

        {{-- Left: Steps --}}
        <div class="space-y-6 order-2 lg:order-1">
            {{-- Step indicator --}}
            <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                @foreach ([1,2,3,4] as $s)
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition"
                            :class="step >= {{ $s }} ? 'bg-[#2563EB] text-white' : 'bg-white/10 text-slate-400'">{{ $s }}</span>
                        @if ($s < 4)<span class="hidden sm:inline text-slate-500">→</span>@endif
                    </div>
                @endforeach
            </div>

            {{-- Step 1: Dates --}}
            <div x-show="step === 1" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="glm-card-static p-6 rounded-xl">
                <h2 class="text-lg font-semibold text-white mb-2">Choisir la période</h2>
                <p class="text-sm text-slate-400 mb-4">Indiquez les dates de début et de fin. La durée et le montant estimé se mettent à jour automatiquement.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="start_at" class="mb-1 block text-sm font-medium text-slate-300">Début *</label>
                        <input type="datetime-local" id="start_at" x-model="startAt" @@change="recalcPrice()" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label for="end_at" class="mb-1 block text-sm font-medium text-slate-300">Fin *</label>
                        <input type="datetime-local" id="end_at" x-model="endAt" @@change="recalcPrice()" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-4 text-sm">
                    <span class="text-slate-400">Durée : <strong class="text-white" x-text="days + ' jour(s)'"></strong></span>
                    <span class="text-slate-400" x-show="vehicleId">Estimation : <strong class="text-emerald-400" x-text="totalPriceFormatted + ' MAD'"></strong></span>
                </div>
                <div class="mt-6 flex justify-between">
                    <span></span>
                    <button type="button" @@click="validateStep1AndGoToVehicle()" class="glm-btn-primary">Suivant</button>
                </div>
            </div>

            {{-- Step 2: Vehicle cards --}}
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="glm-card-static p-6 rounded-xl">
                <h2 class="text-lg font-semibold text-white mb-2">Choisir le véhicule</h2>
                <p class="text-sm text-slate-400 mb-4">Sélectionnez un véhicule disponible pour la période choisie.</p>
                <p x-show="step === 2 && (!vehicles || vehicles.length === 0)" x-cloak class="text-sm text-amber-400 mb-4">Aucun véhicule à afficher. Rechargez la page (F5) ou vérifiez que des véhicules existent pour cette entreprise.</p>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" id="vehicle-cards-container">
                    <template x-for="(v, index) in (vehicles || [])" :key="v.id">
                        <div @@click="selectVehicle(v)"
                            class="rounded-xl border-2 transition cursor-pointer overflow-hidden"
                            :class="vehicleAvailable(v.id) ? (vehicleId == v.id ? 'border-[#2563EB] bg-[#2563EB]/10' : 'border-white/10 bg-white/[0.04] hover:border-white/20 hover:bg-white/5') : 'border-red-500/30 bg-red-500/5 cursor-not-allowed opacity-75'"
                            :title="!vehicleAvailable(v.id) ? 'Indisponible sur cette période' : ''">
                            <div class="aspect-video bg-slate-800/50 relative">
                                <template x-if="v.image_url">
                                    <img :src="v.image_url" :alt="v.plate" class="h-full w-full object-cover" @@error="$event.target.style.display='none'; $event.target.nextElementSibling && ($event.target.nextElementSibling.style.display='flex')">
                                    <div class="hidden h-full items-center justify-center text-slate-500 bg-white/5" style="display:none"><svg class="h-12 w-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg></div>
                                </template>
                                <template x-if="!v.image_url">
                                    <div class="flex h-full items-center justify-center text-slate-500 bg-white/5">
                                        <svg class="h-12 w-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                                    </div>
                                </template>
                                <span class="absolute top-2 right-2 rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="vehicleAvailable(v.id) ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300'"
                                    x-text="vehicleAvailable(v.id) ? 'Disponible' : 'Indisponible'"></span>
                            </div>
                            <div class="p-3">
                                <p class="font-semibold text-white" x-text="v.plate"></p>
                                <p class="text-sm text-slate-400" x-text="v.brand + ' ' + v.model + (v.year ? ' (' + v.year + ')' : '')"></p>
                                <p class="text-sm font-medium text-[#93C5FD] mt-1" x-text="v.daily_price ? (v.daily_price.toLocaleString('fr-MA') + ' MAD/jour') : '–'"></p>
                                <p class="text-xs text-slate-500 mt-1" x-text="v.branch_name"></p>
                                <div class="flex gap-2 mt-2 text-slate-500">
                                    <span class="text-xs" x-text="v.fuel"></span>
                                    <span class="text-xs" x-text="v.transmission"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="mt-6 flex justify-between">
                    <button type="button" @@click="step = 1" class="glm-btn-secondary">Retour</button>
                    <button type="button" @@click="validateStep2AndGoToClient()" class="glm-btn-primary">Suivant</button>
                </div>
            </div>

            {{-- Step 3: Client (CIN search + risk + history) --}}
            <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="glm-card-static p-6 rounded-xl">
                <h2 class="text-lg font-semibold text-white mb-2">Choisir le client</h2>
                <p class="text-sm text-slate-400 mb-4">Saisissez le CIN pour rechercher un client existant ou créez-en un nouveau.</p>
                <div class="space-y-4">
                    <div>
                        <label for="cin_search" class="mb-1 block text-sm font-medium text-slate-300">CIN</label>
                        <div class="relative">
                            <input type="text" id="cin_search" x-model="cinSearch" @@input.debounce.400ms="lookupCin()"
                                class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 pr-10 text-sm text-white" placeholder="Rechercher par CIN…">
                            <span x-show="cinLoading" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </span>
                        </div>
                    </div>
                    <div x-show="customerSearchResult && customerSearchResult.found" x-cloak class="rounded-xl border border-white/10 bg-white/5 p-4 space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <p class="font-semibold text-white" x-text="customerSearchResult.customer.name"></p>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="{ 'bg-emerald-500/20 text-emerald-300': customerSearchResult.risk === 'green', 'bg-amber-500/20 text-amber-300': customerSearchResult.risk === 'yellow', 'bg-red-500/20 text-red-300': customerSearchResult.risk === 'red' }"
                                x-text="customerSearchResult.risk === 'green' ? '🟢 Client fiable' : (customerSearchResult.risk === 'yellow' ? '🟡 Retards de paiement' : '🔴 Historique de dommages / signalé')"></span>
                        </div>
                        <p class="text-sm text-slate-400" x-text="'Tél. ' + (customerSearchResult.customer.phone || '–') + ' · ' + (customerSearchResult.customer.email || '–')"></p>
                        <div class="grid grid-cols-2 gap-2 text-xs text-slate-500">
                            <span x-text="'Réservations : ' + customerSearchResult.total_reservations"></span>
                            <span x-text="'Revenus : ' + customerSearchResult.total_revenue.toLocaleString('fr-MA') + ' MAD'"></span>
                            <span x-text="'Impayé : ' + customerSearchResult.unpaid_balance.toLocaleString('fr-MA') + ' MAD'"></span>
                            <span x-text="'Dégâts : ' + customerSearchResult.damage_count"></span>
                        </div>
                        <button type="button" @@click="selectSearchedCustomer()" class="glm-btn-primary text-sm">Utiliser ce client</button>
                    </div>
                    <p x-show="customerSearchResult && !customerSearchResult.found && cinSearch.length >= 2" x-cloak class="text-sm text-slate-400">Aucun client trouvé. Créez-en un ci-dessous.</p>
                    <div class="border-t border-white/10 pt-4">
                        <p class="text-sm font-medium text-slate-300 mb-2">Ou sélectionner un client existant</p>
                        <select x-model="customerId" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                            <option value="">— Sélectionner —</option>
                            <template x-for="c in customers" :key="c.id">
                                <option :value="c.id" x-text="c.name + ' – ' + (c.cin || '') + (c.is_flagged ? ' ⚠ Signalé' : '')"></option>
                            </template>
                        </select>
                    </div>
                    <div class="border-t border-white/10 pt-4">
                        <button type="button" @@click="showAddClientModal = true; newClientExtractMerged = null; newClientExtractCin = false; newClientExtractLicense = false; newClientExtractError = ''" class="text-[#93C5FD] hover:text-white text-sm font-medium">+ Créer un nouveau client</button>
                    </div>
                </div>
                <div class="mt-6 flex justify-between">
                    <button type="button" @@click="step = 2" class="glm-btn-secondary">Retour</button>
                    <button type="button" @@click="goStep4()" class="glm-btn-primary">Suivant</button>
                </div>
            </div>

            {{-- Step 4: Confirmation --}}
            <div x-show="step === 4" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="glm-card-static p-6 rounded-xl">
                <h2 class="text-lg font-semibold text-white mb-4">Récapitulatif</h2>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4 space-y-3">
                    <div class="flex gap-3">
                        <template x-if="selectedVehicle && selectedVehicle.image_url">
                            <img :src="selectedVehicle.image_url" alt="" class="h-20 w-24 rounded-lg object-cover shrink-0" @@error="$event.target.style.display='none'; $event.target.nextElementSibling && ($event.target.nextElementSibling.style.display='flex')">
                            <div class="hidden h-20 w-24 shrink-0 rounded-lg bg-white/10 items-center justify-center" style="display:none"><svg class="h-8 w-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg></div>
                        </template>
                        <template x-if="selectedVehicle && !selectedVehicle.image_url">
                            <div class="h-20 w-24 shrink-0 rounded-lg bg-white/10 flex items-center justify-center"><svg class="h-8 w-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg></div>
                        </template>
                        <div>
                            <p class="font-medium text-white" x-text="vehicleLabel"></p>
                            <p class="text-sm text-slate-400" x-text="customerLabel"></p>
                        </div>
                    </div>
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        <dt class="text-slate-500">Période</dt><dd class="text-white" x-text="periodLabel"></dd>
                        <dt class="text-slate-500">Jours</dt><dd class="text-white" x-text="days"></dd>
                        <dt class="text-slate-500">Tarif jour</dt><dd class="text-white" x-text="dailyPriceFormatted + ' MAD'"></dd>
                        <dt class="text-slate-500">Sous-total</dt><dd class="text-white" x-text="totalPriceFormatted + ' MAD'"></dd>
                        <dt class="text-slate-500">Caution</dt><dd class="text-white" x-text="depositFormatted + ' MAD'"></dd>
                        <dt class="text-slate-500 font-semibold">Total à encaisser</dt><dd class="font-semibold text-white" x-text="totalWithDepositFormatted + ' MAD'"></dd>
                    </dl>
                </div>
                <div class="mt-4 space-y-3">
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" x-model="paidNow" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                        Paiement reçu maintenant
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" x-model="depositReceived" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                        Caution reçue
                    </label>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="button" @@click="step = 3" class="glm-btn-secondary">Retour</button>
                    <button type="submit" name="status" value="draft" class="glm-btn-secondary" :disabled="submitting" @@click="submitting = true; confirmAndStart = false">Enregistrer en brouillon</button>
                    <button type="submit" name="status" value="confirmed" class="glm-btn-primary" :disabled="submitting" @@click="submitting = true; confirmAndStart = false" x-text="submitting ? 'Enregistrement…' : 'Confirmer la réservation'">Confirmer la réservation</button>
                    <input type="submit" name="status" value="confirmed" x-ref="confirmAndStartSubmit" class="hidden">
                    <button type="button" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-500 disabled:opacity-50" :disabled="submitting" @@click="confirmAndStart = true; submitting = true; $nextTick(() => $refs.confirmAndStartSubmit.click())">Confirmer et démarrer</button>
                </div>
            </div>
        </div>

        {{-- Right: Sticky summary (desktop) / Collapsible (mobile) --}}
        <div class="order-1 lg:order-2 lg:sticky lg:top-6 h-fit">
            <div class="glm-card-static p-5 rounded-xl border border-white/10" :class="{ 'lg:sticky lg:top-6': true }">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Récapitulatif</h3>
                <div class="space-y-3 text-sm">
                    <template x-if="selectedVehicle">
                        <div class="flex gap-3">
                            <template x-if="selectedVehicle.image_url">
                                <img :src="selectedVehicle.image_url" alt="" class="h-16 w-20 rounded-lg object-cover shrink-0" @@error="$event.target.style.display='none'; $event.target.nextElementSibling && ($event.target.nextElementSibling.style.display='flex')">
                                <div class="hidden h-16 w-20 shrink-0 rounded-lg bg-white/10 items-center justify-center" style="display:none"><svg class="h-6 w-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg></div>
                            </template>
                            <template x-if="!selectedVehicle.image_url">
                                <div class="h-16 w-20 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                    <svg class="h-6 w-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                </div>
                            </template>
                            <div class="min-w-0">
                                <p class="font-medium text-white truncate" x-text="vehicleLabel"></p>
                                <p class="text-slate-400" x-text="selectedVehicle ? (selectedVehicle.daily_price.toLocaleString('fr-MA') + ' MAD/jour') : ''"></p>
                            </div>
                        </div>
                    </template>
                    <p class="text-slate-400"><span class="text-slate-500">Client :</span> <span class="text-white" x-text="customerLabel || '–'"></span></p>
                    <p class="text-slate-400"><span class="text-slate-500">Période :</span> <span class="text-white" x-text="periodLabel || '–'"></span></p>
                    <p class="text-slate-400"><span class="text-slate-500">Jours :</span> <span class="text-white" x-text="days"></span></p>
                    <p class="pt-2 border-t border-white/10 font-semibold text-white" x-text="'Total : ' + totalPriceFormatted + ' MAD'"></p>
                </div>
            </div>
        </div>
    </form>

    {{-- Modal: Add client --}}
    <div x-show="showAddClientModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 overflow-y-auto" x-transition @@keydown.escape.window="showAddClientModal = false">
        <div class="glm-card-static max-w-md w-full p-6 my-8" @@click.stop>
            <h3 class="text-lg font-semibold text-white mb-4">Nouveau client</h3>

            {{-- Optional: upload CIN / Permis to auto-fill --}}
            <div class="mb-4 rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Ou importer depuis des documents</p>
                <div class="flex gap-2">
                    <div class="flex-1 relative rounded-lg border-2 border-dashed border-white/20 hover:border-white/40 transition min-h-[56px] flex items-center justify-center" @@dragover.prevent="$event.currentTarget.classList.add('border-[#2563EB]/50')" @@dragleave.prevent="$event.currentTarget.classList.remove('border-[#2563EB]/50')" @@drop.prevent="uploadNewClientDoc($event, 'cin_front')">
                        <input type="file" class="absolute inset-0 opacity-0 cursor-pointer" accept=".pdf,.jpg,.jpeg,.png" @@change="uploadNewClientDoc($event, 'cin_front')" :disabled="newClientExtractLoading">
                        <span class="text-xs text-slate-400" x-text="newClientExtractCin ? 'CIN · OK' : 'CIN'"></span>
                    </div>
                    <div class="flex-1 relative rounded-lg border-2 border-dashed border-white/20 hover:border-white/40 transition min-h-[56px] flex items-center justify-center" @@dragover.prevent="$event.currentTarget.classList.add('border-[#2563EB]/50')" @@dragleave.prevent="$event.currentTarget.classList.remove('border-[#2563EB]/50')" @@drop.prevent="uploadNewClientDoc($event, 'license')">
                        <input type="file" class="absolute inset-0 opacity-0 cursor-pointer" accept=".pdf,.jpg,.jpeg,.png" @@change="uploadNewClientDoc($event, 'license')" :disabled="newClientExtractLoading">
                        <span class="text-xs text-slate-400" x-text="newClientExtractLicense ? 'Permis · OK' : 'Permis'"></span>
                    </div>
                </div>
                <p x-show="newClientExtractLoading" class="mt-1 text-xs text-slate-500">Extraction…</p>
                <p x-show="newClientExtractError" class="mt-1 text-xs text-red-400" x-text="newClientExtractError"></p>
                <div x-show="newClientExtractMerged && (newClientExtractMerged.name || newClientExtractMerged.cin || newClientExtractMerged.driving_license_number)" class="mt-2 flex items-center gap-2">
                    <span class="text-xs text-slate-400">Données détectées</span>
                    <button type="button" @@click="prefillNewClientFromExtract()" class="text-xs font-medium text-[#93C5FD] hover:text-white">Pré-remplir le formulaire</button>
                </div>
            </div>

            <form @@submit.prevent="submitNewClient()" class="space-y-4">
                <div><label for="new_client_name" class="mb-1 block text-sm font-medium text-slate-300">Nom *</label><input type="text" id="new_client_name" x-model="newClientName" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Nom complet"></div>
                <div><label for="new_client_cin" class="mb-1 block text-sm font-medium text-slate-300">CIN *</label><input type="text" id="new_client_cin" x-model="newClientCin" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="CIN"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label for="new_client_license_number" class="mb-1 block text-sm font-medium text-slate-300">N° permis</label><input type="text" id="new_client_license_number" x-model="newClientDrivingLicenseNumber" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="N° permis"></div>
                    <div><label for="new_client_license_expiry" class="mb-1 block text-sm font-medium text-slate-300">Expiration permis</label><input type="date" id="new_client_license_expiry" x-model="newClientDrivingLicenseExpiry" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                </div>
                <div><label for="new_client_address" class="mb-1 block text-sm font-medium text-slate-300">Adresse</label><input type="text" id="new_client_address" x-model="newClientAddress" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Adresse"></div>
                <div><label for="new_client_phone" class="mb-1 block text-sm font-medium text-slate-300">Téléphone</label><input type="text" id="new_client_phone" x-model="newClientPhone" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="+212 6..."></div>
                <div><label for="new_client_email" class="mb-1 block text-sm font-medium text-slate-300">Email</label><input type="email" id="new_client_email" x-model="newClientEmail" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="email@@exemple.ma"></div>
                <p x-show="addClientError" x-text="addClientError" class="text-sm text-red-400"></p>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @@click="showAddClientModal = false; addClientError = ''" class="glm-btn-secondary">Annuler</button>
                    <button type="submit" class="glm-btn-primary" :disabled="addClientLoading" x-text="addClientLoading ? 'Enregistrement…' : 'Ajouter'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Client flagged --}}
    <div x-show="showFlaggedModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" x-transition>
        <div class="glm-card-static max-w-md p-6" @@click.stop>
            <h3 class="text-lg font-semibold text-white mb-2">Client signalé</h3>
            <p class="text-slate-300 text-sm mb-4">Ce client est marqué comme signalé. Voulez-vous quand même l'utiliser pour cette réservation ?</p>
            <div class="flex gap-3 justify-end">
                <button type="button" @@click="showFlaggedModal = false; customerId = ''" class="glm-btn-secondary">Annuler</button>
                <button type="button" @@click="showFlaggedModal = false; step = 4; recalcPrice()" class="glm-btn-primary">Oui, continuer</button>
            </div>
        </div>
    </div>
</div>

<script>
function reservationWizard(initialStep) {
    const vehiclesData = @json($vehiclesJson);
    const availabilityUrl = @json($availabilityUrlTemplate);
    const customerLookupUrl = @json($customerLookupUrl);
    const customerStoreUrl = @json($customerStoreUrl);
    const customerExtractUrl = @json($customerExtractUrl);
    const customerStoreCsrf = @json($customerStoreCsrf);

    return {
        vehicles: Array.isArray(vehiclesData) ? vehiclesData : [],
        step: typeof initialStep === 'number' ? initialStep : 1,
        vehicleId: '',
        customerId: '',
        startAt: '',
        endAt: '',
        totalPrice: 0,
        dailyPrice: 0,
        reservedRangesByVehicle: {},
        customers: @json($customersJson),
        showFlaggedModal: false,
        showAddClientModal: false,
        newClientName: '',
        newClientCin: '',
        newClientPhone: '',
        newClientEmail: '',
        newClientAddress: '',
        newClientDrivingLicenseNumber: '',
        newClientDrivingLicenseExpiry: '',
        newClientExtractMerged: null,
        newClientExtractCin: false,
        newClientExtractLicense: false,
        newClientExtractLoading: false,
        newClientExtractError: '',
        addClientLoading: false,
        addClientError: '',
        cinSearch: '',
        cinLoading: false,
        customerSearchResult: null,
        paidNow: false,
        depositReceived: false,
        confirmAndStart: false,
        submitting: false,

        get selectedVehicle() {
            if (!this.vehicleId) return null;
            return (this.vehicles || []).find(x => x.id == this.vehicleId) || null;
        },
        get vehicleLabel() {
            const v = this.selectedVehicle;
            return v ? `${v.plate} – ${v.brand} ${v.model}` : '–';
        },
        get customerLabel() {
            if (!this.customerId) return '–';
            const c = this.customers.find(x => x.id == this.customerId);
            return c ? `${c.name} (${c.cin || ''})` : '–';
        },
        get periodLabel() {
            if (!this.startAt || !this.endAt) return '–';
            return this.startAt.slice(0, 16) + ' → ' + this.endAt.slice(0, 16);
        },
        get days() {
            if (!this.startAt || !this.endAt) return 0;
            const start = new Date(this.startAt);
            const end = new Date(this.endAt);
            const d = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            return Math.max(1, d);
        },
        get dailyPriceFormatted() { return new Intl.NumberFormat('fr-MA').format(this.dailyPrice); },
        get totalPriceFormatted() { return new Intl.NumberFormat('fr-MA').format(this.totalPrice); },
        get depositAmount() {
            const v = this.selectedVehicle;
            return v ? (v.deposit || 0) : 0;
        },
        get depositFormatted() { return new Intl.NumberFormat('fr-MA').format(this.depositAmount); },
        get totalWithDepositFormatted() { return new Intl.NumberFormat('fr-MA').format(this.totalPrice + this.depositAmount); },

        vehicleAvailable(vehicleId) {
            const ranges = this.reservedRangesByVehicle[vehicleId];
            if (!ranges || !this.startAt || !this.endAt) return true;
            const ourStart = this.startAt.slice(0, 10);
            const ourEnd = this.endAt.slice(0, 10);
            for (const r of ranges) {
                if (ourStart <= r.end && ourEnd >= r.start) return false;
            }
            return true;
        },
        selectVehicle(v) {
            if (!this.vehicleAvailable(v.id)) return;
            this.vehicleId = String(v.id);
            this.dailyPrice = v.daily_price || 0;
            this.recalcPrice();
        },

        init() {
            this.setDefaultDates();
            this.recalcPrice();
            this.$watch('step', (val) => {
                if (val === 2 && this.startAt && this.endAt) this.fetchAllAvailabilities();
            });
        },
        setDefaultDates() {
            if (!this.startAt) {
                const d = new Date();
                d.setHours(9, 0, 0, 0);
                this.startAt = d.toISOString().slice(0, 16);
            }
            if (!this.endAt) {
                const d = new Date(this.startAt);
                d.setDate(d.getDate() + 1);
                d.setHours(9, 0, 0, 0);
                this.endAt = d.toISOString().slice(0, 16);
            }
            this.recalcPrice();
        },
        fetchAllAvailabilities() {
            (this.vehicles || []).forEach(v => {
                fetch(availabilityUrl.replace('VEHICLE_ID', v.id))
                    .then(r => r.json())
                    .then(data => {
                        this.reservedRangesByVehicle[v.id] = data.reserved || [];
                    })
                    .catch(() => { this.reservedRangesByVehicle[v.id] = []; });
            });
        },
        recalcPrice() {
            const d = this.days || 0;
            this.totalPrice = Math.round(this.dailyPrice * d * 100) / 100;
        },
        validateStep1AndGoToVehicle() {
            if (!this.startAt || !this.endAt) { alert('Veuillez renseigner les dates.'); return; }
            const start = new Date(this.startAt);
            const end = new Date(this.endAt);
            if (end <= start) { alert('La date de fin doit être après la date de début.'); return; }
            this.step = 2;
            this.reservedRangesByVehicle = {};
            this.fetchAllAvailabilities();
        },
        validateStep2AndGoToClient() {
            if (!this.vehicleId) { alert('Veuillez sélectionner un véhicule.'); return; }
            const v = (this.vehicles || []).find(x => x.id == this.vehicleId);
            if (v && !this.vehicleAvailable(v.id)) {
                if (!confirm('Ce véhicule n\'est pas disponible sur cette période. Continuer quand même ?')) return;
            }
            this.recalcPrice();
            this.step = 3;
        },
        async lookupCin() {
            const cin = (this.cinSearch || '').trim();
            if (cin.length < 2) { this.customerSearchResult = null; return; }
            this.cinLoading = true;
            this.customerSearchResult = null;
            try {
                const r = await fetch(customerLookupUrl + '?cin=' + encodeURIComponent(cin));
                const data = await r.json();
                this.customerSearchResult = data;
            } catch (e) { this.customerSearchResult = { found: false }; }
            this.cinLoading = false;
        },
        selectSearchedCustomer() {
            if (!this.customerSearchResult || !this.customerSearchResult.found) return;
            const c = this.customerSearchResult.customer;
            const existing = this.customers.find(x => x.id == c.id);
            if (!existing) this.customers.push({ id: c.id, name: c.name, cin: c.cin, phone: c.phone, email: c.email, is_flagged: c.is_flagged });
            this.customerId = String(c.id);
            if (c.is_flagged) this.showFlaggedModal = true;
        },
        async uploadNewClientDoc(e, type) {
            const file = e.dataTransfer ? e.dataTransfer.files[0] : (e.target && e.target.files && e.target.files[0]);
            if (!file) return;
            this.newClientExtractError = '';
            this.newClientExtractLoading = true;
            const fd = new FormData();
            fd.append('file', file);
            fd.append('type', type);
            fd.append('_token', customerStoreCsrf);
            try {
                const r = await fetch(customerExtractUrl, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await r.json().catch(() => ({}));
                if (r.ok && data.merged) {
                    this.newClientExtractMerged = data.merged;
                    if (type === 'license') this.newClientExtractLicense = true; else this.newClientExtractCin = true;
                } else { this.newClientExtractError = 'Erreur extraction'; }
            } catch (err) { this.newClientExtractError = 'Erreur réseau'; }
            this.newClientExtractLoading = false;
        },
        prefillNewClientFromExtract() {
            const m = this.newClientExtractMerged;
            if (!m) return;
            if (m.name) this.newClientName = m.name;
            if (m.cin) this.newClientCin = m.cin;
            if (m.address) this.newClientAddress = m.address;
            if (m.driving_license_number) this.newClientDrivingLicenseNumber = m.driving_license_number;
            if (m.driving_license_expiry) this.newClientDrivingLicenseExpiry = m.driving_license_expiry;
        },
        async submitNewClient() {
            this.addClientError = '';
            this.addClientLoading = true;
            const formData = new FormData();
            formData.append('_token', customerStoreCsrf);
            formData.append('name', this.newClientName);
            formData.append('cin', this.newClientCin);
            formData.append('phone', this.newClientPhone || '');
            formData.append('email', this.newClientEmail || '');
            formData.append('address', this.newClientAddress || '');
            formData.append('driving_license_number', this.newClientDrivingLicenseNumber || '');
            formData.append('driving_license_expiry', this.newClientDrivingLicenseExpiry || '');
            try {
                const r = await fetch(customerStoreUrl, { method: 'POST', body: formData, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await r.json().catch(() => ({}));
                if (r.ok && data.customer) {
                    this.customers.push({ id: data.customer.id, name: data.customer.name, cin: data.customer.cin, phone: data.customer.phone, email: data.customer.email, is_flagged: data.customer.is_flagged });
                    this.customerId = String(data.customer.id);
                    this.showAddClientModal = false;
                    this.newClientName = ''; this.newClientCin = ''; this.newClientPhone = ''; this.newClientEmail = '';
                    this.newClientAddress = ''; this.newClientDrivingLicenseNumber = ''; this.newClientDrivingLicenseExpiry = '';
                    this.newClientExtractMerged = null; this.newClientExtractCin = false; this.newClientExtractLicense = false;
                } else if (r.status === 422 && data.errors) {
                    const first = Object.values(data.errors)[0];
                    this.addClientError = Array.isArray(first) ? first[0] : first;
                } else {
                    this.addClientError = data.message || 'Erreur lors de l\'ajout.';
                }
            } catch (e) { this.addClientError = 'Erreur réseau.'; }
            this.addClientLoading = false;
        },
        goStep4() {
            if (!this.customerId) { alert('Veuillez sélectionner ou créer un client.'); return; }
            const c = this.customers.find(x => x.id == this.customerId);
            if (c && c.is_flagged) { this.showFlaggedModal = true; return; }
            this.recalcPrice();
            this.step = 4;
        },
        onSubmit(e) {
            this.recalcPrice();
            if (!this.vehicleId || !this.customerId || !this.startAt || !this.endAt) {
                e.preventDefault();
                alert('Veuillez remplir toutes les étapes (dates, véhicule, client).');
                this.submitting = false;
                return;
            }
            // Ensure hidden inputs have current Alpine values before native submit
            const form = e.target;
            if (form) {
                const set = (name, val) => { const el = form.querySelector('[name="' + name + '"]'); if (el && val != null) el.value = String(val); };
                set('vehicle_id', this.vehicleId);
                set('customer_id', this.customerId);
                set('start_at', this.startAt);
                set('end_at', this.endAt);
                set('total_price', this.totalPrice);
                set('paid_now', this.paidNow ? '1' : '0');
                set('deposit_received', this.depositReceived ? '1' : '0');
                set('confirm_and_start', this.confirmAndStart ? '1' : '0');
            }
            // Allow form to submit (do not preventDefault)
        },
    };
}
</script>
<style>[x-cloak]{display:none!important}</style>
@endsection
