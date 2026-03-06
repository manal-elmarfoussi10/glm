@extends('app.layouts.app')

@section('pageSubtitle')
Nouveau client – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in" x-data="customerCreateWithExtraction('{{ route('app.companies.customers.extract-documents', $company) }}')">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.customers.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Clients · {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Nouveau client</h1>
            <p class="mt-1 text-sm text-slate-400">Informations Maroc : CIN, permis, documents (recto/verso CIN, permis).</p>
        </div>
    </header>

    {{-- Upload CIN / Permis (extraction) --}}
    <div class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-2">Documents (extraction optionnelle)</h2>
        <p class="text-sm text-slate-400 mb-4">Uploadez CIN ou permis pour pré-remplir le formulaire. Les fichiers sont analysés localement et stockés de façon privée.</p>
        <div class="grid gap-4 sm:grid-cols-3">
            <div
                class="relative rounded-xl border-2 border-dashed transition-colors"
                :class="uploadState.cin_front ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-white/20 hover:border-white/40'"
                @dragover.prevent="dragOver($event, 'cin_front')"
                @dragleave.prevent="dragLeave($event, 'cin_front')"
                @drop.prevent="drop($event, 'cin_front')"
            >
                <input type="file" class="absolute inset-0 z-10 cursor-pointer opacity-0" accept=".pdf,.jpg,.jpeg,.png" @change="uploadFile($event.target, 'cin_front')" :disabled="uploading">
                <div class="flex flex-col items-center justify-center gap-2 p-6 text-center min-h-[120px]">
                    <template x-if="uploadState.cin_front">
                        <div class="flex items-center gap-2 text-emerald-400 text-sm">
                            <span x-text="uploadState.cin_front.name"></span>
                            <span class="text-slate-500">· Analysé</span>
                        </div>
                    </template>
                    <template x-if="!uploadState.cin_front">
                        <span class="text-sm text-slate-400">CIN recto · Déposez ou cliquez</span>
                    </template>
                </div>
            </div>
            <div
                class="relative rounded-xl border-2 border-dashed transition-colors"
                :class="uploadState.cin_back ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-white/20 hover:border-white/40'"
                @dragover.prevent="dragOver($event, 'cin_back')"
                @dragleave.prevent="dragLeave($event, 'cin_back')"
                @drop.prevent="drop($event, 'cin_back')"
            >
                <input type="file" class="absolute inset-0 z-10 cursor-pointer opacity-0" accept=".pdf,.jpg,.jpeg,.png" @change="uploadFile($event.target, 'cin_back')" :disabled="uploading">
                <div class="flex flex-col items-center justify-center gap-2 p-6 text-center min-h-[120px]">
                    <template x-if="uploadState.cin_back">
                        <div class="flex items-center gap-2 text-emerald-400 text-sm">
                            <span x-text="uploadState.cin_back.name"></span>
                            <span class="text-slate-500">· Analysé</span>
                        </div>
                    </template>
                    <template x-if="!uploadState.cin_back">
                        <span class="text-sm text-slate-400">CIN verso · Déposez ou cliquez</span>
                    </template>
                </div>
            </div>
            <div
                class="relative rounded-xl border-2 border-dashed transition-colors"
                :class="uploadState.license ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-white/20 hover:border-white/40'"
                @dragover.prevent="dragOver($event, 'license')"
                @dragleave.prevent="dragLeave($event, 'license')"
                @drop.prevent="drop($event, 'license')"
            >
                <input type="file" class="absolute inset-0 z-10 cursor-pointer opacity-0" accept=".pdf,.jpg,.jpeg,.png" @change="uploadFile($event.target, 'license')" :disabled="uploading">
                <div class="flex flex-col items-center justify-center gap-2 p-6 text-center min-h-[120px]">
                    <template x-if="uploadState.license">
                        <div class="flex items-center gap-2 text-emerald-400 text-sm">
                            <span x-text="uploadState.license.name"></span>
                            <span class="text-slate-500">· Analysé</span>
                        </div>
                    </template>
                    <template x-if="!uploadState.license">
                        <span class="text-sm text-slate-400">Permis · Déposez ou cliquez</span>
                    </template>
                </div>
            </div>
        </div>
        <p x-show="uploading" class="mt-2 text-sm text-slate-400">Extraction en cours…</p>
        <p x-show="uploadError" class="mt-2 text-sm text-red-400" x-text="uploadError"></p>
    </div>

    {{-- Données détectées + Pré-remplir --}}
    <div x-show="hasMergedData()" x-cloak class="glm-card-static p-6 border border-[#2563EB]/30 bg-[#2563EB]/5">
        <h2 class="text-lg font-semibold text-white mb-3">Données détectées</h2>
        <p class="text-sm text-slate-400 mb-4">Vérifiez et corrigez si besoin, puis pré-remplissez le formulaire.</p>
        <dl class="grid gap-2 sm:grid-cols-2 text-sm mb-4">
            <template x-if="merged.name">
                <div><dt class="text-slate-500">Nom</dt><dd class="text-white font-medium" x-text="merged.name"></dd></div>
            </template>
            <template x-if="merged.cin">
                <div><dt class="text-slate-500">CIN</dt><dd class="text-white font-medium" x-text="merged.cin"></dd></div>
            </template>
            <template x-if="merged.address">
                <div class="sm:col-span-2"><dt class="text-slate-500">Adresse</dt><dd class="text-white" x-text="merged.address"></dd></div>
            </template>
            <template x-if="merged.driving_license_number">
                <div><dt class="text-slate-500">N° permis</dt><dd class="text-white font-medium" x-text="merged.driving_license_number"></dd></div>
            </template>
            <template x-if="merged.driving_license_expiry">
                <div><dt class="text-slate-500">Expiration permis</dt><dd class="text-white font-medium" x-text="merged.driving_license_expiry"></dd></div>
            </template>
        </dl>
        <button type="button" @click="prefillForm()" class="glm-btn-primary">Pré-remplir le formulaire</button>
    </div>

    <form action="{{ route('app.companies.customers.store', $company) }}" method="post" enctype="multipart/form-data" class="space-y-6" id="customer-form">
        @csrf
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Identité</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-300">Nom complet *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cin" class="mb-1 block text-sm font-medium text-slate-300">CIN *</label>
                    <input type="text" id="cin" name="cin" value="{{ old('cin') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Carte d'identité nationale">
                    @error('cin')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-slate-300">Téléphone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-300">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label for="city" class="mb-1 block text-sm font-medium text-slate-300">Ville</label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="mb-1 block text-sm font-medium text-slate-300">Adresse</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
            </div>
        </div>

        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Permis de conduire</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="driving_license_number" class="mb-1 block text-sm font-medium text-slate-300">N° permis</label>
                    <input type="text" id="driving_license_number" name="driving_license_number" value="{{ old('driving_license_number') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label for="driving_license_expiry" class="mb-1 block text-sm font-medium text-slate-300">Date d'expiration</label>
                    <input type="date" id="driving_license_expiry" name="driving_license_expiry" value="{{ old('driving_license_expiry') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                </div>
            </div>
        </div>

        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Documents (CIN + permis)</h2>
            <div class="grid gap-4 sm:grid-cols-1">
                <div>
                    <label for="cin_front" class="mb-1 block text-sm font-medium text-slate-300">CIN recto (PDF / image)</label>
                    <input type="file" id="cin_front" name="cin_front" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
                </div>
                <div>
                    <label for="cin_back" class="mb-1 block text-sm font-medium text-slate-300">CIN verso (PDF / image)</label>
                    <input type="file" id="cin_back" name="cin_back" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
                </div>
                <div>
                    <label for="license_document" class="mb-1 block text-sm font-medium text-slate-300">Permis (PDF / image)</label>
                    <input type="file" id="license_document" name="license_document" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white file:mr-4 file:rounded file:border-0 file:bg-[#2563EB] file:px-4 file:py-2 file:text-sm file:text-white">
                </div>
            </div>
        </div>

        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Interne (entreprise uniquement)</h2>
            <div class="space-y-4">
                <div>
                    <label for="internal_notes" class="mb-1 block text-sm font-medium text-slate-300">Notes internes</label>
                    <textarea id="internal_notes" name="internal_notes" rows="4" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="Notes visibles uniquement par votre entreprise…">{{ old('internal_notes') }}</textarea>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_flagged" value="1" {{ old('is_flagged') ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                    Client signalé (interne)
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Créer le client</button>
            <a href="{{ route('app.companies.customers.index', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('customerCreateWithExtraction', function (extractUrl) {
        return {
            extractUrl: extractUrl,
            uploadState: { cin_front: null, cin_back: null, license: null },
            merged: {},
            uploading: false,
            uploadError: null,

            dragOver(e, type) { e.currentTarget.classList.add('border-[#2563EB]/50'); },
            dragLeave(e, type) { if (e && e.currentTarget) e.currentTarget.classList.remove('border-[#2563EB]/50'); },

            drop(e, type) {
                e.currentTarget.classList.remove('border-[#2563EB]/50');
                var f = e.dataTransfer?.files?.[0];
                if (f) this.doUpload(f, type);
            },

            uploadFile(input, type) {
                var f = input?.files?.[0];
                if (f) this.doUpload(f, type);
                input.value = '';
            },

            doUpload(file, type) {
                var self = this;
                self.uploadError = null;
                self.uploading = true;
                var fd = new FormData();
                fd.append('file', file);
                fd.append('type', type);
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                fetch(self.extractUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(function (r) {
                        if (!r.ok) throw new Error('Erreur lors de l\'extraction');
                        return r.json();
                    })
                    .then(function (data) {
                        self.uploadState[type] = { name: data.filename || file.name };
                        self.merged = data.merged || {};
                        self.uploading = false;
                    })
                    .catch(function (err) {
                        self.uploadError = err.message || 'Erreur réseau';
                        self.uploading = false;
                    });
            },

            hasMergedData() {
                var m = this.merged;
                return !!(m.name || m.cin || m.address || m.driving_license_number || m.driving_license_expiry);
            },

            prefillForm() {
                var m = this.merged;
                var set = function (id, val) {
                    var el = document.getElementById(id);
                    if (el && val != null && val !== '') el.value = val;
                };
                set('name', m.name);
                set('cin', m.cin);
                set('address', m.address);
                set('driving_license_number', m.driving_license_number);
                set('driving_license_expiry', m.driving_license_expiry);
            }
        };
    });
});
</script>
@endpush
@endsection
