@php
    $branchId = old('branch_id', $preselected_branch_id ?? $branches->first()?->id);
@endphp
<div class="glm-card-static p-6 max-w-2xl">
    <h2 class="text-lg font-semibold text-white mb-4">Informations essentielles</h2>
    <p class="text-sm text-slate-400 mb-6">Vous pourrez compléter la photo, la tarification, l'assurance et le reste plus tard.</p>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="plate" class="mb-1 block text-sm font-medium text-slate-300">Plaque *</label>
            <input type="text" id="plate" name="plate" value="{{ old('plate') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="12345-A-1">
            @error('plate')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="brand" class="mb-1 block text-sm font-medium text-slate-300">Marque *</label>
            <input type="text" id="brand" name="brand" value="{{ old('brand') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Renault">
            @error('brand')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="model" class="mb-1 block text-sm font-medium text-slate-300">Modèle *</label>
            <input type="text" id="model" name="model" value="{{ old('model') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Clio">
            @error('model')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="branch_id" class="mb-1 block text-sm font-medium text-slate-300">Agence *</label>
            <select id="branch_id" name="branch_id" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" {{ (int)$branchId === (int)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
            @error('branch_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="year" class="mb-1 block text-sm font-medium text-slate-300">Année</label>
            <input type="number" id="year" name="year" value="{{ old('year') }}" min="1900" max="2100" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="2023">
            @error('year')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="partner_category" class="mb-1 block text-sm font-medium text-slate-300">Catégorie</label>
            <select id="partner_category" name="partner_category" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">– Non partagé –</option>
                @foreach (\App\Models\Vehicle::PARTNER_CATEGORIES as $key => $label)
                    <option value="{{ $key }}" {{ old('partner_category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('partner_category')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label for="daily_price" class="mb-1 block text-sm font-medium text-slate-300">Prix jour (MAD)</label>
            <input type="number" id="daily_price" name="daily_price" value="{{ old('daily_price') }}" min="0" step="0.01" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="300">
            @error('daily_price')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
