<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBrandController extends Controller
{
    public function index()
    {
        $brands = PlatformBrand::query()->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        PlatformBrand::create($validated);

        return back()->with('success', 'Alt marka eklendi.');
    }

    public function update(Request $request, PlatformBrand $brand)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $brand->update($validated);

        return back()->with('success', 'Alt marka güncellendi.');
    }

    public function destroy(PlatformBrand $brand)
    {
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        $brand->delete();

        return back()->with('success', 'Alt marka silindi.');
    }
}
