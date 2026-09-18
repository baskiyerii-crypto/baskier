<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequirementTemplate;
use App\Support\UiLabels;
use Illuminate\Http\Request;

class AdminDocumentRequirementController extends Controller
{
    public function index()
    {
        $groups = DocumentRequirementTemplate::query()
            ->orderBy('audience')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('audience');

        return view('admin.document-requirements.index', [
            'groups' => $groups,
            'audiences' => DocumentRequirementTemplate::AUDIENCES,
            'types' => UiLabels::documentTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'audience' => ['required', 'in:'.implode(',', DocumentRequirementTemplate::AUDIENCES)],
            'document_type' => ['required', 'string', 'max:64'],
            'label' => ['required', 'string', 'max:120'],
            'required' => ['sometimes', 'boolean'],
            'requires_file' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
        DocumentRequirementTemplate::query()->updateOrCreate(
            [
                'audience' => $validated['audience'],
                'document_type' => $validated['document_type'],
            ],
            [
                'label' => $validated['label'],
                'required' => $request->boolean('required'),
                'requires_file' => $request->boolean('requires_file', true),
                'sort_order' => (int) ($validated['sort_order'] ?? 10),
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Belge şablonu kaydedildi.');
    }

    public function update(Request $request, DocumentRequirementTemplate $documentRequirementTemplate)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
        $documentRequirementTemplate->update([
            'label' => $validated['label'],
            'required' => $request->boolean('required'),
            'requires_file' => $request->boolean('requires_file'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($validated['sort_order'] ?? $documentRequirementTemplate->sort_order),
        ]);

        return back()->with('success', 'Şablon güncellendi.');
    }

    public function destroy(DocumentRequirementTemplate $documentRequirementTemplate)
    {
        $documentRequirementTemplate->delete();

        return back()->with('success', 'Şablon silindi.');
    }
}
