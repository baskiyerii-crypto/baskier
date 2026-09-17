<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

final class OutdoorSchema
{
    public static function inventoriesReady(): bool
    {
        return Schema::hasTable('ooh_inventories');
    }

    public static function plansReady(): bool
    {
        return Schema::hasTable('ooh_plans');
    }

    public static function membersReady(): bool
    {
        return Schema::hasTable('vendor_members');
    }

    public static function occupanciesReady(): bool
    {
        return Schema::hasTable('ooh_occupancies');
    }

    public static function hasGeoFingerprint(): bool
    {
        return self::inventoriesReady() && Schema::hasColumn('ooh_inventories', 'geo_fingerprint');
    }

    public static function vendorModuleColumnsReady(): bool
    {
        return Schema::hasColumn('vendors', 'outdoor_enabled');
    }

    public static function abortIfVendorPanelUnavailable(): void
    {
        if (! self::inventoriesReady() || ! self::membersReady()) {
            abort(403, 'Açık hava şeması henüz kurulmadı.');
        }
    }

    /**
     * @return LengthAwarePaginator<int, mixed>
     */
    public static function emptyPaginator(int $perPage = 20): LengthAwarePaginator
    {
        return (new LengthAwarePaginator([], 0, $perPage))->withQueryString();
    }
}
