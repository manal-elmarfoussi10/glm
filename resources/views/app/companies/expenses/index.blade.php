@extends('app.layouts.app')

@section('pageSubtitle')
Dépenses – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Dépenses</h1>
            <p class="mt-1 text-sm text-slate-400">Dépenses manuelles par véhicule (entretien, réparation, accident, nettoyage, autre). Liées à la rentabilité.</p>
        </div>
        @if (auth()->user()?->role === 'company_admin')
            <a href="{{ route('app.companies.expenses.create', $company) }}" class="glm-btn-primary inline-flex no-underline">Nouvelle dépense</a>
        @endif
    </header>

    <form method="get" action="{{ route('app.companies.expenses.index', $company) }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div>
            <label for="vehicle_id" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Véhicule</label>
            <select id="vehicle_id" name="vehicle_id" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" onchange="this.form.submit()">
                <option value="">Tous</option>
                @foreach ($vehicles as $v)
                    <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->plate }} – {{ $v->brand }} {{ $v->model }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="category" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Catégorie</label>
            <select id="category" name="category" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" onchange="this.form.submit()">
                <option value="">Toutes</option>
                @foreach (\App\Models\Expense::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="from" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Du</label>
            <input type="date" id="from" name="from" value="{{ request('from') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" onchange="this.form.submit()">
        </div>
        <div>
            <label for="to" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Au</label>
            <input type="date" id="to" name="to" value="{{ request('to') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" onchange="this.form.submit()">
        </div>
        @if (request()->hasAny(['vehicle_id', 'category', 'from', 'to']))
            <a href="{{ route('app.companies.expenses.index', $company) }}" class="text-sm text-slate-400 hover:text-white no-underline">Réinitialiser</a>
        @endif
    </form>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Véhicule</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Catégorie</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Note</th>
                        @if (auth()->user()?->role === 'company_admin')
                            <th class="w-0 px-6 py-4"></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($expenses as $e)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $e->date?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-white">
                                @if ($e->vehicle)
                                    <a href="{{ route('app.companies.vehicles.show', [$company, $e->vehicle]) }}" class="hover:text-[#93C5FD] no-underline">{{ $e->vehicle->plate }} {{ $e->vehicle->brand }} {{ $e->vehicle->model }}</a>
                                @else
                                    <span class="text-slate-500">–</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ \App\Models\Expense::CATEGORIES[$e->category] ?? $e->category }}</td>
                            <td class="px-6 py-4 text-right text-sm font-semibold text-amber-400">{{ number_format($e->amount, 0, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4 text-sm text-slate-400 max-w-xs truncate">{{ Str::limit($e->description, 40) ?: '–' }}</td>
                            @if (auth()->user()?->role === 'company_admin')
                                <td class="px-6 py-4">
                                    <a href="{{ route('app.companies.expenses.edit', [$company, $e]) }}" class="text-sm text-[#2563EB] hover:text-[#93C5FD] no-underline mr-3">Modifier</a>
                                    <form action="{{ route('app.companies.expenses.destroy', [$company, $e]) }}" method="post" class="inline" onsubmit="return confirm('Supprimer cette dépense ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-400 hover:text-red-300 bg-transparent border-0 cursor-pointer p-0">Supprimer</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->role === 'company_admin' ? 6 : 5 }}" class="px-6 py-8 text-center text-slate-500">Aucune dépense.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($expenses->hasPages())
            <div class="px-6 py-4 border-t border-white/10">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>
@endsection
