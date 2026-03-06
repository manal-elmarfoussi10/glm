@extends('app.layouts.app')

@section('pageSubtitle')
Support
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header>
        <h1 class="text-3xl font-bold tracking-tight text-white">Support</h1>
        <p class="mt-2 text-slate-400">Besoin d'aide ? Créez un ticket ou consultez vos demandes.</p>
    </header>

    @if ($canCreateTicket)
        <div class="glm-card-static p-6 max-w-2xl">
            <h2 class="text-lg font-semibold text-white mb-4">Nouvelle demande</h2>
            <form action="{{ route('app.support.tickets.store') }}" method="post" class="space-y-4">
                @csrf
                <div>
                    <label for="subject" class="mb-1 block text-sm font-medium text-slate-300">Sujet *</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Objet de votre demande">
                    @error('subject')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="body" class="mb-1 block text-sm font-medium text-slate-300">Message *</label>
                    <textarea id="body" name="body" rows="4" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Décrivez votre question ou problème…">{{ old('body') }}</textarea>
                    @error('body')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="glm-btn-primary">Envoyer la demande</button>
            </form>
        </div>
    @endif

    @if ($isPlatformStaff)
        <div class="glm-card-static p-6 max-w-2xl border-[#2563EB]/30 bg-[#2563EB]/5">
            <p class="text-slate-300">Vous êtes membre de l'équipe support. Gérez les tickets depuis l'inbox.</p>
            <a href="{{ route('app.inbox.index') }}" class="inline-flex mt-3 glm-btn-primary no-underline">Ouvrir l'inbox</a>
        </div>
    @endif

    @if ($canCreateTicket && $tickets->isNotEmpty())
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Mes demandes</h2>
            <ul class="space-y-3">
                @foreach ($tickets as $t)
                    <li class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                        <a href="{{ route('app.support.tickets.show', $t) }}" class="font-medium text-white hover:text-[#93C5FD] no-underline flex-1 min-w-0 truncate">{{ $t->subject }}</a>
                        <span class="text-xs text-slate-500 shrink-0">{{ $t->updated_at->format('d/m/Y H:i') }}</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium shrink-0
                            {{ $t->status === 'new' ? 'bg-blue-500/20 text-blue-400' : '' }}
                            {{ $t->status === 'open' || $t->status === 'waiting' ? 'bg-amber-500/20 text-amber-400' : '' }}
                            {{ $t->status === 'resolved' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                        ">{{ $t->status }}</span>
                    </li>
                @endforeach
            </ul>
            @if ($tickets->hasPages())
                <div class="mt-4">{{ $tickets->links() }}</div>
            @endif
        </div>
    @elseif ($canCreateTicket)
        <div class="glm-card-static p-6 text-slate-400 text-sm">
            <p>Vous n'avez pas encore de demande. Utilisez le formulaire ci-dessus pour contacter le support.</p>
        </div>
    @endif

    @if (!$canCreateTicket && !$isPlatformStaff)
        <div class="glm-card-static p-8 max-w-2xl">
            <p class="text-slate-300">Pour contacter le support, envoyez-nous un email à :</p>
            <p class="mt-2">
                <a href="mailto:{{ config('mail.from.address', 'support@glm.com') }}" class="text-[#60A5FA] hover:text-[#93C5FD] font-medium no-underline">{{ config('mail.from.address', 'support@glm.com') }}</a>
            </p>
        </div>
    @endif
</div>
@endsection
