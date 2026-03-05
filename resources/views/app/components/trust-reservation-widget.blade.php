{{--
    Trust & Verification widget for "when adding reservation".
    Include with: @include('app.components.trust-reservation-widget', [
        'company' => $company,
        'trustData' => $trustData,   // TrustVerification|null
        'companyFlag' => $companyFlag, // CompanyClientFlag|null (this company's private flag)
    ])
    Optional: 'phone' => ..., 'email' => ... for display context.
--}}
<div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4 sm:p-5 space-y-4">
    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Confiance & Vérification</h3>

    {{-- Verification status (shared) --}}
    @if ($trustData ?? null)
        <div class="flex flex-wrap items-center gap-3">
            @if ($trustData->verified_identity)
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-300">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Identité vérifiée
                </span>
            @endif
            @if ($trustData->successful_rentals_count > 0)
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-blue-500/20 bg-blue-500/10 px-3 py-1.5 text-xs font-medium text-blue-300">
                    {{ $trustData->successful_rentals_count }} location(s) réussie(s)
                </span>
            @endif
        </div>
        @if (! ($trustData->verified_identity || $trustData->successful_rentals_count > 0))
            <p class="text-xs text-slate-500">Aucune donnée de confiance partagée.</p>
        @endif
    @else
        <p class="text-xs text-slate-500">Saisissez le téléphone ou l’email du client puis vérifiez pour afficher le statut.</p>
    @endif

    {{-- Internal warning: only if this company has flagged the client --}}
    @if ($companyFlag ?? null)
        <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 px-3 py-2.5">
            <p class="text-xs font-medium text-amber-300">Avertissement interne : ce client a été signalé par votre entreprise.</p>
            @if ($companyFlag->reason)
                <p class="mt-1 text-xs text-slate-400">{{ $companyFlag->reason }}</p>
            @endif
        </div>
    @endif
</div>
