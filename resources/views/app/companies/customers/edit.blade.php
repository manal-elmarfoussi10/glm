@extends('app.layouts.app')

@section('pageSubtitle')
Modifier {{ $customer->name }} – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.customers.show', [$company, $customer]) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $customer->name }} · {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Modifier le client</h1>
            <p class="mt-1 text-sm text-slate-400">Identité, permis, documents et notes internes.</p>
        </div>
    </header>

    <form action="{{ route('app.companies.customers.update', [$company, $customer]) }}" method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Identité</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-300">Nom complet *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $customer->name) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cin" class="mb-1 block text-sm font-medium text-slate-300">CIN *</label>
                    <input type="text" id="cin" name="cin" value="{{ old('cin', $customer->cin) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    @error('cin')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-slate-300">Téléphone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-300">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label for="city" class="mb-1 block text-sm font-medium text-slate-300">Ville</label>
                    <input type="text" id="city" name="city" value="{{ old('city', $customer->city) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="mb-1 block text-sm font-medium text-slate-300">Adresse</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $customer->address) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
            </div>
        </div>

        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Permis de conduire</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="driving_license_number" class="mb-1 block text-sm font-medium text-slate-300">N° permis</label>
                    <input type="text" id="driving_license_number" name="driving_license_number" value="{{ old('driving_license_number', $customer->driving_license_number) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label for="driving_license_expiry" class="mb-1 block text-sm font-medium text-slate-300">Date d'expiration</label>
                    <input type="date" id="driving_license_expiry" name="driving_license_expiry" value="{{ old('driving_license_expiry', $customer->driving_license_expiry?->format('Y-m-d')) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
            </div>
        </div>

        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Documents (CIN + permis)</h2>
            <p class="text-sm text-slate-400 mb-4">Remplacer uniquement si besoin. Sinon, les fichiers actuels sont conservés.</p>
            <div class="grid gap-4 sm:grid-cols-1">
                <div>
                    <label for="cin_front" class="mb-1 block text-sm font-medium text-slate-300">CIN recto (PDF / image)</label>
                    @if ($customer->cin_front_path)
                        <p class="mb-1 text-xs text-slate-500">Fichier actuel : {{ basename($customer->cin_front_path) }}</p>
                    @endif
                    <input type="file" id="cin_front" name="cin_front" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
                </div>
                <div>
                    <label for="cin_back" class="mb-1 block text-sm font-medium text-slate-300">CIN verso (PDF / image)</label>
                    @if ($customer->cin_back_path)
                        <p class="mb-1 text-xs text-slate-500">Fichier actuel : {{ basename($customer->cin_back_path) }}</p>
                    @endif
                    <input type="file" id="cin_back" name="cin_back" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
                </div>
                <div>
                    <label for="license_document" class="mb-1 block text-sm font-medium text-slate-300">Permis (PDF / image)</label>
                    @if ($customer->license_document_path)
                        <p class="mb-1 text-xs text-slate-500">Fichier actuel : {{ basename($customer->license_document_path) }}</p>
                    @endif
                    <input type="file" id="license_document" name="license_document" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
                </div>
            </div>
        </div>

        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Interne (entreprise uniquement)</h2>
            <div class="space-y-4">
                <div>
                    <label for="internal_notes" class="mb-1 block text-sm font-medium text-slate-300">Notes internes</label>
                    <textarea id="internal_notes" name="internal_notes" rows="4" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">{{ old('internal_notes', $customer->internal_notes) }}</textarea>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_flagged" value="1" {{ old('is_flagged', $customer->is_flagged) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                    Client signalé (interne)
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Enregistrer</button>
            <a href="{{ route('app.companies.customers.show', [$company, $customer]) }}" class="glm-btn-secondary no-underline">Annuler</a>
        </div>
    </form>
</div>
@endsection
