@extends('app.layouts.app')

@section('pageSubtitle')
Demandes partenaires – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Demandes partenaires</h1>
            <p class="mt-1 text-sm text-slate-400">Demandes reçues et envoyées. La communication se fait par téléphone ou WhatsApp.</p>
        </div>
        <a href="{{ route('app.companies.partners.search', $company) }}" class="glm-btn-primary no-underline">Rechercher des partenaires</a>
    </header>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden">
            <div class="p-4 border-b border-white/10">
                <h2 class="text-base font-extrabold text-white/90">Reçues</h2>
            </div>
            <div class="divide-y divide-white/10">
                @forelse ($received as $req)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-white">{{ $req->requesterCompany->name ?? '–' }}</p>
                                <p class="text-sm text-slate-400">{{ $req->from_date->format('d/m/Y') }} – {{ $req->to_date->format('d/m/Y') }} @if($req->category) · {{ \App\Models\Vehicle::PARTNER_CATEGORIES[$req->category] ?? $req->category }} @endif</p>
                                @if ($req->branch)
                                    <p class="text-xs text-slate-500">{{ $req->branch->name }} · {{ $req->branch->city }}</p>
                                @endif
                                @if ($req->message)
                                    <p class="mt-2 text-sm text-slate-300">« {{ Str::limit($req->message, 80) }} »</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if ($req->status === \App\Models\PartnerRequest::STATUS_PENDING)
                                    @if (auth()->user()?->role === 'company_admin')
                                        <form action="{{ route('app.companies.partner-requests.accept', [$company, $req]) }}" method="post" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium">Accepter</button>
                                        </form>
                                        <form action="{{ route('app.companies.partner-requests.reject', [$company, $req]) }}" method="post" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-sm text-red-400 hover:text-red-300 font-medium">Refuser</button>
                                        </form>
                                    @else
                                        <span class="text-slate-500 text-xs">En attente</span>
                                    @endif
                                @else
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $req->status === \App\Models\PartnerRequest::STATUS_ACCEPTED ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                                        {{ $req->status === \App\Models\PartnerRequest::STATUS_ACCEPTED ? 'Acceptée' : 'Refusée' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if ($req->requesterCompany && ($req->requesterCompany->phone || $req->requesterCompany->email))
                            <div class="mt-2 flex flex-wrap gap-2">
                                @if ($req->requesterCompany->phone)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $req->requesterCompany->phone) }}" class="text-xs text-[#93C5FD] hover:underline">📞 {{ $req->requesterCompany->phone }}</a>
                                @endif
                                @if ($req->requesterCompany->email)
                                    <a href="mailto:{{ $req->requesterCompany->email }}" class="text-xs text-[#93C5FD] hover:underline">{{ $req->requesterCompany->email }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500 text-sm">Aucune demande reçue.</div>
                @endforelse
            </div>
            @if ($received->hasPages())
                <div class="p-4 border-t border-white/10">{{ $received->links() }}</div>
            @endif
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden">
            <div class="p-4 border-b border-white/10">
                <h2 class="text-base font-extrabold text-white/90">Envoyées</h2>
            </div>
            <div class="divide-y divide-white/10">
                @forelse ($sent as $req)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-white">{{ $req->partnerCompany->name ?? '–' }}</p>
                                <p class="text-sm text-slate-400">{{ $req->from_date->format('d/m/Y') }} – {{ $req->to_date->format('d/m/Y') }} @if($req->category) · {{ \App\Models\Vehicle::PARTNER_CATEGORIES[$req->category] ?? $req->category }} @endif</p>
                                @if ($req->branch)
                                    <p class="text-xs text-slate-500">{{ $req->branch->name }} · {{ $req->branch->city }}</p>
                                @endif
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium shrink-0
                                {{ $req->status === \App\Models\PartnerRequest::STATUS_PENDING ? 'bg-amber-500/20 text-amber-400' : ($req->status === \App\Models\PartnerRequest::STATUS_ACCEPTED ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400') }}">
                                {{ $req->status === \App\Models\PartnerRequest::STATUS_PENDING ? 'En attente' : ($req->status === \App\Models\PartnerRequest::STATUS_ACCEPTED ? 'Acceptée' : 'Refusée') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500 text-sm">Aucune demande envoyée.</div>
                @endforelse
            </div>
            @if ($sent->hasPages())
                <div class="p-4 border-t border-white/10">{{ $sent->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
