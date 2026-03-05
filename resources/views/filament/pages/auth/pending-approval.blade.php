<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#0B1220]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>En attente d'approbation - GLM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-gray-100 flex items-center justify-center p-6 bg-[#0B1220]">
    <div class="max-w-md w-full text-center space-y-8">
        <div>
            <img class="mx-auto h-12 w-auto" src="{{ url('images/light-logo.png') }}" alt="GLM">
            <h2 class="mt-6 text-3xl font-extrabold tracking-tight text-white">Inscription réussie</h2>
            <p class="mt-4 text-lg text-gray-300">
                Merci pour votre inscription. Votre compte a bien été créé.
            </p>
            <p class="mt-4 text-sm text-gray-400">
                <strong>Notre équipe support vous contactera très prochainement</strong> pour vous donner accès à votre essai gratuit et activer votre espace professionnel.
            </p>
        </div>
        
        <div class="pt-6">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
