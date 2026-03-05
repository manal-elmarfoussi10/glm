<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aperçu contrat</title>
    <style>
        html { height: 100%; }
        body {
            margin: 0;
            padding: 1.5rem 2rem;
            min-height: 600px;
            box-sizing: border-box;
            overflow-y: auto;
        }
        body * { max-width: 100% !important; box-sizing: border-box; }
    </style>
</head>
<body class="bg-white text-slate-800">
    {!! $previewHtml !!}
</body>
</html>
