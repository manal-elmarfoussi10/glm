{{-- Preview modal: expects Alpine x-data with previewOpen, previewContent, previewVersion, previewUpdated --}}
<div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div x-show="previewOpen" x-transition class="absolute inset-0 bg-black/60" @click="previewOpen = false"></div>
    <div x-show="previewOpen" x-transition class="glm-dark-bg relative z-10 w-full max-w-3xl max-h-[85vh] flex flex-col rounded-2xl border border-white/10 bg-[#1e293b] shadow-2xl">
        <div class="flex shrink-0 items-center justify-between border-b border-white/10 px-6 py-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Aperçu du contrat</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Version <span x-text="previewVersion || '–'"></span>
                    <span x-show="previewUpdated" class="ml-2">· Modifié le <span x-text="previewUpdated"></span></span>
                </p>
            </div>
            <button type="button" @click="previewOpen = false" class="rounded-lg p-2 text-slate-400 hover:bg-white/5 hover:text-white transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="flex-1 min-h-0 overflow-y-auto p-6">
            <div class="glm-light-bg contract-preview-content rounded-xl border border-white/5 bg-white/5 p-6 text-slate-200 text-sm min-h-[200px]" x-html="previewContent || '<p class=\'text-slate-500\'>Aucun contenu.</p>'"></div>
        </div>
    </div>
</div>
<style>
[x-cloak]{display:none!important}
.contract-preview-content h1{font-size:1.25rem;font-weight:700;margin-bottom:0.5rem}.contract-preview-content h2{font-size:1.1rem;font-weight:600;margin:0.75rem 0 0.25rem}.contract-preview-content p{margin-bottom:0.5rem}.contract-preview-content ul{margin:0.5rem 0;padding-left:1.25rem}
</style>
