@extends('emails.layout')

@section('content')
<p style="margin: 0 0 16px 0; font-size: 16px;">Bonjour {{ $userName ?? 'vous' }},</p>
<p style="margin: 0 0 20px 0; color: #475569; line-height: 1.6;">Bienvenue sur GLM. Votre compte a été créé avec succès.</p>
@include('emails.partials.section-title', ['title' => 'Prochaines étapes'])
<p style="margin: 0 0 24px 0; color: #475569;">Connectez-vous à votre tableau de bord pour configurer votre entreprise.</p>
@if(!empty($loginUrl))
@include('emails.partials.button', ['url' => $loginUrl, 'text' => 'Se connecter'])
@endif
<p style="margin: 28px 0 0 0; font-size: 14px; color: #64748b;">L'équipe GLM</p>
@endsection
