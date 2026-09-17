<?php

namespace App\Services;

use App\Models\Category;
use App\Models\OohInventory;
use App\Models\User;
use App\Models\Vendor;
use App\Support\OutdoorSchema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class OutdoorInventoryService
{
    public function __construct(
        private OutdoorStaffService $staff,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<UploadedFile>  $images
     */
    public function create(Vendor $vendor, User $actor, array $payload, array $images = []): OohInventory
    {
        $this->staff->assertCanManageInventory($actor, $vendor);
        $this->assertOutdoorCategory((int) $payload['category_id']);

        return DB::transaction(function () use ($vendor, $payload, $images) {
            $inventory = OohInventory::create([
                'vendor_id' => $vendor->id,
                'category_id' => $payload['category_id'],
                'title' => $payload['title'],
                'slug' => Str::slug($payload['title']).'-'.Str::lower(Str::random(6)),
                'description' => $payload['description'] ?? null,
                'turkiye_il_id' => $payload['turkiye_il_id'] ?? null,
                'turkiye_ilce_id' => $payload['turkiye_ilce_id'] ?? null,
                'city' => $payload['city'] ?? null,
                'district' => $payload['district'] ?? null,
                'address' => $payload['address'] ?? null,
                'lat' => $payload['lat'],
                'lng' => $payload['lng'],
                'permit_no' => $payload['permit_no'] ?? null,
                'list_price' => $payload['list_price'] ?? null,
                'price_unit' => $payload['price_unit'] ?? OohInventory::UNIT_MONTH,
                'proof_radius_m' => $payload['proof_radius_m'] ?? 75,
                'status' => OohInventory::STATUS_DRAFT,
            ]);

            $this->storeImages($inventory, $images);

            return $inventory->load('images');
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, OohInventory>
     */
    public function similarListings(OohInventory $inventory)
    {
        if (! OutdoorSchema::hasGeoFingerprint()) {
            return collect();
        }

        return $inventory->similarListings();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<UploadedFile>  $images
     */
    public function update(OohInventory $inventory, User $actor, array $payload, array $images = []): OohInventory
    {
        $this->staff->assertCanManageInventory($actor, $inventory->vendor);
        if (isset($payload['category_id'])) {
            $this->assertOutdoorCategory((int) $payload['category_id']);
        }

        $inventory->fill($payload);
        if ($inventory->status === OohInventory::STATUS_PUBLISHED) {
            $inventory->status = OohInventory::STATUS_PENDING_REVIEW;
        }
        $inventory->save();
        $this->storeImages($inventory, $images);

        return $inventory->fresh('images');
    }

    public function submitForReview(OohInventory $inventory, User $actor): OohInventory
    {
        $this->staff->assertCanManageInventory($actor, $inventory->vendor);
        if (! in_array($inventory->status, [OohInventory::STATUS_DRAFT, OohInventory::STATUS_REJECTED], true)) {
            throw new RuntimeException('Bu envanter incelenmeye gönderilemez.');
        }
        $others = $inventory->similarListings()->where('vendor_id', '!=', $inventory->vendor_id);
        if ($others->isNotEmpty()) {
            throw new RuntimeException('Bu ruhsat ve konum başka bir satıcıda kayıtlı. Çift ilan raporu açın; yayını yönetici karar verir.');
        }
        $inventory->update([
            'status' => OohInventory::STATUS_PENDING_REVIEW,
            'rejection_reason' => null,
        ]);

        return $inventory;
    }

    public function publish(OohInventory $inventory): OohInventory
    {
        $inventory->update([
            'status' => OohInventory::STATUS_PUBLISHED,
            'rejection_reason' => null,
        ]);

        return $inventory;
    }

    public function reject(OohInventory $inventory, string $reason): OohInventory
    {
        $inventory->update([
            'status' => OohInventory::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        return $inventory;
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, OohInventory>
     */
    public function publishedCatalog(?int $provinceId, ?int $districtId, ?int $categoryId)
    {
        if (! OutdoorSchema::inventoriesReady()) {
            return OutdoorSchema::emptyPaginator();
        }

        $with = ['images', 'vendor', 'category'];
        if (Schema::hasTable('turkiye_iller')) {
            $with[] = 'province';
        }
        if (Schema::hasTable('turkiye_ilceler')) {
            $with[] = 'districtRel';
        }

        return OohInventory::query()
            ->with($with)
            ->where('status', OohInventory::STATUS_PUBLISHED)
            ->when($provinceId, fn ($q) => $q->where('turkiye_il_id', $provinceId))
            ->when($districtId, fn ($q) => $q->where('turkiye_ilce_id', $districtId))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, OohInventory>
     */
    public function poolForVendor(Vendor $vendor, ?int $provinceId = null)
    {
        if (! OutdoorSchema::inventoriesReady()) {
            return OutdoorSchema::emptyPaginator();
        }

        return OohInventory::query()
            ->with(['images', 'vendor', 'occupancies'])
            ->where('status', OohInventory::STATUS_PUBLISHED)
            ->where('vendor_id', '!=', $vendor->id)
            ->when($provinceId, fn ($q) => $q->where('turkiye_il_id', $provinceId))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @param  list<UploadedFile>  $images
     */
    private function storeImages(OohInventory $inventory, array $images): void
    {
        $order = (int) $inventory->images()->max('sort_order');
        foreach ($images as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $order++;
            $path = $file->store('ooh/inventories/'.$inventory->id, 'public');
            $inventory->images()->create([
                'path' => $path,
                'sort_order' => $order,
            ]);
        }
    }

    private function assertOutdoorCategory(int $categoryId): void
    {
        $ok = Category::query()
            ->whereKey($categoryId)
            ->where('channel', Category::CHANNEL_OUTDOOR)
            ->where('is_active', true)
            ->exists();
        if (! $ok) {
            throw new RuntimeException('Yalnızca yönetici outdoor kategorileri kullanılabilir.');
        }
    }
}
