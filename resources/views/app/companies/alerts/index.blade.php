@extends('app.layouts.app')

@section('pageSubtitle')
Alertes – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Alertes & rappels</h1>
            <p class="mt-1 text-sm text-slate-400">Conformité véhicules, réservations, paiements. Filtrez par type et gravité.</p>
        </div>
    </header>

    <form method="get" action="{{ route('app.companies.alerts.index', $company) }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div>
            <label for="type" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Type</label>
            <select id="type" name="type" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Tous</option>
                @foreach ($typeLabels as $k => $v)<option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="severity" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Gravité</label>
            <select id="severity" name="severity" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Toutes</option>
                @foreach ($severityLabels as $k => $v)<option value="{{ $k }}" {{ request('severity') === $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="glm-btn-primary">Filtrer</button>
        @if (request()->hasAny(['type', 'severity']))
            <a href="{{ route('app.companies.alerts.index', $company) }}" class="glm-btn-secondary no-underline">Réinitialiser</a>
        @endif
    </form>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    <div class="space-y-3">
        @forelse ($alerts as $a)
            @php
                $severityClass = match($a['severity']) {
                    'urgent' => 'border-red-500/30 bg-red-500/5',
                    'warning' => 'border-amber-500/30 bg-amber-500/5',
                    default => 'border-white/10',
                };
                $badgeClass = match($a['severity']) {
                    'urgent' => 'bg-red-500/20 text-red-400',
                    'warning' => 'bg-amber-500/20 text-amber-400',
                    default => 'bg-blue-500/20 text-blue-300',
                };
            @endphp
            <div class="glm-card-static p-4 {{ $severityClass }} flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">{{ $severityLabels[$a['severity']] ?? $a['severity'] }}</span>
                        <span class="text-xs text-slate-500">{{ $typeLabels[$a['type']] ?? $a['type'] }}</span>
                    </div>
                    <h3 class="mt-1 font-semibold text-white">{{ $a['title'] }}</h3>
                    <p class="text-sm text-slate-400">{{ $a['body'] }}</p>
                    @if ($a['due_at'])
                        <p class="text-xs text-slate-500 mt-1">Échéance : {{ $a['due_at']->format('d/m/Y') }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if (!empty($a['meta']['phone']))
                        @php $phone = preg_replace('/\s+/', '', $a['meta']['phone']); $phone = $phone ? (str_starts_with($phone, '+') ? $phone : '+212' . ltrim($phone, '0')) : null; @endphp
                        @if ($phone)
                            <a href="tel:{{ $phone }}" class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white hover:bg-white/10 no-underline">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V21a2 2 0 01-2 2h-1C9.716 23 3 16.284 3 8V5z" /></svg>
                                Appeler
                            </a>
                            <a href="https://wa.me/{{ str_replace('+', '', $phone) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-300 hover:bg-emerald-500/20 no-underline">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp
                            </a>
                        @endif
                    @endif
                    <a href="{{ $a['related_url'] }}" class="glm-btn-primary text-sm py-2 no-underline">Ouvrir</a>
                    <form action="{{ route('app.companies.alerts.snooze', $company) }}" method="post" class="inline">
                        @csrf
                        <input type="hidden" name="identifier" value="{{ $a['identifier'] }}">
                        <input type="hidden" name="days" value="1">
                        <button type="submit" class="glm-btn-secondary text-sm py-2">Reporter 1 j</button>
                    </form>
                    <form action="{{ route('app.companies.alerts.mark-done', $company) }}" method="post" class="inline">
                        @csrf
                        <input type="hidden" name="identifier" value="{{ $a['identifier'] }}">
                        <button type="submit" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-300 hover:bg-white/10">Marquer fait</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="glm-card-static p-12 text-center text-slate-400">
                Aucune alerte pour le moment.
            </div>
        @endforelse
    </div>
</div>
@endsection
