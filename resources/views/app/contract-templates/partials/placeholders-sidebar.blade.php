@php
    $placeholders = config('contract_placeholders.placeholders', []);
    $groups = config('contract_placeholders.groups', []);
    if (empty($groups)) {
        $groups = ['Variables' => array_keys($placeholders)];
    }
@endphp
<div class="glm-card-static p-4">
    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-3">Variables (placeholders)</h3>
    <p class="text-xs text-slate-500 mb-3">Cliquez pour insérer <code class="text-slate-400">&#123;&#123;nom&#125;&#125;</code> dans le contenu.</p>
    <div class="space-y-4 max-h-[420px] overflow-y-auto" x-data="{}">
        @foreach ($groups as $groupName => $keys)
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 mb-2">{{ $groupName }}</p>
                <ul class="space-y-1">
                    @foreach ($keys as $key)
                        @if (isset($placeholders[$key]))
                            @php
                                $info = $placeholders[$key];
                                $label = is_array($info) ? ($info['label'] ?? $key) : $info;
                                $tag = '{{' . $key . '}}';
                            @endphp
                            <li>
                                <button
                                    type="button"
                                    data-copy="{{ $tag }}"
                                    class="placeholder-copy w-full rounded-lg border border-white/5 bg-white/5 px-3 py-1.5 text-left text-sm text-slate-300 hover:bg-white/10 hover:text-white transition-colors flex items-center justify-between gap-2"
                                    title="Copier {{ $tag }}"
                                >
                                    <span class="truncate">{{ $label }}</span>
                                    <span class="shrink-0 text-xs font-mono text-slate-500">{{ $tag }}</span>
                                </button>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.placeholder-copy').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var text = this.getAttribute('data-copy');
        navigator.clipboard.writeText(text).then(function() {
            var orig = btn.innerHTML;
            btn.innerHTML = '<span class="text-emerald-400">Copié</span>';
            setTimeout(function() { btn.innerHTML = orig; }, 1200);
        });
    });
});
</script>
@endpush
