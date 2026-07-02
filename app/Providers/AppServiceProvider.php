<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $headerCategories = Category::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->where(function ($query): void {
                    $query->whereHas('products')
                        ->orWhereHas('children.products');
                })
                ->with([
                    'children' => function ($query): void {
                        $query->where('is_active', true)
                            ->whereHas('products')
                            ->orderBy('name');
                    },
                ])
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'parent_id']);

            $view->with('headerCategories', $headerCategories);
        });
    }
}
