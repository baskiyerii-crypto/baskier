<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use App\Support\SiteMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminMenuController extends Controller
{
    public function index()
    {
        $items = SiteMenu::allItems();
        $categories = Category::query()
            ->when(Schema::hasColumn('categories', 'is_active'), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.menu.index', [
            'items' => $items,
            'categories' => $categories,
            'routeOptions' => SiteMenu::routeOptions(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.label' => ['required', 'string', 'max:80'],
            'items.*.type' => ['required', 'in:route,url,category,products,page,categories_accordion'],
            'items.*.target' => ['nullable', 'string', 'max:500'],
            'items.*.placement' => ['required', 'in:top,drawer'],
            'items.*.is_active' => ['nullable'],
        ]);

        $items = [];
        foreach (array_values($validated['items'] ?? []) as $i => $row) {
            $type = $row['type'];
            $target = $row['target'] ?? null;
            if (in_array($type, ['products', 'categories_accordion'], true)) {
                $target = null;
            }
            $items[] = [
                'label' => trim($row['label']),
                'type' => $type,
                'target' => $target !== null && $target !== '' ? (string) $target : null,
                'placement' => $row['placement'],
                'sort' => $i + 1,
                'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        Setting::set('menu_items_json', json_encode($items, JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.menu.index')->with('success', __('panel.settings_saved'));
    }

    public function reset()
    {
        Setting::set('menu_items_json', json_encode(SiteMenu::defaults(), JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.menu.index')->with('success', __('panel.settings_saved'));
    }
}
