<?php

namespace App\Http\Controllers\App\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContractTemplateController extends Controller
{
    public function index(): View
    {
        $templates = ContractTemplate::global()->orderBy('name')->paginate(15);

        return view('app.admin.contract-templates.index', [
            'title' => 'Bibliothèque de contrats',
            'templates' => $templates,
        ]);
    }

    public function create(): View
    {
        return view('app.admin.contract-templates.create', [
            'title' => 'Nouveau modèle global',
            'sampleData' => app(\App\Services\ContractRenderer::class)->sampleData(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'content' => 'nullable|string',
            'version' => 'nullable|string|max:32',
        ]);

        $exists = ContractTemplate::global()->where('slug', $validated['slug'])->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['slug' => 'Ce slug existe déjà dans la bibliothèque globale.']);
        }

        $validated['company_id'] = null;
        $validated['version'] = $validated['version'] ?? '1.0';
        $validated['created_by'] = auth()->id();

        ContractTemplate::create($validated);

        return redirect()
            ->route('app.admin.contract-templates.index')
            ->with('success', 'Modèle créé.');
    }

    /**
     * Preview as minimal HTML for iframe (e.g. in modal).
     */
    public function previewFrame(ContractTemplate $contractTemplate): View
    {
        if (! $contractTemplate->isGlobal()) {
            abort(404);
        }

        $previewHtml = $contractTemplate->contentForPreview();

        return view('app.admin.contract-templates.preview-frame', [
            'previewHtml' => $previewHtml,
        ]);
    }

    public function show(ContractTemplate $contractTemplate): View|RedirectResponse
    {
        if (! $contractTemplate->isGlobal()) {
            abort(404);
        }

        $previewHtml = $contractTemplate->contentForPreview();

        return view('app.admin.contract-templates.show', [
            'title' => 'Aperçu – ' . $contractTemplate->name,
            'template' => $contractTemplate,
            'previewHtml' => $previewHtml,
        ]);
    }

    public function edit(ContractTemplate $contractTemplate): View|RedirectResponse
    {
        if (!$contractTemplate->isGlobal()) {
            abort(404);
        }

        return view('app.admin.contract-templates.edit', [
            'title' => 'Modifier – ' . $contractTemplate->name,
            'template' => $contractTemplate,
            'sampleData' => app(\App\Services\ContractRenderer::class)->sampleData(),
        ]);
    }

    public function update(Request $request, ContractTemplate $contractTemplate): RedirectResponse
    {
        if (!$contractTemplate->isGlobal()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'content' => 'nullable|string',
            'version' => 'nullable|string|max:32',
        ]);

        $exists = ContractTemplate::global()->where('slug', $validated['slug'])->where('id', '!=', $contractTemplate->id)->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['slug' => 'Ce slug existe déjà.']);
        }

        $contractTemplate->update($validated);

        return redirect()
            ->route('app.admin.contract-templates.index')
            ->with('success', 'Modèle mis à jour.');
    }

    public function destroy(ContractTemplate $contractTemplate): RedirectResponse
    {
        if (!$contractTemplate->isGlobal()) {
            abort(404);
        }

        $contractTemplate->delete();

        return redirect()
            ->route('app.admin.contract-templates.index')
            ->with('success', 'Modèle supprimé.');
    }
}
