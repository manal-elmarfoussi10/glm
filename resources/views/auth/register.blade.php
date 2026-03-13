@php
    $layoutTitle = 'Créer un compte';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $layoutTitle }} - GLM</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap');
        @keyframes glm-fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes glm-slide-up { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .glm-fade-in { animation: glm-fade-in 0.6s ease-out forwards; }
        .glm-slide-up { animation: glm-slide-up 0.7s ease-out 0.2s forwards; opacity: 0; }
        .glm-register-input { width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: #fff; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        .glm-register-input::placeholder { color: rgba(255,255,255,0.4); }
        .glm-register-input:focus { border-color: rgba(255,255,255,0.2); box-shadow: 0 0 0 2px rgba(59,130,246,0.3); }
        .glm-register-input.error { border-color: rgba(248,113,113,0.5); }
        .glm-register-input.error:focus { box-shadow: 0 0 0 2px rgba(248,113,113,0.3); }
        .glm-register-label { display: block; font-size: 0.875rem; font-weight: 500; color: rgb(203 213 225); margin-bottom: 0.375rem; }
    </style>
</head>
<body class="min-h-screen bg-[#0B1220] antialiased">
    <div class="glm-auth-layout min-h-screen flex flex-col lg:flex-row">
        {{-- Left: hero (same as login) --}}
        <div class="glm-auth-left hidden lg:flex lg:w-1/2 xl:w-[55%] bg-[#0F172A] text-white flex-col justify-between p-10 xl:p-16 relative overflow-hidden">
            <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute top-20 left-10 w-72 h-72 bg-[#2563EB]/20 rounded-full blur-3xl glm-orb glm-orb-1"></div>
                <div class="absolute bottom-32 right-20 w-96 h-96 bg-[#2563EB]/10 rounded-full blur-3xl glm-orb glm-orb-2"></div>
                <div class="absolute top-1/2 left-1/3 w-64 h-64 border border-[#2563EB]/30 rounded-full glm-orb glm-orb-3"></div>
            </div>
            <div class="relative z-10 flex items-center gap-4 glm-fade-in">
                <a href="{{ url('/admin') }}" class="flex items-center gap-4">
                    <img src="{{ url('images/light-logo.png') }}" alt="GLM" class="h-20 w-20 lg:h-28 lg:w-28 object-contain flex-shrink-0" onerror="this.style.display='none'; this.nextElementSibling?.classList.remove('hidden');" />
                    <span class="hidden text-2xl lg:text-3xl font-bold text-white tracking-tight" style="font-family: 'Montserrat', sans-serif;">GLM</span>
                </a>
            </div>
            <div class="relative z-10 space-y-8 glm-slide-up">
                <h2 class="text-6xl xl:text-7xl font-bold tracking-tight leading-[1.05] text-white max-w-xl" style="font-family: 'Montserrat', sans-serif;">Gestion Location Maroc</h2>
                <p class="text-white/85 text-xl max-w-lg leading-relaxed mt-8" style="font-family: 'Inter', sans-serif;">Gérez vos locations et contrats en toute simplicité. Accédez à votre espace professionnel.</p>
            </div>
            <div class="relative z-10 text-white/55 text-sm" style="font-family: 'Inter', sans-serif;">&copy; {{ date('Y') }} GLM. Tous droits réservés.</div>
        </div>

        {{-- Right: register card (wider than login) --}}
        <div class="glm-auth-right flex-1 flex flex-col justify-center items-center p-8 sm:p-12 lg:p-16 bg-[#0B1220]">
            <div class="w-full max-w-3xl glm-fade-in">
                <div class="glm-auth-card rounded-2xl p-8 sm:p-10 shadow-xl">
                    <div class="flex flex-col items-center lg:hidden mb-6">
                        <a href="{{ url('/admin') }}" class="flex items-center justify-center">
                            <img src="{{ url('images/light-logo.png') }}" alt="GLM" class="h-16 w-auto object-contain" onerror="this.style.display='none'; this.nextElementSibling?.classList.remove('hidden');" />
                            <span class="hidden text-xl font-bold text-white tracking-tight" style="font-family: 'Montserrat', sans-serif;">GLM</span>
                        </a>
                    </div>
                    <header class="text-left space-y-1 mb-6">
                        <span class="inline-block text-xs font-semibold uppercase tracking-widest text-blue-400" style="font-family: 'Inter', sans-serif;">Inscription</span>
                        <h1 class="text-2xl font-bold text-white tracking-tight" style="font-family: 'Montserrat', sans-serif;">Créer un compte</h1>
                        <p class="text-white/80 text-sm">Déjà un compte ? <a href="{{ url('/admin/login') }}" class="text-blue-400 hover:text-blue-300 font-medium">Se connecter</a></p>
                    </header>

                    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                        @csrf
                        @if ($errors->any())
                            <div class="rounded-xl bg-red-500/10 border border-red-500/20 px-4 py-3 text-sm text-red-200">
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="glm-register-label">Nom complet <span class="text-red-400">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                                    class="glm-register-input {{ $errors->has('name') ? 'error' : '' }}" placeholder="Jean Dupont" />
                            </div>
                            <div>
                                <label for="email" class="glm-register-label">Adresse e-mail <span class="text-red-400">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                    class="glm-register-input {{ $errors->has('email') ? 'error' : '' }}" placeholder="vous@exemple.com" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="requested_company_name" class="glm-register-label">Nom de l'entreprise <span class="text-red-400">*</span></label>
                                <input type="text" id="requested_company_name" name="requested_company_name" value="{{ old('requested_company_name') }}" required
                                    class="glm-register-input {{ $errors->has('requested_company_name') ? 'error' : '' }}" placeholder="Ma Société SARL" />
                            </div>
                            <div>
                                <label for="phone" class="glm-register-label">Téléphone <span class="text-red-400">*</span></label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                                    class="glm-register-input {{ $errors->has('phone') ? 'error' : '' }}" placeholder="+212 6 00 00 00 00" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="requested_ice" class="glm-register-label">ICE <span class="text-red-400">*</span></label>
                                <input type="text" id="requested_ice" name="requested_ice" value="{{ old('requested_ice') }}" required
                                    pattern="[0-9]*" minlength="12" maxlength="20" inputmode="numeric"
                                    class="glm-register-input {{ $errors->has('requested_ice') ? 'error' : '' }}" placeholder="15 chiffres" />
                              
                            </div>
                            <div>
                                <label for="requested_country" class="glm-register-label">Pays <span class="text-red-400">*</span></label>
                                <input type="text" id="requested_country" name="requested_country" value="{{ old('requested_country', 'Maroc') }}" required
                                    class="glm-register-input {{ $errors->has('requested_country') ? 'error' : '' }}" placeholder="Maroc" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="plan_id" class="glm-register-label">Plan choisi <span class="text-red-400">*</span></label>
                                <select id="plan_id" name="plan_id" required
                                    class="glm-register-input {{ $errors->has('plan_id') ? 'error' : '' }}">
                                    <option value="">Choisir un plan</option>
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }}{{ $plan->monthly_price !== null ? ' — ' . $plan->monthly_price . ' MAD/mois' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="fleet_size" class="glm-register-label">Taille de la flotte (véhicules) <span class="text-red-400">*</span></label>
                                <input type="number" id="fleet_size" name="fleet_size" value="{{ old('fleet_size', '0') }}" required min="0" step="1"
                                    class="glm-register-input {{ $errors->has('fleet_size') ? 'error' : '' }}" />
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="operating_cities" class="glm-register-label">Villes d'opération</label>
                            <input type="text" id="operating_cities" name="operating_cities" value="{{ old('operating_cities') }}"
                                class="glm-register-input {{ $errors->has('operating_cities') ? 'error' : '' }}" placeholder="Casablanca, Rabat, Marrakech" />
                        </div>

                        <div class="md:col-span-2">
                            <label for="registration_message" class="glm-register-label">Message / Notes (optionnel)</label>
                            <textarea id="registration_message" name="registration_message" rows="3"
                                class="glm-register-input min-h-[80px] resize-y {{ $errors->has('registration_message') ? 'error' : '' }}" placeholder="Précisions ou questions…">{{ old('registration_message') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <label for="password" class="glm-register-label">Mot de passe <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" required
                                        class="glm-register-input pr-12 {{ $errors->has('password') ? 'error' : '' }}" placeholder="••••••••" />
                                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white focus:outline-none" aria-label="Afficher le mot de passe"
                                        onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password'">
                                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <label for="password_confirmation" class="glm-register-label">Confirmer le mot de passe <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" required
                                        class="glm-register-input pr-12 {{ $errors->has('password_confirmation') ? 'error' : '' }}" placeholder="••••••••" />
                                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white focus:outline-none" aria-label="Afficher le mot de passe"
                                        onclick="var i=document.getElementById('password_confirmation');i.type=i.type==='password'?'text':'password'">
                                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-3 px-4 rounded-xl font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-0 focus:ring-offset-[#0B1220] transition shadow-lg">
                                Créer mon compte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
