@extends('app.layouts.app')

@section('pageSubtitle')
{{ Str::limit($ticket->subject, 40) }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('app.inbox.index') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Inbox</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">{{ $ticket->subject }}</h1>
            <p class="mt-1 text-sm text-slate-400">
                {{ $ticket->company?->name ?? $ticket->email ?? '–' }}
                · {{ $ticket->created_at->format('d/m/Y H:i') }}
                · <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $ticket->status === 'new' ? 'bg-blue-500/20 text-blue-400' : ($ticket->status === 'resolved' ? 'glm-badge-approved' : 'bg-amber-500/20 text-amber-400') }}">{{ $ticket->status }}</span>
            </p>
        </div>
    </header>

    {{-- Status + Assign – form --}}
    <div class="glm-card-static p-6 flex flex-wrap items-end gap-6">
        <form action="{{ route('app.inbox.update', $ticket) }}" method="post" class="flex flex-wrap items-center gap-4">
            @csrf
            @method('put')
            <div>
                <label for="status" class="mb-1 block text-xs font-medium text-slate-500">Statut</label>
                <select id="status" name="status" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white">
                    @foreach (\App\Models\Ticket::STATUSES as $s)
                        <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="assigned_to" class="mb-1 block text-xs font-medium text-slate-500">Assigné à</label>
                <select id="assigned_to" name="assigned_to" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white">
                    <option value="">– Non assigné –</option>
                    @foreach ($agents as $a)
                        <option value="{{ $a->id }}" {{ $ticket->assigned_to == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- Thread --}}
    <div class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Conversation</h2>
        <div class="space-y-4">
            @foreach ($ticket->replies as $reply)
                <div class="rounded-xl border border-white/10 p-4 {{ $reply->is_internal ? 'bg-amber-500/5 border-amber-500/20' : 'bg-white/5' }}">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="font-medium text-white">{{ $reply->user?->name ?? 'Système' }}</span>
                        <span class="text-xs text-slate-500">{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                        @if ($reply->is_internal)
                            <span class="rounded px-2 py-0.5 text-xs bg-amber-500/20 text-amber-400">Interne</span>
                        @endif
                    </div>
                    <div class="text-slate-300 whitespace-pre-wrap">{{ $reply->body }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Reply + internal note + templates --}}
    <div class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Répondre</h2>
        <form action="{{ route('app.inbox.reply', $ticket) }}" method="post" class="space-y-4">
            @csrf
            @if ($templates->isNotEmpty())
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Modèle de réponse</label>
                    <select id="template_select" class="w-full max-w-md rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white" onchange="var t=this.options[this.selectedIndex]; if(t.value) document.getElementById('body').value=t.value;">
                        <option value="">– Choisir un modèle –</option>
                        @foreach ($templates as $tpl)
                            <option value="{{ e($tpl->body) }}">{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label for="body" class="mb-1 block text-sm font-medium text-slate-300">Message</label>
                <textarea id="body" name="body" rows="5" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Votre réponse…">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-400">
                    <input type="checkbox" name="is_internal" value="1" {{ old('is_internal') ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB] focus:ring-[#2563EB]/50">
                    Note interne (visible uniquement par l’équipe)
                </label>
            </div>
            <button type="submit" class="glm-btn-primary">Envoyer la réponse</button>
        </form>
    </div>
</div>
@endsection
