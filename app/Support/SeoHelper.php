<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\BlogPost;

class SeoHelper
{
    public const SITE_NAME = 'BaskıYeri';
    public const DEFAULT_TITLE = 'BaskıYeri – Matbaa, Baskı, Promosyon ve Özel Üretim Pazaryeri';
    public const DEFAULT_DESC = 'BaskıYeri; kartvizit, broşür, etiket, kutu, promosyon, tabela ve özel üretim baskı işleriniz için güvenilir üreticileri ve freelancer tasarımcıları buluşturan modern pazaryeri.';

    public static function title(?string $title = null): string
    {
        if (empty($title)) {
            return self::DEFAULT_TITLE;
        }

        if (str_contains($title, self::SITE_NAME)) {
            return $title;
        }

        return $title . ' – ' . self::SITE_NAME;
    }

    public static function description(?string $desc = null): string
    {
        if (empty($desc)) {
            return self::DEFAULT_DESC;
        }

        $clean = strip_tags($desc);
        $clean = preg_replace('/\s+/', ' ', $clean);
        return mb_substr(trim($clean), 0, 160);
    }

    public static function shouldIndex(): bool
    {
        $request = request();

        // Admin, vendor or customer panels
        if ($request->is('admin*') || $request->is('satici-panel*') || $request->is('hesabim*') || $request->is('hesap*')) {
            return false;
        }

        // Checkout, cart, auth, private actions
        if ($request->is('odeme*') || $request->is('sepet*') || $request->is('giris') || $request->is('kayit') || $request->is('dogrulama*') || $request->is('favorilerim*')) {
            return false;
        }

        // Quote requests submission / private views
        if ($request->is('teklif-talepleri*')) {
            return false;
        }

        // Search queries or heavy filter combinations on catalog
        if ($request->has('q') && trim($request->input('q')) !== '') {
            return false;
        }

        // Complex multi-filters on catalog: if filtering by multiple arbitrary params
        $filterParams = ['min_price', 'max_price', 'sort', 'material', 'tag'];
        foreach ($filterParams as $param) {
            if ($request->has($param)) {
                return false;
            }
        }

        return true;
    }

    public static function canonicalUrl(?string $custom = null): string
    {
        if (!empty($custom)) {
            return $custom;
        }

        $request = request();
        $root = config('app.url', $request->schemeAndHttpHost());

        // Products catalog
        if ($request->routeIs('products.index')) {
            $params = [];
            // Preserve category slug if present
            if ($request->filled('category')) {
                $params['category'] = $request->input('category');
            } elseif ($request->filled('category_id')) {
                // If category_id used, resolve to category slug if possible
                $cat = Category::find($request->input('category_id'));
                if ($cat && $cat->slug) {
                    $params['category'] = $cat->slug;
                } else {
                    $params['category_id'] = $request->input('category_id');
                }
            }
            if ($request->filled('type') && in_array($request->input('type'), ['digital', 'service'])) {
                $params['type'] = $request->input('type');
            }
            // Preserve page number for pagination so every page has its own canonical
            if ($request->filled('page') && (int) $request->input('page') > 1) {
                $params['page'] = (int) $request->input('page');
            }

            return url()->route('products.index', $params);
        }

        // Vendors catalog
        if ($request->routeIs('vendors.index')) {
            $params = [];
            if ($request->filled('page') && (int) $request->input('page') > 1) {
                $params['page'] = (int) $request->input('page');
            }
            return url()->route('vendors.index', $params);
        }

        // Blog catalog
        if ($request->routeIs('blog.index')) {
            $params = [];
            if ($request->filled('page') && (int) $request->input('page') > 1) {
                $params['page'] = (int) $request->input('page');
            }
            return url()->route('blog.index', $params);
        }

        // Product show
        if ($request->routeIs('products.show')) {
            $product = $request->route('slug');
            if ($product instanceof Product) {
                return route('products.show', $product->slug);
            }
            return url()->current();
        }

        // Vendor show
        if ($request->routeIs('vendors.show')) {
            $vendor = $request->route('slug');
            if ($vendor instanceof Vendor) {
                return route('vendors.show', $vendor->slug);
            }
            return url()->current();
        }

        return url()->current();
    }

    public static function organizationJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => self::SITE_NAME,
            'url' => config('app.url', url('/')),
            'logo' => url('/icons/icon-512.png'),
            'description' => self::DEFAULT_DESC,
        ];
    }

    public static function productJsonLd(Product $product): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->localizedName(),
            'description' => self::description($product->description),
            'url' => route('products.show', $product->slug),
        ];

        if ($product->main_image) {
            $schema['image'] = [asset('storage/' . $product->main_image)];
        }

        if ($product->category) {
            $schema['category'] = $product->category->localizedName();
        }

        if ($product->vendor) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $product->vendor->name,
            ];
        }

        // Only add genuine price / offer data from DB, never fabricated
        if (!$product->isQuoteBased() && $product->price > 0) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => number_format((float) $product->price, 2, '.', ''),
                'priceCurrency' => 'TRY',
                'availability' => ((int) $product->stock > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => route('products.show', $product->slug),
            ];
        }

        return $schema;
    }

    public static function breadcrumbsJsonLd(array $items): array
    {
        $elements = [];
        $pos = 1;
        foreach ($items as $name => $url) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => $name,
                'item' => $url,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }
}
