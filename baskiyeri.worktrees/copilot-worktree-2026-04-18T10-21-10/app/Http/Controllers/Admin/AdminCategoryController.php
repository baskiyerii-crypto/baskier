<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->with('parent')->orderBy('name')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'delivery_days' => ['nullable', 'integer', 'min:0'],
            'requires_quote' => ['boolean'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['requires_quote'] = $request->boolean('requires_quote');
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }
        Category::create($validated);
        return redirect()->route('admin.categories.index')->with('success', 'Kategori eklendi.');
    }

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('name')->get();
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'delivery_days' => ['nullable', 'integer', 'min:0'],
            'requires_quote' => ['boolean'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['requires_quote'] = $request->boolean('requires_quote');
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }
        $category->update($validated);
        return redirect()->route('admin.categories.index')->with('success', 'Kategori güncellendi.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Bu kategoride ürün var, önce ürünleri taşıyın veya silin.');
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Kategori silindi.');
    }
}
