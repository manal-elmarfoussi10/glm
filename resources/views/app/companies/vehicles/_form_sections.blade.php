@php
    $v = $vehicle ?? null;
    $val = function($key, $default = '') use ($v) { return old($key, $v ? $v->$key : $default); };
    $branchId = old('branch_id', $v ? $v->branch_id : ($preselected_branch_id ?? $branches->first()?->id));
@endphp

{{-- 0. Photo --}}
<div id="section-photo" class="glm-card-static p-6">
    <h2 class="text-lg font-semibold text-white mb-4">Photo du véhicule</h2>
    <div class="flex flex-wrap items-start gap-6">
        @if ($v && $v->image_path)
            <div class="shrink-0">
                <img src="{{ asset('storage/' . $v->image_path) }}" alt="{{ $v->plate }}" class="h-40 w-auto rounded-xl border border-white/10 object-cover">
                <p class="mt-1 text-xs text-slate-500">Photo actuelle</p>
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <label for="image" class="mb-1 block text-sm font-medium text-slate-300">Image (JPG, PNG, WebP – max 5 Mo)</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
            @error('image')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- 1. Identification --}}
<div class="glm-card-static p-6">
    <h2 class="text-lg font-semibold text-white mb-4">Identification</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <label for="plate" class="mb-1 block text-sm font-medium text-slate-300">Plaque *</label>
            <input type="text" id="plate" name="plate" value="{{ $val('plate') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
            @error('plate')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="brand" class="mb-1 block text-sm font-medium text-slate-300">Marque *</label>
            <input type="text" id="brand" name="brand" value="{{ $val('brand') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
            @error('brand')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="model" class="mb-1 block text-sm font-medium text-slate-300">Modèle *</label>
            <input type="text" id="model" name="model" value="{{ $val('model') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
            @error('model')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="partner_category" class="mb-1 block text-sm font-medium text-slate-300">Catégorie partenaire</label>
            <select id="partner_category" name="partner_category" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">– Non partagé –</option>
                @foreach (\App\Models\Vehicle::PARTNER_CATEGORIES as $key => $label)
                    <option value="{{ $key }}" {{ $val('partner_category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Pour la recherche partenaires (Économique / Berline / SUV).</p>
        </div>
        <div>
            <label for="year" class="mb-1 block text-sm font-medium text-slate-300">Année</label>
            <input type="number" id="year" name="year" value="{{ $val('year') }}" min="1900" max="2100" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="vin" class="mb-1 block text-sm font-medium text-slate-300">VIN</label>
            <input type="text" id="vin" name="vin" value="{{ $val('vin') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="fuel" class="mb-1 block text-sm font-medium text-slate-300">Carburant</label>
            <select id="fuel" name="fuel" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">–</option>
                <option value="essence" {{ $val('fuel') === 'essence' ? 'selected' : '' }}>Essence</option>
                <option value="diesel" {{ $val('fuel') === 'diesel' ? 'selected' : '' }}>Diesel</option>
                <option value="hybrid" {{ $val('fuel') === 'hybrid' ? 'selected' : '' }}>Hybride</option>
                <option value="electric" {{ $val('fuel') === 'electric' ? 'selected' : '' }}>Électrique</option>
            </select>
        </div>
        <div>
            <label for="transmission" class="mb-1 block text-sm font-medium text-slate-300">Transmission</label>
            <select id="transmission" name="transmission" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">–</option>
                <option value="manual" {{ $val('transmission') === 'manual' ? 'selected' : '' }}>Manuelle</option>
                <option value="automatic" {{ $val('transmission') === 'automatic' ? 'selected' : '' }}>Automatique</option>
            </select>
        </div>
        <div>
            <label for="mileage" class="mb-1 block text-sm font-medium text-slate-300">Kilométrage</label>
            <input type="number" id="mileage" name="mileage" value="{{ $val('mileage') }}" min="0" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="color" class="mb-1 block text-sm font-medium text-slate-300">Couleur</label>
            <input type="text" id="color" name="color" value="{{ $val('color') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="seats" class="mb-1 block text-sm font-medium text-slate-300">Places</label>
            <input type="number" id="seats" name="seats" value="{{ $val('seats') }}" min="1" max="99" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="branch_id" class="mb-1 block text-sm font-medium text-slate-300">Agence *</label>
            <select id="branch_id" name="branch_id" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" {{ (int)$branchId === (int)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-slate-300">Statut</label>
            <select id="status" name="status" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="available" {{ $val('status', 'available') === 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="maintenance" {{ $val('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="inactive" {{ $val('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
            </select>
        </div>
    </div>
</div>

{{-- 2. Pricing --}}
<div id="section-pricing" class="glm-card-static p-6">
    <h2 class="text-lg font-semibold text-white mb-4">Tarification</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="daily_price" class="mb-1 block text-sm font-medium text-slate-300">Prix jour (MAD)</label>
            <input type="number" id="daily_price" name="daily_price" value="{{ $val('daily_price') }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="weekly_price" class="mb-1 block text-sm font-medium text-slate-300">Prix semaine (MAD)</label>
            <input type="number" id="weekly_price" name="weekly_price" value="{{ $val('weekly_price') }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="monthly_price" class="mb-1 block text-sm font-medium text-slate-300">Prix mois (MAD)</label>
            <input type="number" id="monthly_price" name="monthly_price" value="{{ $val('monthly_price') }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="deposit" class="mb-1 block text-sm font-medium text-slate-300">Caution (MAD)</label>
            <input type="number" id="deposit" name="deposit" value="{{ $val('deposit') }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
    </div>
</div>

{{-- 3. Compliance Morocco --}}
<div class="glm-card-static p-6">
    <h2 class="text-lg font-semibold text-white mb-4">Conformité (Maroc)</h2>

    <div class="space-y-6">
        <div id="section-insurance" class="border-b border-white/10 pb-6">
            <h3 class="text-base font-medium text-slate-200 mb-3">Assurance</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div><label for="insurance_company" class="mb-1 block text-xs font-medium text-slate-500">Compagnie</label><input type="text" id="insurance_company" name="insurance_company" value="{{ $val('insurance_company') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div><label for="insurance_policy_number" class="mb-1 block text-xs font-medium text-slate-500">N° police</label><input type="text" id="insurance_policy_number" name="insurance_policy_number" value="{{ $val('insurance_policy_number') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div><label for="insurance_type" class="mb-1 block text-xs font-medium text-slate-500">Type (RC, RC+tierce, tous risques…)</label><input type="text" id="insurance_type" name="insurance_type" value="{{ $val('insurance_type') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div><label for="insurance_start_date" class="mb-1 block text-xs font-medium text-slate-500">Début</label><input type="date" id="insurance_start_date" name="insurance_start_date" value="{{ $v && $v->insurance_start_date ? $v->insurance_start_date->format('Y-m-d') : old('insurance_start_date') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div><label for="insurance_end_date" class="mb-1 block text-xs font-medium text-slate-500">Fin</label><input type="date" id="insurance_end_date" name="insurance_end_date" value="{{ $v && $v->insurance_end_date ? $v->insurance_end_date->format('Y-m-d') : old('insurance_end_date') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div><label for="insurance_annual_cost" class="mb-1 block text-xs font-medium text-slate-500">Coût annuel (MAD)</label><input type="number" id="insurance_annual_cost" name="insurance_annual_cost" value="{{ $val('insurance_annual_cost') }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div class="sm:col-span-2"><label for="insurance_document" class="mb-1 block text-xs font-medium text-slate-500">Document (PDF/image)</label><input type="file" id="insurance_document" name="insurance_document" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">@if($v && $v->insurance_document_path)<p class="mt-1 text-xs text-slate-500">Actuel : {{ basename($v->insurance_document_path) }}</p>@endif</div>
                <div class="flex items-center"><label class="inline-flex items-center gap-2 text-sm text-slate-400"><input type="checkbox" name="insurance_reminder" value="1" {{ $val('insurance_reminder', true) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]"> Rappel expiration</label></div>
            </div>
        </div>

        <div id="section-vignette" class="border-b border-white/10 pb-6">
            <h3 class="text-base font-medium text-slate-200 mb-3">Vignette</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div><label for="vignette_year" class="mb-1 block text-xs font-medium text-slate-500">Année</label><input type="number" id="vignette_year" name="vignette_year" value="{{ $val('vignette_year') }}" min="2000" max="2100" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div><label for="vignette_amount" class="mb-1 block text-xs font-medium text-slate-500">Montant (MAD)</label><input type="number" id="vignette_amount" name="vignette_amount" value="{{ $val('vignette_amount') }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div><label for="vignette_paid_date" class="mb-1 block text-xs font-medium text-slate-500">Date paiement</label><input type="date" id="vignette_paid_date" name="vignette_paid_date" value="{{ $v && $v->vignette_paid_date ? $v->vignette_paid_date->format('Y-m-d') : old('vignette_paid_date') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div class="sm:col-span-2"><label for="vignette_receipt" class="mb-1 block text-xs font-medium text-slate-500">Reçu (PDF/image)</label><input type="file" id="vignette_receipt" name="vignette_receipt" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">@if($v && $v->vignette_receipt_path)<p class="mt-1 text-xs text-slate-500">Actuel : {{ basename($v->vignette_receipt_path) }}</p>@endif</div>
                <div class="flex items-center"><label class="inline-flex items-center gap-2 text-sm text-slate-400"><input type="checkbox" name="vignette_reminder" value="1" {{ $val('vignette_reminder', true) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]"> Rappel annuel</label></div>
            </div>
        </div>

        <div id="section-visite">
            <h3 class="text-base font-medium text-slate-200 mb-3">Visite technique</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div><label for="visite_last_date" class="mb-1 block text-xs font-medium text-slate-500">Dernière date</label><input type="date" id="visite_last_date" name="visite_last_date" value="{{ $v && $v->visite_last_date ? $v->visite_last_date->format('Y-m-d') : old('visite_last_date') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div><label for="visite_expiry_date" class="mb-1 block text-xs font-medium text-slate-500">Expiration</label><input type="date" id="visite_expiry_date" name="visite_expiry_date" value="{{ $v && $v->visite_expiry_date ? $v->visite_expiry_date->format('Y-m-d') : old('visite_expiry_date') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
                <div class="sm:col-span-2"><label for="visite_document" class="mb-1 block text-xs font-medium text-slate-500">Document</label><input type="file" id="visite_document" name="visite_document" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">@if($v && $v->visite_document_path)<p class="mt-1 text-xs text-slate-500">Actuel : {{ basename($v->visite_document_path) }}</p>@endif</div>
                <div class="flex items-center"><label class="inline-flex items-center gap-2 text-sm text-slate-400"><input type="checkbox" name="visite_reminder" value="1" {{ $val('visite_reminder', true) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]"> Rappel</label></div>
            </div>
        </div>
    </div>
</div>

{{-- 4. Financing --}}
<div id="section-financing" class="glm-card-static p-6" x-data="{ isFinanced: {{ $val('is_financed') ? 'true' : 'false' }} }">
    <h2 class="text-lg font-semibold text-white mb-4">Financement</h2>
    <label class="inline-flex items-center gap-2 text-sm text-slate-300 mb-4">
        <input type="checkbox" name="is_financed" value="1" x-model="isFinanced" class="rounded border-white/20 bg-white/5 text-[#2563EB]">
        Véhicule financé (crédit / leasing)
    </label>
    <div x-show="isFinanced" x-cloak class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div><label for="financing_type" class="mb-1 block text-xs font-medium text-slate-500">Type</label><select id="financing_type" name="financing_type" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"><option value="">–</option><option value="credit" {{ $val('financing_type') === 'credit' ? 'selected' : '' }}>Crédit</option><option value="leasing" {{ $val('financing_type') === 'leasing' ? 'selected' : '' }}>Leasing</option></select></div>
        <div><label for="financing_bank" class="mb-1 block text-xs font-medium text-slate-500">Banque</label><input type="text" id="financing_bank" name="financing_bank" value="{{ $val('financing_bank') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
        <div><label for="financing_monthly_payment" class="mb-1 block text-xs font-medium text-slate-500">Mensualité (MAD)</label><input type="number" id="financing_monthly_payment" name="financing_monthly_payment" value="{{ $val('financing_monthly_payment') }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
        <div><label for="financing_start_date" class="mb-1 block text-xs font-medium text-slate-500">Début</label><input type="date" id="financing_start_date" name="financing_start_date" value="{{ $v && $v->financing_start_date ? $v->financing_start_date->format('Y-m-d') : old('financing_start_date') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
        <div><label for="financing_end_date" class="mb-1 block text-xs font-medium text-slate-500">Fin</label><input type="date" id="financing_end_date" name="financing_end_date" value="{{ $v && $v->financing_end_date ? $v->financing_end_date->format('Y-m-d') : old('financing_end_date') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
        <div><label for="financing_remaining_amount" class="mb-1 block text-xs font-medium text-slate-500">Montant restant (MAD)</label><input type="number" id="financing_remaining_amount" name="financing_remaining_amount" value="{{ $val('financing_remaining_amount') }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></div>
        <div class="sm:col-span-2"><label for="financing_contract" class="mb-1 block text-xs font-medium text-slate-500">Contrat (PDF/image)</label><input type="file" id="financing_contract" name="financing_contract" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">@if($v && $v->financing_contract_path)<p class="mt-1 text-xs text-slate-500">Actuel : {{ basename($v->financing_contract_path) }}</p>@endif</div>
    </div>
</div>
