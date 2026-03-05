@extends('app.layouts.app')

@section('pageSubtitle')
Signalisations clients
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.trust.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Confiance & Vérification</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Signalisations clients</h1>
            <p class="mt-1 text-sm text-slate-400">Liste privée à votre entreprise. Aucune donnée négative partagée.</p>
        </div>
        <a href="{{ route('app.companies.trust.index', $company) }}" class="glm-btn-primary no-underline">Vérifier un client</a>
    </header>

    <div class="glm-card-static overflow-hidden p-0">
        @if ($flags->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <p>Aucune signalisation.</p>
                <a href="{{ route('app.companies.trust.index', $company) }}" class="mt-4 inline-block text-[#93C5FD] hover:text-white no-underline">Vérifier un client</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-white/5">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Identifiant</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Raison</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                            <th class="w-0 px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($flags as $flag)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 text-sm font-mono text-slate-400">{{ Str::limit($flag->client_identifier, 16) }}</td>
                                <td class="px-6 py-4 text-sm text-slate-300">{{ $flag->reason ?? '–' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $flag->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <form action="{{ route('app.companies.trust.unflag', $company) }}" method="post" class="inline">@csrf <input type="hidden" name="client_identifier" value="{{ $flag->client_identifier }}"> <button type="submit" class="text-sm text-slate-400 hover:text-red-400">Retirer</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($flags->hasPages())
                <div class="border-t border-white/10 px-6 py-4">{{ $flags->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
