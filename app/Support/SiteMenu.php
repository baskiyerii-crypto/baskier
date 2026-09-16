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
    public static function allItems(): array
    {
        $raw = Setting::get('menu_items_json');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && $decoded !== []) {
                return collect($decoded)
                    ->filter(fn ($i) => is_array($i) && ! empty($i['label']))
                    ->map(function (array $i) {
                        return [
                            'label' => (string) $i['label'],
                            'label_en' => isset($i['label_en']) && $i['label_en'] !== '' ? (string) $i['label_en'] : null,
                            'type' => (string) ($i['type'] ?? 'route'),
                            'target' => isset($i['target']) && $i['target'] !== '' ? (string) $i['target'] : null,
                            'placement' => in_array(($i['placement'] ?? 'drawer'), ['top', 'drawer'], true) ? $i['placement'] : 'drawer',
                            'sort' => (int) ($i['sort'] ?? 0),
                            'is_active' => (bool) ($i['is_active'] ?? true),
                        ];
                    })
                    ->sortBy(fn ($i) => (int) ($i['sort'] ?? 0))
                    ->values()
                    ->all();
            }
        }

        return self::defaults();
    }

    /** @return list<array{label:string,type:string,target:?string,placement:string,sort:int,is_active:bool}> */
    public static function items(): array
    {
        return collect(self::allItems())
            ->filter(fn ($i) => ($i['is_active'] ?? true))
            ->values()
            ->all();
    }

    public static function forPlacement(string $placement): Collection
    {
        $items = collect(self::items())->filter(fn ($i) => ($i['placement'] ?? 'drawer') === $placement)->values();
        if ($items->isEmpty()) {
            $items = collect(self::defaults())
                ->filter(fn ($i) => ($i['placement'] ?? 'drawer') === $placement && ($i['is_active'] ?? true))
                ->values();
        }

        return $items;
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

    /** @return list<array{value:string,label:string}> */
    public static function routeOptions(): array
    {
        $opts = [
            ['value' => 'home', 'label' => 'Anasayfa'],
            ['value' => 'products.index', 'label' => 'Ürünler'],
            ['value' => 'vendors.index', 'label' => 'Satıcılar'],
            ['value' => 'pages.contact', 'label' => 'İletişim'],
            ['value' => 'freelancer-jobs.index', 'label' => 'İş ilanları'],
            ['value' => 'blog.index', 'label' => 'Blog'],
            ['value' => 'quote-requests.create', 'label' => 'Teklif talebi'],
        ];

        return array_values(array_filter(
            $opts,
            fn ($o) => \Illuminate\Support\Facades\Route::has($o['value'])
        ));
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

    public static function displayLabel(array $item): string
    {
        if (app()->getLocale() === 'en' && ! empty($item['label_en'])) {
            return (string) $item['label_en'];
        }

        if (app()->getLocale() === 'en') {
            $type = $item['type'] ?? 'route';
            $target = $item['target'] ?? null;
            $key = match (true) {
                $type === 'route' && $target === 'home' => 'ui.home',
                $type === 'route' && $target === 'products.index' => 'ui.products',
                $type === 'route' && $target === 'vendors.index' => 'ui.vendors',
                $type === 'route' && $target === 'pages.contact' => 'ui.contact',
                $type === 'route' && $target === 'freelancer-jobs.index' => 'ui.jobs',
                $type === 'route' && $target === 'blog.index' => 'ui.blog',
                $type === 'route' && $target === 'quote-requests.create' => 'ui.quote',
                $type === 'products' => 'ui.products',
                $type === 'categories_accordion' => 'ui.categories',
                default => null,
            };
            if ($key) {
                return __($key);
            }
        }

        return (string) ($item['label'] ?? '');
    }
}
