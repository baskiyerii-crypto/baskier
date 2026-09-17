<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // Static pages
        $staticRoutes = [
            'home' => ['changefreq' => 'daily', 'priority' => '1.0'],
            'products.index' => ['changefreq' => 'daily', 'priority' => '0.9'],
            'vendors.index' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            'service-requests.index' => ['changefreq' => 'daily', 'priority' => '0.8'],
            'blog.index' => ['changefreq' => 'weekly', 'priority' => '0.7'],
            'pages.about' => ['changefreq' => 'monthly', 'priority' => '0.5'],
            'pages.contact' => ['changefreq' => 'monthly', 'priority' => '0.5'],
            'pages.privacy' => ['changefreq' => 'monthly', 'priority' => '0.3'],
            'pages.terms' => ['changefreq' => 'monthly', 'priority' => '0.3'],
        ];

        foreach ($staticRoutes as $name => $meta) {
            $urls[] = [
                'loc' => route($name),
                'lastmod' => date('Y-m-d'),
                'changefreq' => $meta['changefreq'],
                'priority' => $meta['priority'],
            ];
        }

        // Categories (only root/active)
        $categories = Category::query()->whereNotNull('slug')->get();
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => route('products.index', ['category' => $category->slug]),
                'lastmod' => $category->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Active and approved Products
        $products = Product::query()
            ->where('is_active', true)
            ->where('moderation_status', 'approved')
            ->whereNotNull('slug')
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        foreach ($products as $product) {
            $urls[] = [
                'loc' => route('products.show', $product->slug),
                'lastmod' => $product->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Active Vendors
        $vendors = Vendor::query()
            ->where('is_active', true)
            ->where('is_suspended', false)
            ->whereNotNull('slug')
            ->select(['id', 'slug', 'updated_at'])
            ->get();

        foreach ($vendors as $vendor) {
            $urls[] = [
                'loc' => route('vendors.show', $vendor->slug),
                'lastmod' => $vendor->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        // Published blog posts if any
        if (class_exists(BlogPost::class)) {
            $posts = BlogPost::query()
                ->where('is_published', true)
                ->whereNotNull('slug')
                ->select(['id', 'slug', 'updated_at'])
                ->get();

            foreach ($posts as $post) {
                $urls[] = [
                    'loc' => route('blog.show', $post->slug),
                    'lastmod' => $post->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . "</lastmod>\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
