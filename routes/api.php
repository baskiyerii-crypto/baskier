<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AdminPanelController;
use App\Http\Controllers\Api\V1\AdminPayoutRequestController;
use App\Http\Controllers\Api\V1\AdminSupportTicketController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\CustomerDashboardController;
use App\Http\Controllers\Api\V1\DesignApprovalController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\FreelancerJobController;
use App\Http\Controllers\Api\V1\GeographyController;
use App\Http\Controllers\Api\V1\HomeContentController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PageContentController;
use App\Http\Controllers\Api\V1\PriceEstimateController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuoteRequestController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\Api\V1\VendorController;
use App\Http\Controllers\Api\V1\VendorPanelController;
use App\Http\Controllers\Api\V1\VendorPayoutController;
use App\Http\Controllers\Api\V1\VendorProductController;
use App\Http\Controllers\Api\V1\VendorQuoteController;
use App\Http\Controllers\Api\V1\VendorOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/home', [HomeContentController::class, 'index']);
    Route::get('/pages/{slug}', [PageContentController::class, 'show'])->where('slug', 'privacy|terms|about');

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/business-types', [CategoryController::class, 'businessTypes']);
    Route::get('/geography/provinces', [GeographyController::class, 'provinces']);
    Route::get('/geography/provinces/{provinceId}/districts', [GeographyController::class, 'districts'])->whereNumber('provinceId');
    Route::get('/geography/districts/{districtId}/neighborhoods', [GeographyController::class, 'neighborhoods'])->whereNumber('districtId');
    Route::get('/geography/postal-lookup', [GeographyController::class, 'postalLookup']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/price-estimate/bundle', [PriceEstimateController::class, 'bundle']);
    Route::get('/products/{slug}/price-estimate', [ProductController::class, 'priceEstimate']);
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
        Route::get('/orders/{order}/design-approvals', [DesignApprovalController::class, 'index']);
        Route::post('/orders/{order}/reviews', [OrderController::class, 'review']);
        Route::post('/orders/{order}/conversation', [ConversationController::class, 'openOrCreateForOrder']);
        Route::post('/design-approvals/{designApproval}/approve', [DesignApprovalController::class, 'approve']);
        Route::post('/design-approvals/{designApproval}/revision', [DesignApprovalController::class, 'revision']);

        Route::get('/support-tickets', [SupportTicketController::class, 'index']);
        Route::post('/support-tickets', [SupportTicketController::class, 'store']);
        Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show']);
        Route::post('/support-tickets/{supportTicket}/reply', [SupportTicketController::class, 'reply']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

        Route::get('/conversations', [ConversationController::class, 'index']);
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
        Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage']);

        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/toggle/{product}', [FavoriteController::class, 'toggle']);

        Route::get('/quote-requests', [QuoteRequestController::class, 'index']);
        Route::post('/quote-requests', [QuoteRequestController::class, 'store']);
        Route::get('/quote-requests/{quoteRequest}', [QuoteRequestController::class, 'show']);
        Route::post('/quote-requests/{quoteRequest}/files', [QuoteRequestController::class, 'storeFiles']);
        Route::post('/quote-requests/{quoteRequest}/quotes/{quote}/select', [QuoteRequestController::class, 'selectQuote']);

        Route::post('/freelancer-jobs', [FreelancerJobController::class, 'store']);
        Route::post('/freelancer-jobs/{job}/bids', [FreelancerJobController::class, 'storeBid']);
        Route::post('/freelancer-jobs/{job}/bids/{bid}/select', [FreelancerJobController::class, 'selectBid']);

        Route::middleware('role:vendor')->prefix('vendor')->group(function () {
            Route::get('/dashboard', [VendorPanelController::class, 'dashboard']);
            Route::get('/products', [VendorProductController::class, 'index']);
            Route::post('/products', [VendorProductController::class, 'store']);
            Route::put('/products/{product}', [VendorProductController::class, 'update']);
            Route::delete('/products/{product}', [VendorProductController::class, 'destroy']);
            Route::get('/orders', [VendorPanelController::class, 'orders']);
            Route::get('/orders/{order}', [VendorOrderController::class, 'show']);
            Route::patch('/orders/{order}/status', [VendorPanelController::class, 'updateOrderStatus']);
            Route::post('/orders/{order}/design-approvals', [DesignApprovalController::class, 'vendorStore']);
            Route::get('/matched-quote-requests', [VendorPanelController::class, 'matchedQuoteRequests']);
            Route::post('/quote-requests/{quoteRequest}/meeting', [VendorQuoteController::class, 'purchaseMeeting']);
            Route::post('/quote-requests/{quoteRequest}/quotes', [VendorQuoteController::class, 'store']);
            Route::get('/payout-requests', [VendorPayoutController::class, 'index']);
            Route::post('/payout-requests', [VendorPayoutController::class, 'store']);
        });

        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminPanelController::class, 'dashboard']);
            Route::get('/support-tickets', [AdminSupportTicketController::class, 'index']);
            Route::get('/support-tickets/{supportTicket}', [AdminSupportTicketController::class, 'show']);
            Route::patch('/support-tickets/{supportTicket}/status', [AdminSupportTicketController::class, 'updateStatus']);
            Route::post('/support-tickets/{supportTicket}/assign', [AdminSupportTicketController::class, 'assign']);
            Route::post('/support-tickets/{supportTicket}/reply', [AdminSupportTicketController::class, 'reply']);
            Route::get('/payout-requests', [AdminPayoutRequestController::class, 'index']);
            Route::post('/payout-requests/{payoutRequest}/approve', [AdminPayoutRequestController::class, 'approve']);
            Route::post('/payout-requests/{payoutRequest}/reject', [AdminPayoutRequestController::class, 'reject']);
        });
    });
});
