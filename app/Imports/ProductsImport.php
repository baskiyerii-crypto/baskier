<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function __construct(
        private int $vendorId,
        private bool $canCreateDigital = false
    ) {}

    public function model(array $row)
    {
        $name = $row['name'] ?? $row['urun_adi'] ?? null;
        if (! $name || trim((string) $name) === '') {
            return null;
        }

        $categoryId = (int) ($row['category_id'] ?? $row['kategori_id'] ?? 0);
        if ($categoryId < 1) {
            return null;
        }

        $productType = in_array($row['product_type'] ?? 'physical', ['physical', 'digital'], true)
            ? $row['product_type']
            : 'physical';
        if (! $this->canCreateDigital && $productType === 'digital') {
            $productType = 'physical';
        }

        return new Product([
            'vendor_id' => $this->vendorId,
            'category_id' => $categoryId,
            'name' => trim((string) $name),
            'slug' => Str::slug((string) $name) . '-' . uniqid(),
            'sku' => isset($row['sku']) ? trim((string) $row['sku']) : null,
            'price' => (float) ($row['price'] ?? $row['fiyat'] ?? 0),
            'stock' => (int) ($row['stock'] ?? $row['stok'] ?? 0),
            'is_active' => true,
            'product_type' => $productType,
            'short_description' => isset($row['short_description']) ? (string) $row['short_description'] : null,
        ]);
    }
}
