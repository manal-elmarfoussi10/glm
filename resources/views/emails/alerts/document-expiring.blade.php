@extends('emails.layout')

@section('content')
<p style="margin: 0 0 16px 0; font-size: 16px;">Bonjour {{ $recipientName ?? 'vous' }},</p>

<p style="margin: 0 0 20px 0; color: #475569; line-height: 1.6;">Un document de conformité arrive à expiration prochainement pour un véhicule de votre flotte.</p>

@include('emails.partials.section-title', ['title' => 'Détails'])
<p style="margin: 0 0 8px 0;"><strong>Type :</strong> {{ $documentType ?? 'Document' }}</p>
<p style="margin: 0 0 8px 0;"><strong>Véhicule :</strong> {{ $vehiclePlate ?? '–' }} ({{ $vehicleName ?? '–' }})</p>
<p style="margin: 0 0 8px 0;"><strong>Date d'expiration :</strong> {{ $expiryDate ?? '–' }}</p>
@if(!empty($daysLeft))
<p style="margin: 0 0 20px 0;"><strong>Jours restants :</strong> {{ $daysLeft }}</p>
@endif

@if(!empty($vehicleUrl))
@include('emails.partials.button', ['url' => $vehicleUrl, 'text' => 'Voir la fiche véhicule'])
@endif

<p style="margin: 28px 0 0 0; font-size: 14px; color: #64748b;">Pensez à renouveler ce document pour rester en conformité.</p>

<p style="margin: 12px 0 0 0; font-size: 14px; color: #64748b;">L'équipe GLM</p>
@endsection
