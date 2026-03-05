@extends('app.layouts.app')

@section('pageSubtitle')
Nouveau ticket
@endsection

@section('content')
<div class="space-y-8 glm-fade-in max-w-2xl">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Nouveau ticket</h1>
            <p class="mt-2 text-slate-400">Créer un ticket manuellement (MVP sans email).</p>
        </div>
        <a href="{{ route('app.inbox.index') }}" class="glm-btn-secondary no-underline">Retour à l’inbox</a>
    </header>

    <form action="{{ route('app.inbox.store') }}" method="post" class="glm-card-static space-y-6 p-6">
        @csrf
        <div>
            <label for="subject" class="mb-1.5 block text-sm font-medium text-slate-300">Sujet *</label>
            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Objet du ticket">
            @error('subject')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="company_id" class="mb-1.5 block text-sm font-medium text-slate-300">Entreprise</label>
            <select id="company_id" name="company_id" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50">
                <option value="">– Aucune –</option>
                @foreach ($companies as $c)
                    <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="user_id" class="mb-1.5 block text-sm font-medium text-slate-300">Utilisateur (requérant)</label>
            <select id="user_id" name="user_id" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50">
                <option value="">– Aucun –</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Optionnel. Choisir après une entreprise si besoin.</p>
        </div>
        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-slate-300">Email (si pas d’utilisateur)</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50" placeholder="contact@exemple.com">
        </div>
        <div>
            <label for="body" class="mb-1.5 block text-sm font-medium text-slate-300">Message initial *</label>
            <textarea id="body" name="body" rows="5" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Contenu du premier message">{{ old('body') }}</textarea>
            @error('body')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="assigned_to" class="mb-1.5 block text-sm font-medium text-slate-300">Assigner à</label>
            <select id="assigned_to" name="assigned_to" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-white focus:ring-2 focus:ring-[#2563EB]/50">
                <option value="">– Non assigné –</option>
                @foreach ($agents as $a)
                    <option value="{{ $a->id }}" {{ old('assigned_to') == $a->id ? 'selected' : '' }}>{{ $a->name }} ({{ $a->role }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Créer le ticket</button>
            <a href="{{ route('app.inbox.index') }}" class="glm-btn-secondary no-underline">Annuler</a>
        </div>
    </form>
</div>
@endsection
