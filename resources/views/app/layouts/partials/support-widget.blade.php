@auth
@php
    $user = auth()->user();
    $canCreateTicket = $user && in_array($user->role, ['company_admin', 'agent'], true) && $user->company_id;
    $isPlatformStaff = $user && in_array($user->role, ['super_admin', 'support'], true);
@endphp
{{-- Fixed support icon bottom-right: admin and company users can start a ticket --}}
<div class="fixed bottom-6 right-6 z-[100]" x-data="{ supportOpen: false }">
    {{-- Popup panel --}}
    <div x-show="supportOpen" x-cloak
         class="absolute bottom-16 right-0 w-[360px] max-w-[calc(100vw-3rem)] glm-card-static border border-white/10 shadow-2xl overflow-hidden rounded-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         @click.away="supportOpen = false">
        <div class="p-4 border-b border-white/10 flex items-center justify-between bg-white/5">
            <h3 class="font-semibold text-white">Support</h3>
            <button type="button" @click="supportOpen = false" class="text-slate-400 hover:text-white p-1" aria-label="Fermer">&times;</button>
        </div>
        <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
            @if ($canCreateTicket)
                <p class="text-sm text-slate-400">Envoyez une demande ou consultez vos tickets.</p>
                <form action="{{ route('app.support.tickets.store') }}" method="post" class="space-y-3">
                    @csrf
                    <div>
                        <label for="support_widget_subject" class="mb-0.5 block text-xs font-medium text-slate-400">Sujet</label>
                        <input type="text" id="support_widget_subject" name="subject" required maxlength="255" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white" placeholder="Objet">
                    </div>
                    <div>
                        <label for="support_widget_body" class="mb-0.5 block text-xs font-medium text-slate-400">Message</label>
                        <textarea id="support_widget_body" name="body" required rows="3" maxlength="10000" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white" placeholder="Décrivez votre question…"></textarea>
                    </div>
                    <button type="submit" class="w-full glm-btn-primary text-sm py-2">Envoyer</button>
                </form>
                <a href="{{ route('app.support.index') }}" class="block text-center text-sm text-[#93C5FD] hover:text-white no-underline">Voir toutes mes demandes</a>
            @elseif ($isPlatformStaff)
                <p class="text-sm text-slate-400">Gérez les tickets ou créez-en un.</p>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('app.inbox.index') }}" class="block w-full text-center glm-btn-primary text-sm py-2.5 no-underline">Ouvrir l'inbox</a>
                    <a href="{{ route('app.inbox.create') }}" class="block w-full text-center rounded-xl border border-white/20 bg-white/5 px-4 py-2.5 text-sm font-medium text-white hover:bg-white/10 no-underline">Créer un ticket</a>
                </div>
            @else
                <p class="text-sm text-slate-400">Contactez le support.</p>
                <a href="{{ route('app.support.index') }}" class="block text-center text-sm text-[#93C5FD] hover:text-white no-underline">Page Support</a>
            @endif
        </div>
    </div>
    {{-- Floating button: fixed bottom-right --}}
    <button type="button" @click="supportOpen = !supportOpen"
            class="flex h-14 w-14 items-center justify-center rounded-full bg-[#2563EB] text-white shadow-xl hover:bg-[#1d4ed8] hover:scale-105 transition focus:outline-none focus:ring-4 focus:ring-[#2563EB]/50"
            aria-label="Support – Créer un ticket"
            title="Support – Créer un ticket">
        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
    </button>
</div>
@endauth
