<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBusinessTypeController extends Controller
{
    public function index()
    {
        $types = BusinessType::withCount('vendors')->orderBy('sort_order')->orderBy('name')->paginate(30);

        return view('admin.business-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.business-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:business_types,slug'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
        $validated['slug'] = $validated['slug']
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']) . '-' . uniqid();
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        BusinessType::create($validated);

        return redirect()->route('admin.business-types.index')->with('success', 'İş kolu eklendi.');
    }

    public function edit(BusinessType $businessType)
    {
        return view('admin.business-types.edit', ['type' => $businessType]);
    }

    public function update(Request $request, BusinessType $businessType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:business_types,slug,' . $businessType->id],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
        $validated['slug'] = Str::slug($validated['slug']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $businessType->update($validated);

        return redirect()->route('admin.business-types.index')->with('success', 'İş kolu güncellendi.');
    }

    public function destroy(BusinessType $businessType)
    {
        if ($businessType->vendors()->exists()) {
            return back()->with('error', 'Bu iş koluna bağlı satıcılar var. Önce satıcı kayıtlarından kaldırın.');
        }
        $businessType->delete();

        return redirect()->route('admin.business-types.index')->with('success', 'İş kolu silindi.');
    }
}
