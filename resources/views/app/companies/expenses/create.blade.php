@extends('app.layouts.app')

@section('pageSubtitle')
Nouvelle dépense – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header>
        <a href="{{ route('app.companies.expenses.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Dépenses · {{ $company->name }}</a>
        <h1 class="text-2xl font-bold tracking-tight text-white">Nouvelle dépense</h1>
        <p class="mt-1 text-sm text-slate-400">Enregistrer une dépense (optionnellement liée à un véhicule). Pièce jointe possible (facture, photo).</p>
    </header>

    <form action="{{ route('app.companies.expenses.store', $company) }}" method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Détails</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="vehicle_id" class="mb-1 block text-sm font-medium text-slate-300">Véhicule (optionnel)</label>
                    <select id="vehicle_id" name="vehicle_id" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                        <option value="">– Aucun –</option>
                        @foreach ($vehicles as $v)
                            <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->plate }} – {{ $v->brand }} {{ $v->model }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category" class="mb-1 block text-sm font-medium text-slate-300">Catégorie *</label>
                    <select id="category" name="category" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                        @foreach (\App\Models\Expense::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="amount" class="mb-1 block text-sm font-medium text-slate-300">Montant (MAD) *</label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    @error('amount')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="date" class="mb-1 block text-sm font-medium text-slate-300">Date *</label>
                    <input type="date" id="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    @error('date')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="description" class="mb-1 block text-sm font-medium text-slate-300">Note / description</label>
                    <textarea id="description" name="description" rows="2" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="attachment" class="mb-1 block text-sm font-medium text-slate-300">Pièce jointe (facture, photo)</label>
                    <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded-lg file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                    <p class="mt-1 text-xs text-slate-500">JPEG, PNG, PDF. Max 10 Mo.</p>
                    @error('attachment')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Enregistrer la dépense</button>
            <a href="{{ route('app.companies.expenses.index', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
        </div>
    </form>
</div>
@endsection
