<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyExpenseController extends Controller
{
    public function index(Request $request, Company $company): View
    {
        $query = $company->expenses()->with(['vehicle', 'branch', 'createdBy'])->orderByDesc('date');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('from')) {
            $query->where('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('date', '<=', $request->to);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $vehicles = $company->vehicles()->orderBy('plate')->get();

        return view('app.companies.expenses.index', [
            'title' => 'Dépenses – ' . $company->name,
            'company' => $company,
            'expenses' => $expenses,
            'vehicles' => $vehicles,
        ]);
    }

    public function create(Company $company): View
    {
        $vehicles = $company->vehicles()->orderBy('plate')->get();
        $branches = $company->branches()->orderBy('name')->get();

        return view('app.companies.expenses.create', [
            'title' => 'Nouvelle dépense – ' . $company->name,
            'company' => $company,
            'vehicles' => $vehicles,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'category' => 'required|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
        ]);

        $vehicleId = $validated['vehicle_id'] ?? null;
        if ($vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            if (! $vehicle || $vehicle->branch->company_id !== $company->id) {
                return back()->withInput()->with('error', 'Véhicule invalide.');
            }
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('expenses/' . $company->id, 'public');
        }

        $company->expenses()->create([
            'branch_id' => $validated['branch_id'] ?? null,
            'vehicle_id' => $vehicleId,
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'attachment_path' => $path,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('app.companies.expenses.index', $company)
            ->with('success', 'Dépense enregistrée.');
    }

    public function edit(Company $company, Expense $expense): View|RedirectResponse
    {
        if ($expense->company_id !== $company->id) {
            abort(404);
        }
        $vehicles = $company->vehicles()->orderBy('plate')->get();
        $branches = $company->branches()->orderBy('name')->get();

        return view('app.companies.expenses.edit', [
            'title' => 'Modifier la dépense – ' . $company->name,
            'company' => $company,
            'expense' => $expense,
            'vehicles' => $vehicles,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, Company $company, Expense $expense): RedirectResponse
    {
        if ($expense->company_id !== $company->id) {
            abort(404);
        }

        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'category' => 'required|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
        ]);

        $vehicleId = $validated['vehicle_id'] ?? null;
        if ($vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            if (! $vehicle || $vehicle->branch->company_id !== $company->id) {
                return back()->withInput()->with('error', 'Véhicule invalide.');
            }
        }

        $path = $expense->attachment_path;
        if ($request->hasFile('attachment')) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            $path = $request->file('attachment')->store('expenses/' . $company->id, 'public');
        }

        $expense->update([
            'branch_id' => $validated['branch_id'] ?? null,
            'vehicle_id' => $vehicleId,
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'attachment_path' => $path,
        ]);

        return redirect()
            ->route('app.companies.expenses.index', $company)
            ->with('success', 'Dépense mise à jour.');
    }

    public function destroy(Company $company, Expense $expense): RedirectResponse
    {
        if ($expense->company_id !== $company->id) {
            abort(404);
        }
        if ($expense->attachment_path) {
            Storage::disk('public')->delete($expense->attachment_path);
        }
        $expense->delete();

        return redirect()
            ->route('app.companies.expenses.index', $company)
            ->with('success', 'Dépense supprimée.');
    }
}
