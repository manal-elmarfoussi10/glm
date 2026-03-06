@extends('app.layouts.app')

@section('pageSubtitle')
{{ Str::limit($ticket->subject, 40) }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in max-w-3xl">
    <header>
        <a href="{{ route('app.support.index') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Support</a>
        <h1 class="text-2xl font-bold tracking-tight text-white">{{ $ticket->subject }}</h1>
        <p class="mt-1 text-sm text-slate-400">
            {{ $ticket->created_at->format('d/m/Y H:i') }}
            · <span class="rounded-full px-2 py-0.5 text-xs font-medium
                {{ $ticket->status === 'new' ? 'bg-blue-500/20 text-blue-400' : '' }}
                {{ $ticket->status === 'open' || $ticket->status === 'waiting' ? 'bg-amber-500/20 text-amber-400' : '' }}
                {{ $ticket->status === 'resolved' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
            ">{{ $ticket->status }}</span>
        </p>
    </header>

    <div class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Conversation</h2>
        <div class="space-y-4">
            @foreach ($ticket->replies as $reply)
                <div class="rounded-xl border border-white/10 p-4 bg-white/5">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="font-medium text-white">{{ $reply->user?->name ?? 'Support' }}</span>
                        <span class="text-xs text-slate-500">{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="text-slate-300 whitespace-pre-wrap">{{ $reply->body }}</div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($ticket->status !== 'resolved')
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Répondre</h2>
            <form action="{{ route('app.support.tickets.reply', $ticket) }}" method="post" class="space-y-4">
                @csrf
                <div>
                    <label for="body" class="mb-1 block text-sm font-medium text-slate-300">Votre message *</label>
                    <textarea id="body" name="body" rows="4" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Écrivez votre message…"></textarea>
                    @error('body')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="glm-btn-primary">Envoyer la réponse</button>
            </form>
        </div>
    @else
        <p class="text-slate-400 text-sm">Ce ticket est résolu. Pour une nouvelle question, <a href="{{ route('app.support.index') }}" class="text-[#93C5FD] hover:text-white">créez une nouvelle demande</a>.</p>
    @endif
</div>
@endsection
