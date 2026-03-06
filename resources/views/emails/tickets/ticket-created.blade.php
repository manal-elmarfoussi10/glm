@extends('emails.layout')

@section('content')
<p style="margin: 0 0 16px 0; font-size: 16px;">Bonjour {{ $recipientName ?? 'vous' }},</p>

<p style="margin: 0 0 20px 0; color: #475569; line-height: 1.6;">Un nouveau ticket de support a été créé.</p>

@include('emails.partials.section-title', ['title' => 'Détails du ticket'])
<p style="margin: 0 0 8px 0;"><strong>Sujet :</strong> {{ $ticketSubject ?? '–' }}</p>
@if(!empty($companyName))
<p style="margin: 0 0 8px 0;"><strong>Entreprise :</strong> {{ $companyName }}</p>
@endif
<p style="margin: 0 0 20px 0;"><strong>Référence :</strong> #{{ $ticketId ?? '–' }}</p>

@if(!empty($ticketUrl))
@include('emails.partials.button', ['url' => $ticketUrl, 'text' => 'Voir le ticket'])
@endif

<p style="margin: 28px 0 0 0; font-size: 14px; color: #64748b;">L'équipe GLM</p>
@endsection
