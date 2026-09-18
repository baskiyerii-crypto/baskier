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
        private WorldPlaceService $places,
        private OutdoorLocationInsightService $insights,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<UploadedFile>  $images
     */
    public function create(Vendor $vendor, User $actor, array $payload, array $images = []): OohInventory
    {
        $this->staff->assertCanManageInventory($actor, $vendor);
        $this->assertOutdoorCategory((int) $payload['category_id']);

        return DB::transaction(function () use ($vendor, $actor, $payload, $images) {
            $geo = $this->geoFromPayload($payload);
            $row = [
                'vendor_id' => $vendor->id,
                'category_id' => $payload['category_id'],
                'title' => $payload['title'],
                'slug' => Str::slug($payload['title']).'-'.Str::lower(Str::random(6)),
                'description' => $payload['description'] ?? null,
                'turkiye_il_id' => $geo['turkiye_il_id'],
                'turkiye_ilce_id' => $geo['turkiye_ilce_id'],
                'city' => $geo['city'],
                'district' => $geo['district'],
                'address' => $payload['address'] ?? null,
                'lat' => $payload['lat'],
                'lng' => $payload['lng'],
                'permit_no' => $payload['permit_no'] ?? null,
                'list_price' => $payload['list_price'] ?? null,
                'price_unit' => $payload['price_unit'] ?? OohInventory::UNIT_MONTH,
                'proof_radius_m' => $payload['proof_radius_m'] ?? 75,
                'status' => OohInventory::STATUS_DRAFT,
            ];
            foreach (['face_width_m', 'face_height_m', 'facing', 'illuminated'] as $col) {
                if (Schema::hasColumn('ooh_inventories', $col) && array_key_exists($col, $payload)) {
                    $row[$col] = $payload[$col];
                }
            }
            if (Schema::hasColumn('ooh_inventories', 'created_by_user_id')) {
                $row['created_by_user_id'] = $actor->id;
            }
            if (OutdoorSchema::hasCountryCode()) {
                $row['country_code'] = $geo['country_code'];
            }
            $inventory = OohInventory::create($row);

            $this->storeImages($inventory, $images);
            $this->insights->enrich($inventory);

            return $inventory->load(['images', 'insight']);
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
        $this->staff->assertCanEditInventory($actor, $inventory);
        if (isset($payload['category_id'])) {
            $this->assertOutdoorCategory((int) $payload['category_id']);
        }

        $geo = $this->geoFromPayload($payload);
        unset($payload['images']);
        $inventory->fill($payload);
        $inventory->fill([
            'turkiye_il_id' => $geo['turkiye_il_id'],
            'turkiye_ilce_id' => $geo['turkiye_ilce_id'],
            'city' => $geo['city'],
            'district' => $geo['district'],
        ]);
        if (OutdoorSchema::hasCountryCode()) {
            $inventory->country_code = $geo['country_code'];
        }
        if ($inventory->status === OohInventory::STATUS_PUBLISHED) {
            $inventory->status = OohInventory::STATUS_PENDING_REVIEW;
        }
        $inventory->save();
        $this->storeImages($inventory, $images);
        $this->insights->enrich($inventory->fresh());

        return $inventory->fresh(['images', 'insight']);
    }

    public function delete(OohInventory $inventory, User $actor): void
    {
        $this->staff->assertCanEditInventory($actor, $inventory);
        $inventory->delete();
    }

    public function submitForReview(OohInventory $inventory, User $actor): OohInventory
    {
        $this->staff->assertCanEditInventory($actor, $inventory);
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
    public function publishedCatalog(
        ?int $provinceId,
        ?int $districtId,
        ?int $categoryId,
        ?string $countryCode = null,
        ?string $city = null,
        ?string $districtName = null,
    ) {
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
        if (Schema::hasTable('countries')) {
            $with[] = 'country';
        }

        $countryCode = strtoupper(trim((string) $countryCode));
        $city = trim((string) $city);
        $districtName = trim((string) $districtName);
        if ($countryCode !== '' && ! \App\Support\IsoCountries::isValid($countryCode)) {
            $countryCode = '';
        }
        $applyTrIds = $countryCode === '' || $countryCode === 'TR';

        return OohInventory::query()
            ->with($with)
            ->where('status', OohInventory::STATUS_PUBLISHED)
            ->when(
                $countryCode !== '' && OutdoorSchema::hasCountryCode(),
                fn ($q) => $q->where('country_code', $countryCode)
            )
            ->when($applyTrIds && $provinceId, fn ($q) => $q->where('turkiye_il_id', $provinceId))
            ->when($applyTrIds && $districtId, fn ($q) => $q->where('turkiye_ilce_id', $districtId))
            ->when($city !== '', fn ($q) => $q->where('city', $city))
            ->when($districtName !== '', fn ($q) => $q->where('district', $districtName))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, OohInventory>
     */
    public function poolForVendor(Vendor $vendor, ?int $provinceId = null, ?string $countryCode = null)
    {
        if (! OutdoorSchema::inventoriesReady()) {
            return OutdoorSchema::emptyPaginator();
        }

        $countryCode = strtoupper(trim((string) $countryCode));
        if ($countryCode !== '' && ! \App\Support\IsoCountries::isValid($countryCode)) {
            $countryCode = '';
        }

        return OohInventory::query()
            ->with(['images', 'vendor', 'occupancies'])
            ->where('status', OohInventory::STATUS_PUBLISHED)
            ->where('vendor_id', '!=', $vendor->id)
            ->when(
                $countryCode !== '' && OutdoorSchema::hasCountryCode(),
                fn ($q) => $q->where('country_code', $countryCode)
            )
            ->when(($countryCode === '' || $countryCode === 'TR') && $provinceId, fn ($q) => $q->where('turkiye_il_id', $provinceId))
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

    /**
     * @param  array<string, mixed>  $payload
     * @return array{country_code: string, city: ?string, district: ?string, turkiye_il_id: mixed, turkiye_ilce_id: mixed}
     */
    private function geoFromPayload(array $payload): array
    {
        return $this->places->normalize(
            $payload['country_code'] ?? 'TR',
            $payload['city'] ?? null,
            $payload['district'] ?? null,
            $payload['turkiye_il_id'] ?? null,
            $payload['turkiye_ilce_id'] ?? null,
        );
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
