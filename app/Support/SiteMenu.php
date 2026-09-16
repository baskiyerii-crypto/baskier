<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Collection;

final class SiteMenu
{
    public static function platformName(): string
    {
        return (string) (Setting::get('platform_name') ?: 'BaskıYeri');
    }

    /** @return list<array{label:string,type:string,target:?string,placement:string,sort:int,is_active:bool}> */
    public static function items(): array
    {
        $raw = Setting::get('menu_items_json');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && $decoded !== []) {
                return collect($decoded)
                    ->filter(fn ($i) => is_array($i) && ! empty($i['label']) && ($i['is_active'] ?? true))
                    ->sortBy(fn ($i) => (int) ($i['sort'] ?? 0))
                    ->values()
                    ->all();
            }
        }

        return self::defaults();
    }

    public static function forPlacement(string $placement): Collection
    {
        return collect(self::items())->filter(fn ($i) => ($i['placement'] ?? 'drawer') === $placement)->values();
    }

    public static function defaults(): array
    {
        return [
            ['label' => 'Anasayfa', 'type' => 'route', 'target' => 'home', 'placement' => 'top', 'sort' => 1, 'is_active' => true],
            ['label' => 'Ürünler', 'type' => 'route', 'target' => 'products.index', 'placement' => 'top', 'sort' => 2, 'is_active' => true],
            ['label' => 'Satıcılar', 'type' => 'route', 'target' => 'vendors.index', 'placement' => 'top', 'sort' => 3, 'is_active' => true],
            ['label' => 'İletişim', 'type' => 'route', 'target' => 'pages.contact', 'placement' => 'top', 'sort' => 4, 'is_active' => true],
            ['label' => 'Anasayfa', 'type' => 'route', 'target' => 'home', 'placement' => 'drawer', 'sort' => 1, 'is_active' => true],
            ['label' => 'Ürünler', 'type' => 'route', 'target' => 'products.index', 'placement' => 'drawer', 'sort' => 2, 'is_active' => true],
            ['label' => 'Satıcılar', 'type' => 'route', 'target' => 'vendors.index', 'placement' => 'drawer', 'sort' => 3, 'is_active' => true],
            ['label' => 'İletişim', 'type' => 'route', 'target' => 'pages.contact', 'placement' => 'drawer', 'sort' => 4, 'is_active' => true],
            ['label' => 'İş ilanları', 'type' => 'route', 'target' => 'freelancer-jobs.index', 'placement' => 'drawer', 'sort' => 5, 'is_active' => true],
            ['label' => 'Kategoriler', 'type' => 'categories_accordion', 'target' => null, 'placement' => 'drawer', 'sort' => 10, 'is_active' => true],
        ];
    }

    public static function href(array $item): ?string
    {
        $type = $item['type'] ?? 'route';
        $target = $item['target'] ?? null;

        return match ($type) {
            'route' => $target && \Illuminate\Support\Facades\Route::has($target) ? route($target) : null,
            'url' => $target,
            'category' => $target ? route('products.index', ['category_id' => $target]) : route('products.index'),
            'products' => route('products.index'),
            'page' => $target && \Illuminate\Support\Facades\Route::has('pages.'.$target)
                ? route('pages.'.$target)
                : ($target ? url('/'.$target) : null),
            default => null,
        };
    }
}
