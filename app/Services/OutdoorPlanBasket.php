<?php

namespace App\Services;

use Illuminate\Http\Request;

class OutdoorPlanBasket
{
    public const KEY = 'ooh_plan_basket';

    /**
     * @return list<array{inventory_id:int, starts_on:string, ends_on:string, title?:string}>
     */
    public function lines(Request $request): array
    {
        $lines = $request->session()->get(self::KEY, []);

        return is_array($lines) ? array_values($lines) : [];
    }

    public function add(Request $request, int $inventoryId, string $startsOn, string $endsOn, ?string $title = null): void
    {
        $lines = $this->lines($request);
        $lines[] = [
            'inventory_id' => $inventoryId,
            'starts_on' => $startsOn,
            'ends_on' => $endsOn,
            'title' => $title,
        ];
        $request->session()->put(self::KEY, $lines);
    }

    public function remove(Request $request, int $index): void
    {
        $lines = $this->lines($request);
        unset($lines[$index]);
        $request->session()->put(self::KEY, array_values($lines));
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(self::KEY);
    }
}
