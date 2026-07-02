<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\CustomerDashboardController;
use App\Http\Controllers\Api\V1\AdminPanelController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HomeContentController;
use App\Http\Controllers\Api\V1\PageContentController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\FreelancerJobController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuoteRequestController;
use App\Http\Controllers\Api\V1\VendorController;
use App\Http\Controllers\Api\V1\VendorPanelController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/home', [HomeContentController::class, 'index']);
    Route::get('/pages/{slug}', [PageContentController::class, 'show'])->where('slug', 'privacy|terms|about');

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/business-types', [CategoryController::class, 'businessTypes']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::get('/vendors', [VendorController::class, 'index']);
    Route::get('/vendors/{slug}', [VendorController::class, 'show']);
    Route::get('/freelancer-jobs', [FreelancerJobController::class, 'index']);
    Route::middleware('auth:sanctum')->get('/freelancer-jobs/mine', [FreelancerJobController::class, 'myListings']);
    Route::get('/freelancer-jobs/{job}', [FreelancerJobController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/user', [AuthController::class, 'user']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::middleware('role:customer')->get('/customer/dashboard', [CustomerDashboardController::class, 'index']);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/items', [CartController::class, 'add']);
        Route::put('/cart/items/{cartItem}', [CartController::class, 'update']);
        Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove']);

        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

        Route::post('/checkout', [CheckoutController::class, 'store']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/reviews', [OrderController::class, 'review']);

        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/toggle/{product}', [FavoriteController::class, 'toggle']);

        Route::get('/quote-requests', [QuoteRequestController::class, 'index']);
        Route::post('/quote-requests', [QuoteRequestController::class, 'store']);
        Route::get('/quote-requests/{quoteRequest}', [QuoteRequestController::class, 'show']);
        Route::post('/quote-requests/{quoteRequest}/quotes/{quote}/select', [QuoteRequestController::class, 'selectQuote']);

        Route::post('/freelancer-jobs', [FreelancerJobController::class, 'store']);
        Route::post('/freelancer-jobs/{job}/bids', [FreelancerJobController::class, 'storeBid']);
        Route::post('/freelancer-jobs/{job}/bids/{bid}/select', [FreelancerJobController::class, 'selectBid']);

        Route::middleware('role:vendor')->prefix('vendor')->group(function () {
            Route::get('/dashboard', [VendorPanelController::class, 'dashboard']);
            Route::get('/products', [VendorPanelController::class, 'products']);
            Route::get('/orders', [VendorPanelController::class, 'orders']);
        });

        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminPanelController::class, 'dashboard']);
        });
    });
});
