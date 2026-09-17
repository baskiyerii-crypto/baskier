<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OohInventory;
use App\Models\TurkiyeIl;
use App\Models\TurkiyeIlce;
use App\Services\OutdoorInventoryService;
use App\Services\OutdoorOccupancyService;
use App\Services\OutdoorStaffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class VendorOutdoorInventoryController extends Controller
{
    public function __construct(
        private OutdoorStaffService $staff,
        private OutdoorInventoryService $inventories,
        private OutdoorOccupancyService $occupancy,
    ) {}

    private function vendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor || ! $vendor->hasActiveOutdoorModule()) {
            abort(403, 'Açık hava modülü aktif değil.');
        }
        $this->staff->ensureOwner($vendor);

        return $vendor;
    }

    public function index(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $items = $vendor->oohInventories()->with('images', 'category')->latest()->paginate(20);
        $role = $this->staff->roleFor($request->user(), $vendor);

        return view('vendor.outdoor.inventories-index', compact('vendor', 'items', 'role'));
    }

    public function create(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->authorize('create', OohInventory::class);
        $this->staff->assertCanManageInventory($request->user(), $vendor);
        $categories = Category::query()->where('channel', Category::CHANNEL_OUTDOOR)->where('is_active', true)->orderBy('name')->get();
        $provinces = Schema::hasTable('turkiye_iller') ? TurkiyeIl::query()->orderBy('name')->get() : collect();
        $districts = collect();

        return view('vendor.outdoor.inventories-form', compact('vendor', 'categories', 'provinces', 'districts'));
    }

    public function store(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->authorize('create', OohInventory::class);
        $validated = $this->rules($request);
        try {
            $inventory = $this->inventories->create($vendor, $request->user(), $validated, $request->file('images', []) ?: []);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return $this->afterSaveRedirect($inventory, 'Envanter kaydedildi.');
    }

    public function edit(Request $request, OohInventory $inventory)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $inventory->vendor_id === (int) $vendor->id, 403);
        $this->authorize('update', $inventory);
        $this->staff->assertCanManageInventory($request->user(), $vendor);
        $inventory->load('images');
        $categories = Category::query()->where('channel', Category::CHANNEL_OUTDOOR)->where('is_active', true)->orderBy('name')->get();
        $provinces = Schema::hasTable('turkiye_iller') ? TurkiyeIl::query()->orderBy('name')->get() : collect();
        $provinceId = old('turkiye_il_id', $inventory->turkiye_il_id);
        $districts = ($provinceId && Schema::hasTable('turkiye_ilceler'))
            ? TurkiyeIlce::query()->where('province_id', $provinceId)->orderBy('name')->get()
            : collect();
        $calendar = $this->occupancy->calendar($inventory);
        $similar = $this->inventories->similarListings($inventory);

        return view('vendor.outdoor.inventories-form', compact('vendor', 'inventory', 'categories', 'provinces', 'districts', 'calendar', 'similar'));
    }

    public function update(Request $request, OohInventory $inventory)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $inventory->vendor_id === (int) $vendor->id, 403);
        $this->authorize('update', $inventory);
        $validated = $this->rules($request);
        try {
            $this->inventories->update($inventory, $request->user(), $validated, $request->file('images', []) ?: []);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return $this->afterSaveRedirect($inventory->fresh(), 'Envanter güncellendi.');
    }

    public function submit(Request $request, OohInventory $inventory)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $inventory->vendor_id === (int) $vendor->id, 403);
        $this->authorize('update', $inventory);
        try {
            $this->inventories->submitForReview($inventory, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'İncelemeye gönderildi.');
    }

    public function block(Request $request, OohInventory $inventory)
    {
        $vendor = $this->vendor($request);
        abort_unless((int) $inventory->vendor_id === (int) $vendor->id, 403);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $validated = $request->validate([
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);
        try {
            $this->occupancy->block(
                $inventory,
                \Carbon\Carbon::parse($validated['starts_on']),
                \Carbon\Carbon::parse($validated['ends_on'])
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Tarih aralığı bloke edildi.');
    }

    public function pool(Request $request)
    {
        $vendor = $this->vendor($request);
        $this->staff->assertCanOperate($request->user(), $vendor);
        $items = $this->inventories->poolForVendor($vendor, $request->integer('il') ?: null);

        return view('vendor.outdoor.pool', compact('vendor', 'items'));
    }

    private function afterSaveRedirect(OohInventory $inventory, string $success)
    {
        $similar = $this->inventories->similarListings($inventory);
        $redirect = redirect()->route('vendor.outdoor.inventories.edit', $inventory)->with('success', $success);
        if ($similar->isNotEmpty()) {
            $titles = $similar->pluck('title')->take(3)->implode(', ');
            $redirect->with('warning', 'Aynı ruhsat/konumda başka ilan var: '.$titles.'. Çift ilan raporu açabilir veya yönetici kararına bırakabilirsiniz.');
        }

        return $redirect;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'turkiye_il_id' => ['nullable', 'integer'],
            'turkiye_ilce_id' => ['nullable', 'integer'],
            'city' => ['nullable', 'string', 'max:80'],
            'district' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'permit_no' => ['nullable', 'string', 'max:64'],
            'list_price' => ['nullable', 'numeric', 'min:0'],
            'price_unit' => ['required', 'in:day,week,month'],
            'proof_radius_m' => ['nullable', 'integer', 'min:10', 'max:500'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
        ]);
    }
}
