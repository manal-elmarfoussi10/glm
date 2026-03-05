@extends('app.layouts.app')

@section('pageSubtitle')
Recherche partenaires – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in" x-data="partnerSearch()">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Recherche disponibilité partenaires</h1>
            <p class="mt-1 text-sm text-slate-400">Ville, dates et catégorie (Économique / Berline / SUV). Aucune donnée de réservation ni plaque n’est affichée.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('app.companies.partner-requests.index', $company) }}" class="glm-btn-secondary no-underline">Mes demandes</a>
            @if (auth()->user()?->role === 'company_admin')
                <a href="{{ route('app.companies.partner-settings.edit', $company) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white/85 hover:bg-white/10 transition no-underline">Paramètres partage</a>
            @endif
        </div>
    </header>

    <form method="get" action="{{ route('app.companies.partners.search', $company) }}" class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)] flex flex-wrap items-end gap-4">
        <div>
            <label for="city" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Ville *</label>
            <select id="city" name="city" required class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50 min-w-[180px]">
                <option value="">Choisir une ville</option>
                @foreach ($cities as $c)
                    <option value="{{ $c }}" {{ $city === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="from" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Du *</label>
            <input type="date" id="from" name="from" value="{{ $from?->format('Y-m-d') }}" required class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
        </div>
        <div>
            <label for="to" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Au *</label>
            <input type="date" id="to" name="to" value="{{ $to?->format('Y-m-d') }}" required class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
        </div>
        <div>
            <label for="category" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Catégorie *</label>
            <select id="category" name="category" required class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50 min-w-[200px]">
                @foreach (\App\Models\Vehicle::PARTNER_CATEGORIES as $key => $label)
                    <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-xl bg-gradient-to-r from-[#2563EB] to-[#2563EB]/70 px-4 py-2.5 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(37,99,235,0.22)] hover:brightness-[1.03] transition">Rechercher</button>
    </form>

    @if ($from && $to && $city && $category)
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden">
            <div class="p-4 border-b border-white/10">
                <h2 class="text-base font-extrabold text-white/90">Résultats ({{ count($results) }})</h2>
                <p class="text-sm text-slate-400">{{ $city }} · {{ $from->format('d/m/Y') }} – {{ $to->format('d/m/Y') }} · {{ \App\Models\Vehicle::PARTNER_CATEGORIES[$category] ?? $category }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-white/5">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Partenaire · Agence</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Catégorie</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Disponibles</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Prix/jour</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Contact</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($results as $r)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-white">{{ $r['company']->name }}</p>
                                    <p class="text-sm text-slate-400">{{ $r['branch']->name }} · {{ $r['branch']->city }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-300">{{ $r['category_label'] }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-semibold text-emerald-400">{{ $r['available_count'] }} véhicule(s)</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-slate-300">
                                    @if ($r['price_min'] !== null && $r['price_max'] !== null)
                                        {{ number_format($r['price_min'], 0, ',', ' ') }} – {{ number_format($r['price_max'], 0, ',', ' ') }} MAD
                                    @else
                                        –
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if ($r['company']->phone)
                                            <a href="tel:{{ preg_replace('/\s+/', '', $r['company']->phone) }}" class="inline-flex items-center gap-1 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-medium text-white hover:bg-white/20 no-underline" title="Appeler">📞 Appeler</a>
                                        @endif
                                        @php
                                            $phone = preg_replace('/\s+/', '', $r['company']->phone ?? '');
                                            $wa = $phone ? (str_starts_with($phone, '+') ? $phone : '+212' . ltrim($phone, '0')) : null;
                                        @endphp
                                        @if ($wa)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $wa) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600/80 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-600 no-underline" title="WhatsApp">WhatsApp</a>
                                        @endif
                                        <button type="button" @click="openRequest({{ json_encode([
                                            'partner_company_id' => $r['company']->id,
                                            'branch_id' => $r['branch']->id,
                                            'category' => $r['category'],
                                            'from_date' => $from->format('Y-m-d'),
                                            'to_date' => $to->format('Y-m-d'),
                                            'company_name' => $r['company']->name,
                                            'branch_name' => $r['branch']->name,
                                        ]) }})" class="inline-flex items-center gap-1 rounded-lg bg-[#2563EB]/80 px-3 py-1.5 text-xs font-medium text-white hover:bg-[#2563EB] no-underline">Envoyer une demande</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">Aucun partenaire disponible pour ces critères.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Send request modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-transition>
        <div x-show="showModal" @click.outside="showModal = false" class="rounded-2xl border border-white/10 bg-[var(--color-navy-950)] p-6 shadow-xl max-w-md w-full" x-transition>
            <h3 class="text-lg font-bold text-white mb-2">Envoyer une demande</h3>
            <p class="text-sm text-slate-400 mb-4" x-text="requestTarget ? requestTarget.company_name + ' · ' + requestTarget.branch_name : ''"></p>
            <form :action="requestUrl" method="post" class="space-y-4">
                @csrf
                <template x-if="requestTarget">
                    <div class="space-y-2">
                        <input type="hidden" name="partner_company_id" :value="requestTarget.partner_company_id">
                        <input type="hidden" name="branch_id" :value="requestTarget.branch_id">
                        <input type="hidden" name="category" :value="requestTarget.category">
                        <input type="hidden" name="from_date" :value="requestTarget.from_date">
                        <input type="hidden" name="to_date" :value="requestTarget.to_date">
                        <label class="block text-sm font-medium text-slate-300">Message (optionnel)</label>
                        <textarea name="message" rows="3" placeholder="Précisez votre besoin…" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500"></textarea>
                    </div>
                </template>
                <div class="flex gap-3">
                    <button type="submit" class="glm-btn-primary">Envoyer</button>
                    <button type="button" @click="showModal = false" class="glm-btn-secondary">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function partnerSearch() {
    return {
        showModal: false,
        requestTarget: null,
        requestUrl: '{{ route('app.companies.partner-requests.store', $company) }}',
        openRequest(payload) {
            this.requestTarget = payload;
            this.showModal = true;
        }
    };
}
</script>
@endsection
