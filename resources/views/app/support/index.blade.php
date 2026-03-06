@extends('app.layouts.app')

@section('pageSubtitle')
Support
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header>
        <h1 class="text-3xl font-bold tracking-tight text-white">Support</h1>
        <p class="mt-2 text-slate-400">Besoin d'aide ? Contactez l'équipe GLM.</p>
    </header>

    <div class="glm-card-static p-8 max-w-2xl">
        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-white mb-2">Contacter le support</h2>
                <p class="text-slate-300 text-sm">Pour toute question technique, demande d'évolution ou problème sur votre compte, envoyez-nous un email à :</p>
                <p class="mt-2">
                    <a href="mailto:{{ config('mail.from.address', 'support@glm.com') }}" class="text-[#60A5FA] hover:text-[#93C5FD] font-medium no-underline">{{ config('mail.from.address', 'support@glm.com') }}</a>
                </p>
            </div>
            <div class="pt-4 border-t border-white/10">
                <p class="text-slate-400 text-sm">Nous nous efforçons de répondre sous 24 à 48 h ouvrées. Indiquez le nom de votre entreprise et une description précise de votre demande.</p>
            </div>
        </div>
    </div>
</div>
@endsection
