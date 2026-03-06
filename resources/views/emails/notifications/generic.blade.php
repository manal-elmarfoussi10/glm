@extends('emails.layout')

@section('content')
<p style="margin: 0 0 16px 0; font-size: 16px;">Bonjour {{ $recipientName ?? 'vous' }},</p>
<p style="margin: 0 0 20px 0; color: #475569; line-height: 1.6;">{{ $body ?? 'Vous avez une nouvelle notification.' }}</p>
@if(!empty($actionUrl) && !empty($actionText))
@include('emails.partials.button', ['url' => $actionUrl, 'text' => $actionText])
@endif
<p style="margin: 28px 0 0 0; font-size: 14px; color: #64748b;">L'équipe GLM</p>
@endsection
