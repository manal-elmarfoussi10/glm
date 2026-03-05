<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu {{ $reservation->reference }}</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 2rem; color: #1e293b; background: #fff; line-height: 1.5; max-width: 800px; margin: 0 auto; }
        .no-print { margin-bottom: 1rem; }
        @media print { .no-print { display: none !important; } body { padding: 0; } }
        h1 { font-size: 1.5rem; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; text-align: left; }
        th { background: #f1f5f9; font-weight: 600; }
        .text-right { text-align: right; }
        .mt-4 { margin-top: 1.5rem; }
        .text-muted { color: #64748b; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()" style="padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; border-radius: 0.5rem; cursor: pointer;">Imprimer / PDF</button>
        <a href="{{ route('app.companies.reservations.show', [$company, $reservation]) }}?tab=payments" style="margin-left: 0.5rem; padding: 0.5rem 1rem; background: #475569; color: white; border-radius: 0.5rem; text-decoration: none;">Retour à la réservation</a>
    </div>

    <h1>Reçu – {{ $reservation->reference }}</h1>

    <p><strong>{{ $company->name }}</strong><br>
    @if ($company->ice) ICE : {{ $company->ice }}<br>@endif
    @if ($company->address) {{ $company->address }}<br>@endif
    @if ($company->phone) Tél. {{ $company->phone }}@endif
    </p>

    <p class="text-muted">Client : <strong>{{ $reservation->customer->name }}</strong> · CIN : {{ $reservation->customer->cin }}<br>
    Véhicule : {{ $reservation->vehicle->plate }} – {{ $reservation->vehicle->brand }} {{ $reservation->vehicle->model }}<br>
    Période : {{ $reservation->start_at->format('d/m/Y') }} → {{ $reservation->end_at->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Moyen</th>
                <th>Référence</th>
                <th class="text-right">Montant (MAD)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalIn = 0; $totalRefund = 0; @endphp
            @foreach ($reservation->payments->sortBy('paid_at') as $p)
                @if ($p->isRefund())
                    @php $totalRefund += (float) $p->amount; @endphp
                    <tr>
                        <td>{{ $p->paid_at->format('d/m/Y') }}</td>
                        <td>Remboursement</td>
                        <td>{{ $p->method }}</td>
                        <td>{{ $p->reference ?? '–' }}</td>
                        <td class="text-right">- {{ number_format($p->amount, 2, ',', ' ') }}</td>
                    </tr>
                @else
                    @php $totalIn += (float) $p->amount; @endphp
                    <tr>
                        <td>{{ $p->paid_at->format('d/m/Y') }}</td>
                        <td>{{ $p->type === 'rental' ? 'Location' : ($p->type === 'deposit' ? 'Caution' : ($p->type === 'fee' ? 'Frais' : $p->type)) }}</td>
                        <td>{{ $p->method }}</td>
                        <td>{{ $p->reference ?? '–' }}</td>
                        <td class="text-right">{{ number_format($p->amount, 2, ',', ' ') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <table>
        <tr><td>Total location (TTC)</td><td class="text-right">{{ number_format($reservation->total_price, 2, ',', ' ') }} MAD</td></tr>
        <tr><td>Caution (véhicule)</td><td class="text-right">{{ number_format($reservation->deposit_expected, 2, ',', ' ') }} MAD</td></tr>
        <tr><td><strong>Total encaissé</strong></td><td class="text-right"><strong>{{ number_format($totalIn - $totalRefund, 2, ',', ' ') }} MAD</strong></td></tr>
    </table>

    <p class="text-muted mt-4">Document établi le {{ now()->format('d/m/Y à H:i') }}. Ce reçu ne vaut pas facture.</p>
</body>
</html>
