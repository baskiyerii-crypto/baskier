<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OutdoorInventoryExport implements FromArray, WithHeadings
{
    /**
     * @param  list<array<int, string|int|float|null>>  $rows
     */
    public function __construct(private array $rows) {}

    public function headings(): array
    {
        return [
            'id',
            'title',
            'category_id',
            'description',
            'country_code',
            'city',
            'district',
            'address',
            'lat',
            'lng',
            'permit_no',
            'list_price',
            'price_unit',
            'face_width_m',
            'face_height_m',
            'facing',
            'illuminated',
            'image_files',
            'status',
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }
}
