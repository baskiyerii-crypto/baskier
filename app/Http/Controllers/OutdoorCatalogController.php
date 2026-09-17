<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Models\OohInventory;
use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Services\OutdoorInventoryService;
use App\Services\OutdoorOccupancyService;
use App\Services\OutdoorPlanBasket;
use App\Services\WorldPlaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class OutdoorCatalogController extends Controller
{
    public function index(Request $request, OutdoorInventoryService $inventories, WorldPlaceService $places)
    {
        $countryCode = strtoupper(trim((string) $request->query('ulke', '')));
        $city = trim((string) $request->query('sehir', ''));
        $districtName = trim((string) $request->query('ilce_adi', ''));
        $provinceId = $request->integer('il') ?: null;
        $districtId = $request->integer('ilce') ?: null;
        $categoryId = $request->integer('kategori') ?: null;
        if ($provinceId || $districtId) {
            $countryCode = $countryCode !== '' ? $countryCode : 'TR';
        }
        $districts = collect();
        $provinces = collect();
        $categories = collect();
        $countries = Country::catalog();
        $citySuggestions = [];
        $districtSuggestions = [];
        try {
            $items = $inventories->publishedCatalog($provinceId, $districtId, $categoryId, $countryCode ?: null, $city ?: null, $districtName ?: null);
            $provinces = Schema::hasTable('turkiye_iller')
                ? TurkiyeIl::query()->orderBy('name')->get()
                : collect();
            $districts = ($provinceId && Schema::hasTable('turkiye_ilceler'))
                ? TurkiyeIlce::query()->where('province_id', $provinceId)->orderBy('name')->get()
                : collect();
            if (Schema::hasColumn('categories', 'channel')) {
                $categories = Category::query()
                    ->where('channel', Category::CHANNEL_OUTDOOR)
                    ->when(Schema::hasColumn('categories', 'is_active'), fn ($q) => $q->where('is_active', true))
                    ->orderBy('name')
                    ->get();
            }
            if ($countryCode !== '') {
                $citySuggestions = $places->cities($countryCode);
                if ($city !== '') {
                    $districtSuggestions = $places->districts($countryCode, $city);
                }
            }
        } catch (\Throwable $e) {
            report($e);
            $items = \App\Support\OutdoorSchema::emptyPaginator();
        }
        $basket = app(OutdoorPlanBasket::class)->lines($request);

        return view('outdoor.index', compact(
            'items',
            'provinces',
            'districts',
            'categories',
            'countries',
            'provinceId',
            'districtId',
            'categoryId',
            'countryCode',
            'city',
            'districtName',
            'citySuggestions',
            'districtSuggestions',
            'basket'
        ));
    }

    public function show(string $slug, OutdoorOccupancyService $occupancy, OutdoorPlanBasket $basket, Request $request)
    {
        abort_unless(\App\Support\OutdoorSchema::inventoriesReady(), 404);
        $with = ['images', 'vendor', 'category', 'province', 'districtRel'];
        if (Schema::hasTable('countries')) {
            $with[] = 'country';
        }
        $inventory = OohInventory::query()
            ->with($with)
            ->where('slug', $slug)
            ->where('status', OohInventory::STATUS_PUBLISHED)
            ->firstOrFail();
        $calendar = $occupancy->calendar($inventory);
        $lines = $basket->lines($request);

        return view('outdoor.show', compact('inventory', 'calendar', 'lines'));
    }

    public function addToPlan(Request $request, string $slug, OutdoorPlanBasket $basket, OutdoorOccupancyService $occupancy)
    {
        abort_unless(\App\Support\OutdoorSchema::inventoriesReady(), 404);
        $inventory = OohInventory::query()
            ->where('slug', $slug)
            ->where('status', OohInventory::STATUS_PUBLISHED)
            ->firstOrFail();
        $validated = $request->validate([
            'starts_on' => ['required', 'date', 'after_or_equal:today'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);
        $occupancy->assertAvailable(
            $inventory,
            \Carbon\Carbon::parse($validated['starts_on']),
            \Carbon\Carbon::parse($validated['ends_on'])
        );
        $basket->add($request, $inventory->id, $validated['starts_on'], $validated['ends_on'], $inventory->title);

        return redirect()->route('outdoor.show', $inventory->slug)->with('success', 'Pano plan sepetine eklendi.');
    }

    public function removeFromPlan(Request $request, OutdoorPlanBasket $basket)
    {
        $basket->remove($request, (int) $request->input('index', -1));

        return back()->with('success', 'Satır kaldırıldı.');
    }
}
