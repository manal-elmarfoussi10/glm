@extends('emails.layout')

@section('content')
<p style="margin: 0 0 16px 0; font-size: 16px;">Bonjour {{ $userName ?? 'vous' }},</p>

<p style="margin: 0 0 20px 0; color: #475569; line-height: 1.6;">Vous avez demandé une réinitialisation de votre mot de passe. Cliquez sur le bouton ci-dessous pour en choisir un nouveau. Ce lien expire dans {{ $expireMinutes ?? 60 }} minutes.</p>

@if(!empty($resetUrl))
@include('emails.partials.button', ['url' => $resetUrl, 'text' => 'Réinitialiser mon mot de passe'])
@endif

<p style="margin: 24px 0 0 0; font-size: 14px; color: #64748b;">Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email. Votre mot de passe ne sera pas modifié.</p>

<p style="margin: 28px 0 0 0; font-size: 14px; color: #64748b;">L'équipe GLM</p>
@endsection
