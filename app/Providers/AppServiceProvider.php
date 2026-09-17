<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            return Limit::perMinute(5)->by($request->ip().'|'.$email)
                ->response(function () {
                    return response()->json([
                        'message' => 'Çok fazla giriş denemesi yapıldı. Lütfen 1 dakika sonra tekrar deneyin.',
                    ], 429);
                });
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(5)->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Çok fazla kayıt denemesi yapıldı. Lütfen daha sonra tekrar deneyin.',
                    ], 429);
                });
        });

        RateLimiter::for('checkout', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(10)->by($key);
        });

        RateLimiter::for('bids', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(20)->by($key);
        });

        RateLimiter::for('document_upload', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(10)->by($key);
        });

        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(60)->by($key);
        });
    }
}
