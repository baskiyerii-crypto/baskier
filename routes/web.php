<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\Admin\AdminApiManagementController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminBusinessTypeController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminContractController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminPayoutController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminProductModerationController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminSupportTicketWebController;
use App\Http\Controllers\Admin\AdminVendorController;
use App\Http\Controllers\Admin\AdminVendorPayoutRequestController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\Customer\CustomerAddressController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerOrderDesignController;
use App\Http\Controllers\Customer\CustomerPriceEstimateController;
use App\Http\Controllers\Customer\CustomerReviewController;
use App\Http\Controllers\Customer\CustomerSupportTicketController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FreelancerJobController;
use App\Http\Controllers\FreelancerJobWebController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Vendor\VendorBalanceController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorDocumentController;
use App\Http\Controllers\Vendor\VendorFreelancerController;
use App\Http\Controllers\Vendor\VendorMessageController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorOrderDesignController;
use App\Http\Controllers\Vendor\VendorPayoutRequestWebController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\Vendor\VendorQuoteRequestController;
use App\Http\Controllers\Vendor\VendorSubscriptionController;
use App\Http\Controllers\Vendor\VendorContractController;
use App\Http\Controllers\Vendor\VendorCategoryRequestController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminVendorUpdateController;
use App\Http\Controllers\Admin\AdminVendorCategoryRequestController;
use App\Http\Controllers\Customer\CustomerDirectQuoteController;
use App\Http\Controllers\Vendor\VendorDirectQuoteController;
use App\Http\Controllers\Vendor\VendorProfileController;

// Genel
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/dil/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['tr', 'en'])
    ->name('locale.switch');
Route::get('/urunler', [ProductController::class, 'index'])->name('products.index');
Route::get('/urun/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/saticilar', [VendorController::class, 'index'])->name('vendors.index');
Route::get('/satici/{slug}', [VendorController::class, 'show'])->name('vendors.show');
Route::get('/gizlilik', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/kullanim-kosullari', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/hakkimizda', [PageController::class, 'about'])->name('pages.about');
Route::get('/iletisim', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/sozlesme/{key}', [ContractController::class, 'show'])->name('contracts.show');

Route::get('/is-ilanlari', [FreelancerJobController::class, 'index'])->name('freelancer-jobs.index');
Route::middleware('auth')->group(function () {
    Route::get('/is-ilanlari/yeni', [FreelancerJobWebController::class, 'create'])->name('freelancer-jobs.create');
    Route::post('/is-ilanlari', [FreelancerJobWebController::class, 'store'])->name('freelancer-jobs.store');
    Route::get('/hesap/is-ilanlarim', [FreelancerJobWebController::class, 'myListings'])->name('freelancer-jobs.my');
    Route::post('/is-ilanlari/{job}/teklif', [FreelancerJobWebController::class, 'storeBid'])->name('freelancer-jobs.bid');
    Route::post('/is-ilanlari/{job}/teklif/{bid}/sec', [FreelancerJobWebController::class, 'selectBid'])->name('freelancer-jobs.select-bid');
});
Route::get('/is-ilanlari/{job}', [FreelancerJobController::class, 'show'])->name('freelancer-jobs.show');

// Auth (misafir)
Route::middleware('guest')->group(function () {
    Route::get('/giris', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/giris', [AuthController::class, 'login']);
    Route::get('/kayit', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/kayit', [AuthController::class, 'register']);
});
Route::post('/cikis', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Teklif talebi oluştur (misafir de formu doldurabilir; gönderim için üye/giriş istenir)
Route::get('teklif-talebi', [QuoteRequestController::class, 'create'])->name('quote-requests.create');
Route::post('teklif-talebi', [QuoteRequestController::class, 'store'])->name('quote-requests.store');
// Teklif taleplerim, detay, teklif seç (giriş gerekli)
Route::middleware('auth')->prefix('teklif-talepleri')->name('quote-requests.')->group(function () {
    Route::get('/', [QuoteRequestController::class, 'index'])->name('index');
    Route::post('{quoteRequest}/dosyalar', [QuoteRequestController::class, 'storeFiles'])->name('store-files');
    Route::get('{quoteRequest}', [QuoteRequestController::class, 'show'])->name('show');
    Route::post('{quoteRequest}/teklif/{quote}/sec', [QuoteRequestController::class, 'selectQuote'])->name('select-quote');
});

Route::middleware('auth')->group(function () {
    Route::get('/sepet', [CartController::class, 'index'])->name('cart.index');
    Route::post('/sepet/urun/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::put('/sepet/kalem/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/sepet/kalem/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/odeme', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/odeme', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/odeme/shopify/{order}', [CheckoutController::class, 'shopifyReturn'])->name('checkout.shopify.return');
    Route::get('/favorilerim', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favoriler/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::prefix('hesap')->name('account.')->group(function () {
        Route::get('/siparisler', [CustomerOrderController::class, 'index'])->name('orders.index');
        Route::get('/siparisler/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
        Route::post('/siparisler/{order}/degerlendirme', [CustomerReviewController::class, 'store'])->name('orders.review');
        Route::post('/siparisler/{order}/tasarim/{designApproval}/onay', [CustomerOrderDesignController::class, 'approve'])->name('orders.design.approve');
        Route::post('/siparisler/{order}/tasarim/{designApproval}/revizyon', [CustomerOrderDesignController::class, 'revision'])->name('orders.design.revision');
        Route::prefix('destek')->name('support.')->group(function () {
            Route::get('/', [CustomerSupportTicketController::class, 'index'])->name('index');
            Route::get('/yeni', [CustomerSupportTicketController::class, 'create'])->name('create');
            Route::post('/', [CustomerSupportTicketController::class, 'store'])->name('store');
            Route::get('/{supportTicket}', [CustomerSupportTicketController::class, 'show'])->name('show');
            Route::post('/{supportTicket}/yanit', [CustomerSupportTicketController::class, 'reply'])->name('reply');
        });
        Route::resource('adresler', CustomerAddressController::class)
            ->except(['show'])
            ->parameters(['adresler' => 'address']);
        Route::post('adresler/{address}/varsayilan', [CustomerAddressController::class, 'setDefault'])->name('adresler.set-default');
        Route::post('adresler/{address}/fatura-varsayilan', [CustomerAddressController::class, 'setBillingDefault'])->name('adresler.set-billing-default');
    });
});

// Müşteri paneli (giriş yapmış, rol fark etmez ama dashboard müşteri için)
Route::middleware(['auth', 'role:customer'])->prefix('hesabim')->name('customer.')->group(function () {
    Route::get('/', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/fiyat-tahmini', [CustomerPriceEstimateController::class, 'index'])->name('price-estimate');
    Route::get('/bireysel-teklifler', [CustomerDirectQuoteController::class, 'index'])->name('direct-quotes.index');
    Route::get('/bireysel-teklifler/yeni', [CustomerDirectQuoteController::class, 'create'])->name('direct-quotes.create');
    Route::post('/bireysel-teklifler', [CustomerDirectQuoteController::class, 'store'])->name('direct-quotes.store');
    Route::get('/bireysel-teklifler/{directQuote}', [CustomerDirectQuoteController::class, 'show'])->name('direct-quotes.show');
    Route::post('/bireysel-teklifler/{directQuote}/kabul', [CustomerDirectQuoteController::class, 'accept'])->name('direct-quotes.accept');
});

// Satıcı paneli
Route::middleware(['auth', 'role:vendor'])->prefix('satici-panel')->name('vendor.')->group(function () {
    Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/urunler', [VendorProductController::class, 'index'])->name('products.index');
    Route::get('/urunler/sablon', [VendorProductController::class, 'downloadTemplate'])->name('products.template');
    Route::post('/urunler/import', [VendorProductController::class, 'import'])->name('products.import');
    Route::get('/urunler/ekle', [VendorProductController::class, 'create'])->name('products.create');
    Route::post('/urunler', [VendorProductController::class, 'store'])->name('products.store');
    Route::get('/urunler/{product}/duzenle', [VendorProductController::class, 'edit'])->name('products.edit');
    Route::put('/urunler/{product}', [VendorProductController::class, 'update'])->name('products.update');
    Route::delete('/urunler/{product}', [VendorProductController::class, 'destroy'])->name('products.destroy');
    Route::get('teklif-talepleri', [VendorQuoteRequestController::class, 'index'])->name('quote-requests.index');
    Route::get('teklif-talepleri/{quoteRequest}', [VendorQuoteRequestController::class, 'show'])->name('quote-requests.show');
    Route::post('teklif-talepleri/{quoteRequest}/gorusme', [VendorQuoteRequestController::class, 'acceptMeeting'])->name('quote-requests.accept-meeting');
    Route::post('teklif-talepleri/{quoteRequest}/teklif', [VendorQuoteRequestController::class, 'submitQuote'])->name('quote-requests.submit-quote');
    Route::get('siparisler', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::get('siparisler/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
    Route::post('siparisler/{order}/tasarim', [VendorOrderDesignController::class, 'store'])->name('orders.design.store');
    Route::put('siparisler/{order}/durum', [VendorOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('odeme-talepleri', [VendorPayoutRequestWebController::class, 'index'])->name('payout-requests.index');
    Route::post('odeme-talepleri', [VendorPayoutRequestWebController::class, 'store'])->name('payout-requests.store');
    Route::get('bakiye', [VendorBalanceController::class, 'index'])->name('balance.index');
    Route::post('bakiye', [VendorBalanceController::class, 'topUp'])->name('balance.topup');
    Route::get('mesajlar', [VendorMessageController::class, 'index'])->name('messages.index');
    Route::get('mesajlar/{conversation}', [VendorMessageController::class, 'show'])->name('messages.show');
    Route::post('mesajlar/{conversation}', [VendorMessageController::class, 'store'])->name('messages.store');
    Route::get('belgeler', [VendorDocumentController::class, 'index'])->name('documents.index');
    Route::post('belgeler', [VendorDocumentController::class, 'store'])->name('documents.store');
    Route::get('freelancerim', [VendorFreelancerController::class, 'index'])->name('freelancer.index');
    Route::get('tabela', [VendorTabelaController::class, 'index'])->name('tabela.index');
    Route::post('tabela/gorusme', [VendorTabelaController::class, 'startMeeting'])->name('tabela.meeting');
    Route::get('abonelikler', [VendorSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('abonelikler/aktiflestir', [VendorSubscriptionController::class, 'activate'])->name('subscriptions.activate');
    Route::get('sozlesmeler', [VendorContractController::class, 'index'])->name('contracts.index');
    Route::post('sozlesmeler/{acceptance}/onayla', [VendorContractController::class, 'accept'])->name('contracts.accept');
    Route::get('kategorilerim', [VendorCategoryRequestController::class, 'index'])->name('categories.index');
    Route::post('kategorilerim', [VendorCategoryRequestController::class, 'store'])->name('categories.store');
    Route::get('profil', [VendorProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profil', [VendorProfileController::class, 'update'])->name('profile.update');
    Route::get('bireysel-teklifler', [VendorDirectQuoteController::class, 'index'])->name('direct-quotes.index');
    Route::get('bireysel-teklifler/{directQuote}', [VendorDirectQuoteController::class, 'show'])->name('direct-quotes.show');
    Route::post('bireysel-teklifler/{directQuote}/teklif', [VendorDirectQuoteController::class, 'offer'])->name('direct-quotes.offer');
    Route::post('bireysel-teklifler/{directQuote}/iletisim', [VendorDirectQuoteController::class, 'consent'])->name('direct-quotes.consent');
});

// Yönetici paneli
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', AdminCategoryController::class)->except('show');
    Route::resource('business-types', AdminBusinessTypeController::class)->except(['show']);
    Route::resource('vendors', AdminVendorController::class);
    Route::post('vendors/{vendor}/verify/approve', [AdminVendorController::class, 'approveVerification'])->name('vendors.verify.approve');
    Route::post('vendors/{vendor}/verify/reject', [AdminVendorController::class, 'rejectVerification'])->name('vendors.verify.reject');
    Route::get('products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::get('urun-onaylari', [AdminProductModerationController::class, 'index'])->name('product-approvals.index');
    Route::post('urun-onaylari/{product}/onayla', [AdminProductModerationController::class, 'approve'])->name('product-approvals.approve');
    Route::post('urun-onaylari/{product}/reddet', [AdminProductModerationController::class, 'reject'])->name('product-approvals.reject');
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::get('finans', [AdminFinanceController::class, 'index'])->name('finance.index');
    Route::post('finans/giderler', [AdminFinanceController::class, 'updateExpenses'])->name('finance.expenses');
    Route::get('finans/export', [AdminFinanceController::class, 'export'])->name('finance.export');
    Route::get('blog', [AdminBlogController::class, 'index'])->name('blog.index');
    Route::get('blog/yeni', [AdminBlogController::class, 'create'])->name('blog.create');
    Route::post('blog', [AdminBlogController::class, 'store'])->name('blog.store');
    Route::get('blog/ice-aktar', [AdminBlogController::class, 'importForm'])->name('blog.import');
    Route::post('blog/ice-aktar', [AdminBlogController::class, 'import'])->name('blog.import.store');
    Route::get('blog/{post}/duzenle', [AdminBlogController::class, 'edit'])->name('blog.edit');
    Route::put('blog/{post}', [AdminBlogController::class, 'update'])->name('blog.update');
    Route::delete('blog/{post}', [AdminBlogController::class, 'destroy'])->name('blog.destroy');
    Route::get('api-yonetimi', [AdminApiManagementController::class, 'index'])->name('api-management.index');
    Route::post('api-yonetimi', [AdminApiManagementController::class, 'update'])->name('api-management.update');
    Route::get('payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
    Route::post('payouts/approve', [AdminPayoutController::class, 'approve'])->name('payouts.approve');
    Route::get('destek-talepleri', [AdminSupportTicketWebController::class, 'index'])->name('support-tickets.index');
    Route::get('destek-talepleri/{supportTicket}', [AdminSupportTicketWebController::class, 'show'])->name('support-tickets.show');
    Route::patch('destek-talepleri/{supportTicket}/durum', [AdminSupportTicketWebController::class, 'updateStatus'])->name('support-tickets.update-status');
    Route::post('destek-talepleri/{supportTicket}/ustlen', [AdminSupportTicketWebController::class, 'assign'])->name('support-tickets.assign');
    Route::post('destek-talepleri/{supportTicket}/yanit', [AdminSupportTicketWebController::class, 'reply'])->name('support-tickets.reply');
    Route::get('satici-odeme-talepleri', [AdminVendorPayoutRequestController::class, 'index'])->name('vendor-payout-requests.index');
    Route::post('satici-odeme-talepleri/{payoutRequest}/onayla', [AdminVendorPayoutRequestController::class, 'approve'])->name('vendor-payout-requests.approve');
    Route::post('satici-odeme-talepleri/{payoutRequest}/reddet', [AdminVendorPayoutRequestController::class, 'reject'])->name('vendor-payout-requests.reject');
    Route::get('musteriler', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('musteriler/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::post('musteriler/{customer}/aktiflik', [AdminCustomerController::class, 'toggleActive'])->name('customers.toggle');
    Route::get('satici-guncellemeleri', [AdminVendorUpdateController::class, 'index'])->name('vendor-updates.index');
    Route::post('satici-guncellemeleri/belgeler/{document}/onayla', [AdminVendorUpdateController::class, 'approveDocument'])->name('vendor-updates.documents.approve');
    Route::post('satici-guncellemeleri/belgeler/{document}/reddet', [AdminVendorUpdateController::class, 'rejectDocument'])->name('vendor-updates.documents.reject');
    Route::post('satici-guncellemeleri/profil/{vendorProfileChangeRequest}/onayla', [AdminVendorUpdateController::class, 'approveProfile'])->name('vendor-updates.profiles.approve');
    Route::post('satici-guncellemeleri/profil/{vendorProfileChangeRequest}/reddet', [AdminVendorUpdateController::class, 'rejectProfile'])->name('vendor-updates.profiles.reject');
    Route::get('satici-kategori-talepleri', [AdminVendorCategoryRequestController::class, 'index'])->name('vendor-category-requests.index');
    Route::post('satici-kategori-talepleri/{vendorCategoryRequest}/onayla', [AdminVendorCategoryRequestController::class, 'approve'])->name('vendor-category-requests.approve');
    Route::post('satici-kategori-talepleri/{vendorCategoryRequest}/reddet', [AdminVendorCategoryRequestController::class, 'reject'])->name('vendor-category-requests.reject');
    Route::resource('contracts', AdminContractController::class)->only(['index', 'create', 'store', 'edit', 'update']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dogrulama', [OtpVerificationController::class, 'show'])->name('otp.show');
    Route::post('/dogrulama/gonder', [OtpVerificationController::class, 'send'])->name('otp.send');
    Route::post('/dogrulama/onayla', [OtpVerificationController::class, 'verify'])->name('otp.verify');
});
