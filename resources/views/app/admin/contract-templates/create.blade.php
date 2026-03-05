@extends('app.layouts.app')

@section('pageSubtitle')
Nouveau modèle global
@endsection

@section('content')
<div class="space-y-8 glm-fade-in" x-data="contractTemplateForm({{ json_encode(['sample_data' => $sampleData ?? []]) }})">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.admin.contract-templates.index') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block transition-colors">← Bibliothèque de contrats</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Nouveau modèle global</h1>
            <p class="mt-1 text-sm text-slate-400">Ce modèle pourra être dupliqué par les entreprises. Les mises à jour globales n’écrasent pas leurs copies.</p>
        </div>
    </div>

    <form action="{{ route('app.admin.contract-templates.store') }}" method="post" class="space-y-6">
        @csrf
        <div class="grid gap-6 lg:grid-cols-[1fr_280px]">
            <div class="glm-card-static p-6 space-y-6">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-slate-300">Nom du modèle <span class="text-red-400">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Ex. Contrat de location standard">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="slug" class="mb-1.5 block text-sm font-medium text-slate-300">Slug <span class="text-red-400">*</span></label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}" pattern="[a-z0-9\-]+" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white font-mono placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="contrat-location-standard">
                    <p class="mt-1 text-xs text-slate-500">Lettres minuscules, chiffres et tirets uniquement.</p>
                    @error('slug')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="version" class="mb-1.5 block text-sm font-medium text-slate-300">Version</label>
                    <input type="text" id="version" name="version" value="{{ old('version', '1.0') }}" class="w-24 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50" placeholder="1.0">
                </div>
                <div>
                    <label for="content" class="mb-1.5 block text-sm font-medium text-slate-300">Contenu HTML</label>
                    <textarea id="content" name="content" rows="18" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white font-mono placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="<h1>Contrat</h1>&#10;<p>Client : @{{ client_name }}</p>">{{ old('content') }}</textarea>
                    @error('content')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="openPreview()" class="glm-btn-secondary">Aperçu</button>
                    <a href="{{ route('app.admin.contract-templates.index') }}" class="glm-btn-secondary no-underline">Annuler</a>
                    <button type="submit" class="glm-btn-primary">Créer le modèle</button>
                </div>
            </div>
            <div>
                @include('app.contract-templates.partials.placeholders-sidebar')
            </div>
        </div>
    </form>

    @include('app.contract-templates.partials.preview-modal')
</div>

@push('scripts')
<script>
function contractTemplateForm(meta) {
    meta = meta || {};
    return {
        previewOpen: false,
        previewContent: '',
        previewVersion: '–',
        previewUpdated: '',
        sampleData: meta.sample_data || {},
        openPreview() {
            var ta = document.getElementById('content');
            var raw = (ta && ta.value) || '';
            var sample = this.sampleData || {};
            var html = raw.replace(/\{\{\s*(\w+)\s*\}\}/g, function(_, key) {
                return sample[key] !== undefined ? sample[key] : '[?]';
            });
            this.previewContent = html || '<p class="text-slate-500">Aucun contenu.</p>';
            this.previewVersion = document.getElementById('version')?.value || '–';
            this.previewUpdated = '';
            this.previewOpen = true;
        }
    };
}
</script>
@endpush
@endsection
