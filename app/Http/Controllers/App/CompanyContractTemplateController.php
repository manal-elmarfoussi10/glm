<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ContractTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyContractTemplateController extends Controller
{
    public function index(Company $company): View
    {
        $templates = $company->contractTemplates()->orderBy('name')->get();
        $globalTemplates = ContractTemplate::global()->orderBy('name')->get();

        return view('app.companies.contract-templates.index', [
            'title' => 'Modèles de contrats – ' . $company->name,
            'company' => $company,
            'templates' => $templates,
            'globalTemplates' => $globalTemplates,
        ]);
    }

    /**
     * Preview a global template (read-only) before duplicating.
     */
    public function previewGlobal(Company $company, ContractTemplate $contractTemplate): View
    {
        if (! $contractTemplate->isGlobal()) {
            abort(404);
        }

        $previewHtml = $contractTemplate->contentForPreview();

        return view('app.companies.contract-templates.preview-global', [
            'title' => 'Aperçu – ' . $contractTemplate->name,
            'company' => $company,
            'template' => $contractTemplate,
            'previewHtml' => $previewHtml,
        ]);
    }

    public function create(Request $request, Company $company): View
    {
        $fromGlobalId = $request->query('from_global');
        $sourceGlobal = $fromGlobalId ? ContractTemplate::global()->find($fromGlobalId) : null;

        return view('app.companies.contract-templates.create', [
            'title' => 'Nouveau modèle – ' . $company->name,
            'company' => $company,
            'globalTemplates' => ContractTemplate::global()->orderBy('name')->get(),
            'sourceGlobal' => $sourceGlobal,
            'sampleData' => app(\App\Services\ContractRenderer::class)->sampleData(),
        ]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'content' => 'nullable|string',
            'version' => 'nullable|string|max:32',
            'source_global_id' => 'nullable|exists:contract_templates,id',
        ]);

        $exists = $company->contractTemplates()->where('slug', $validated['slug'])->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['slug' => 'Ce slug existe déjà pour cette entreprise.']);
        }

        $validated['company_id'] = $company->id;
        $validated['version'] = $validated['version'] ?? '1.0';
        $validated['created_by'] = auth()->id();
        if (empty($validated['source_global_id'])) {
            unset($validated['source_global_id']);
        }

        ContractTemplate::create($validated);

        return redirect()
            ->route('app.companies.contract-templates.index', $company)
            ->with('success', 'Modèle créé.');
    }

    public function edit(Company $company, ContractTemplate $contractTemplate): View|RedirectResponse
    {
        if ($contractTemplate->company_id != $company->id) {
            abort(404);
        }

        return view('app.companies.contract-templates.edit', [
            'title' => 'Modifier – ' . $contractTemplate->name,
            'company' => $company,
            'template' => $contractTemplate,
            'sampleData' => app(\App\Services\ContractRenderer::class)->sampleData(),
        ]);
    }

    public function update(Request $request, Company $company, ContractTemplate $contractTemplate): RedirectResponse
    {
        if ($contractTemplate->company_id != $company->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'content' => 'nullable|string',
            'version' => 'nullable|string|max:32',
        ]);

        $exists = $company->contractTemplates()->where('slug', $validated['slug'])->where('id', '!=', $contractTemplate->id)->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['slug' => 'Ce slug existe déjà.']);
        }

        $contractTemplate->update($validated);

        return redirect()
            ->route('app.companies.contract-templates.index', $company)
            ->with('success', 'Modèle mis à jour.');
    }

    public function destroy(Company $company, ContractTemplate $contractTemplate): RedirectResponse
    {
        if ($contractTemplate->company_id != $company->id) {
            abort(404);
        }

        if ($company->default_contract_template_id == $contractTemplate->id) {
            $company->update(['default_contract_template_id' => null]);
        }

        $contractTemplate->delete();

        return redirect()
            ->route('app.companies.contract-templates.index', $company)
            ->with('success', 'Modèle supprimé.');
    }

    public function setDefault(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:contract_templates,id',
        ]);

        $template = ContractTemplate::where('company_id', $company->id)->findOrFail($validated['template_id']);
        $company->update(['default_contract_template_id' => $template->id]);

        return back()->with('success', 'Modèle par défaut enregistré.');
    }
}
