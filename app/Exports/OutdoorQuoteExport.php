<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OutdoorQuoteExport implements FromArray, WithHeadings
{
    /**
     * @param  list<array<int, string|int|float|null>>  $rows
     */
    public function __construct(private array $rows) {}

    public function headings(): array
    {
        return [
            'Plan',
            'Karsi unvan',
            'Durum',
            'Tutar',
            'Baslangic',
            'Bitis',
            'Pano',
            'Ruhsat',
            'Il',
            'Ilce',
            'Lat',
            'Lng',
            'Harita',
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }
}
