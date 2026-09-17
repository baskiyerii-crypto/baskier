<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminCategoryController extends Controller
{
    public function index(Request $request)
    {
        $channel = $request->get('channel', 'physical_quote');
        $hasChannel = Schema::hasColumn('categories', 'channel');
        $categories = Category::withCount('products')->with('parent')
            ->when($hasChannel && $channel !== 'all', fn ($q) => $q->where('channel', $channel))
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.categories.index', compact('categories', 'channel'));
    }

    public function create(Request $request)
    {
        $channel = $request->get('channel', 'physical_quote');
        $parents = Category::whereNull('parent_id')
            ->when(Schema::hasColumn('categories', 'channel'), fn ($q) => $q->where('channel', $channel))
            ->orderBy('name')->get();
        return view('admin.categories.create', compact('parents', 'channel'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'channel' => ['required', 'in:physical_quote,freelancer,tabela,ozalit,outdoor'],
            'termin_days' => ['nullable', 'integer', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:0'],
            'requires_quote' => ['boolean'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['requires_quote'] = $request->boolean('requires_quote');
        $validated['termin_days'] = $validated['termin_days'] ?? $validated['delivery_days'] ?? null;
        $validated['delivery_days'] = $validated['termin_days'];
        if (! Schema::hasColumn('categories', 'channel')) {
            unset($validated['channel']);
        }
        if (! Schema::hasColumn('categories', 'termin_days')) {
            unset($validated['termin_days']);
        }
        if (! Schema::hasColumn('categories', 'name_en')) {
            unset($validated['name_en']);
        }
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }
        Category::create($validated);
        return redirect()->route('admin.categories.index', array_filter(['channel' => $validated['channel'] ?? null]))->with('success', 'Kategori eklendi.');
    }

    public function edit(Category $category)
    {
        $channel = $category->channel ?? 'physical_quote';
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->when(Schema::hasColumn('categories', 'channel'), fn ($q) => $q->where('channel', $channel))
            ->orderBy('name')->get();
        return view('admin.categories.edit', compact('category', 'parents', 'channel'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'channel' => ['required', 'in:physical_quote,freelancer,tabela,ozalit,outdoor'],
            'termin_days' => ['nullable', 'integer', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:0'],
            'requires_quote' => ['boolean'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['requires_quote'] = $request->boolean('requires_quote');
        $validated['termin_days'] = $validated['termin_days'] ?? $validated['delivery_days'] ?? null;
        $validated['delivery_days'] = $validated['termin_days'];
        if (! Schema::hasColumn('categories', 'channel')) {
            unset($validated['channel']);
        }
        if (! Schema::hasColumn('categories', 'termin_days')) {
            unset($validated['termin_days']);
        }
        if (! Schema::hasColumn('categories', 'name_en')) {
            unset($validated['name_en']);
        }
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }
        $category->update($validated);
        return redirect()->route('admin.categories.index', array_filter(['channel' => $validated['channel'] ?? null]))->with('success', 'Kategori güncellendi.');
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
