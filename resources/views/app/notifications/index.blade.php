@extends('app.layouts.app')

@section('pageSubtitle')
Notifications
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Notifications</h1>
            <p class="mt-1 text-sm text-slate-400">Vos dernières notifications.</p>
        </div>
        @if($notifications->where('read_at', null)->count() > 0)
            <form action="{{ route('app.notifications.mark-all-read') }}" method="post" class="inline">
                @csrf
                <button type="submit" class="text-sm text-slate-400 hover:text-white">Tout marquer comme lu</button>
            </form>
        @endif
    </header>

    <div class="glm-card-static divide-y divide-white/10 p-0">
        @forelse ($notifications as $n)
            @php
                $data = $n->data;
                $title = $data['title'] ?? 'Notification';
                $body = $data['body'] ?? '';
                $url = $data['url'] ?? null;
            @endphp
            <div class="flex items-start gap-4 px-6 py-4 {{ $n->read_at ? 'opacity-75' : 'bg-white/5' }}">
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-white">{{ $title }}</p>
                    @if($body)
                        <p class="mt-0.5 text-sm text-slate-400">{{ $body }}</p>
                    @endif
                    <p class="mt-1 text-xs text-slate-500">{{ $n->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if($url)
                        <a href="{{ $url }}" class="text-sm text-[#60A5FA] hover:text-[#93C5FD] no-underline">Voir</a>
                    @endif
                    @if(!$n->read_at)
                        <form action="{{ route('app.notifications.mark-read', $n->id) }}" method="post" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-slate-500 hover:text-white">Marquer lu</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-6 py-12 text-center text-slate-400">Aucune notification.</div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
