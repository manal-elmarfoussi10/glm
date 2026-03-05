<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrat {{ $reservation->reference }}</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; padding: 2rem; color: #1e293b; background: #fff; line-height: 1.6; font-size: 14px; }
        .contract-page { max-width: 800px; margin: 0 auto; }
        .no-print { position: fixed; top: 1rem; right: 1rem; display: flex; gap: 0.5rem; z-index: 100; }
        .no-print button, .no-print a { padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; text-decoration: none; font-size: 14px; border: none; }
        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e2e8f0; }
        .doc-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem 0; }
        .doc-ref { font-size: 0.875rem; color: #64748b; }
        .doc-section { margin-bottom: 1.25rem; }
        .doc-section h3 { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin: 0 0 0.5rem 0; }
        .doc-section p { margin: 0.25rem 0; }
        .contract-body { margin: 1.5rem 0; padding: 1rem 0; border-top: 1px solid #e2e8f0; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; }
        .signature-block { text-align: center; }
        .signature-line { border-bottom: 1px solid #1e293b; height: 2rem; margin: 2rem 0 0.25rem 0; }
        .signature-label { font-size: 0.75rem; color: #64748b; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()">Imprimer / PDF</button>
        <a href="{{ route('app.companies.reservations.show', [$company, $reservation]) }}">Retour à la réservation</a>
    </div>
    <div class="contract-page">
        <div class="doc-header">
            <div>
                <p class="doc-title">Contrat de location de véhicule</p>
                <p class="doc-ref">Réf. {{ $reservation->reference }} — {{ now()->format('d/m/Y') }}</p>
            </div>
            @if($company->name)
                <div style="text-align: right;">
                    <strong>{{ $company->name }}</strong>
                    @if($company->ice)<br><span style="font-size: 0.875rem; color: #64748b;">ICE {{ $company->ice }}</span>@endif
                </div>
            @endif
        </div>

        <div class="contract-body">
            {!! $content !!}
        </div>

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line"></div>
                <p class="signature-label">Le client</p>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <p class="signature-label">Le loueur</p>
            </div>
        </div>
    </div>
    <script>
        if (new URLSearchParams(window.location.search).get('auto') === 'print') {
            window.onload = function() { window.print(); };
        }
    </script>
</body>
</html>
