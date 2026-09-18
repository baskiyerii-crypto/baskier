<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\Admin\AdminApiManagementController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminBrandController;
use App\Http\Controllers\Admin\AdminBusinessTypeController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminContractController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminMenuController;
use App\Http\Controllers\Admin\AdminOutdoorController;
use App\Http\Controllers\Admin\AdminPayoutController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminProductModerationController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminSupportTicketWebController;
use App\Http\Controllers\Admin\AdminVendorController;
use App\Http\Controllers\Admin\AdminVendorPayoutRequestController;
use App\Http\Controllers\Admin\AdminVerificationQueueController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\IyzicoCallbackController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\Customer\CustomerAddressController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerMessageController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerOrderDesignController;
use App\Http\Controllers\Customer\CustomerOutdoorPlanController;
use App\Http\Controllers\Customer\CustomerPriceEstimateController;
use App\Http\Controllers\Customer\CustomerQuestionController;
use App\Http\Controllers\Customer\CustomerReviewController;
use App\Http\Controllers\Customer\CustomerSupportTicketController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FreelancerJobController;
use App\Http\Controllers\FreelancerJobWebController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationWebController;
use App\Http\Controllers\OutdoorCatalogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Vendor\VendorBalanceController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorDocumentController;
use App\Http\Controllers\Vendor\VendorFreelancerController;
use App\Http\Controllers\Vendor\VendorMessageController;
use App\Http\Controllers\Vendor\VendorOzalitController;
use App\Http\Controllers\OutdoorPanel\OutdoorDashboardController;
use App\Http\Controllers\OutdoorPanel\OutdoorRepresentationController;
use App\Http\Controllers\Vendor\VendorOutdoorInventoryController;
use App\Http\Controllers\Vendor\VendorOutdoorOpsController;
use App\Http\Controllers\Vendor\VendorOutdoorRequestController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorOrderDesignController;
use App\Http\Controllers\Vendor\VendorPayoutRequestWebController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\Vendor\VendorQuestionController;
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
use App\Http\Controllers\Vendor\VendorTabelaController;

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
Route::get('/acik-hava', [OutdoorCatalogController::class, 'index'])->name('outdoor.index');
Route::get('/acik-hava/{slug}', [OutdoorCatalogController::class, 'show'])->name('outdoor.show');
Route::post('/acik-hava/{slug}/plan', [OutdoorCatalogController::class, 'addToPlan'])->name('outdoor.plan.add');
Route::post('/acik-hava-sepet/kaldir', [OutdoorCatalogController::class, 'removeFromPlan'])->name('outdoor.basket.remove');
Route::get('/gizlilik', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/kullanim-kosullari', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/hakkimizda', [PageController::class, 'about'])->name('pages.about');
Route::get('/iletisim', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/sozlesme/{key}', [ContractController::class, 'show'])->name('contracts.show');

// Hizmet Talepleri (Canonical)
Route::get('/hizmet-talepleri', fn () => redirect()->route('quote-requests.create', ['type' => 'freelancer'], 301))->name('service-requests.index');
Route::get('/hizmet-talebi/{job}', fn () => redirect()->route('quote-requests.create', ['type' => 'freelancer'], 301))->name('service-requests.show');

Route::middleware('auth')->group(function () {
    Route::get('/hizmet-talebi-olustur', fn () => redirect()->route('quote-requests.create', ['type' => 'freelancer'], 301))->name('service-requests.create');
    Route::post('/hizmet-talepleri', fn () => redirect()->route('quote-requests.create', ['type' => 'freelancer'], 301))->name('service-requests.store');
    Route::get('/hesap/hizmet-taleplerim', fn () => redirect()->route('quote-requests.index', [], 301))->name('service-requests.my');
});

// Eski /is-ilanlari GET linkleri için 301 Kalıcı Yönlendirme (SEO koruma)
Route::get('/is-ilanlari', fn () => redirect('/hizmet-talepleri', 301))->name('freelancer-jobs.index');
Route::get('/is-ilanlari/yeni', fn () => redirect('/hizmet-talebi-olustur', 301))->name('freelancer-jobs.create');
Route::get('/is-ilanlari/{job}', fn ($job) => redirect("/hizmet-talebi/{$job}", 301))->name('freelancer-jobs.show');
Route::get('/hesap/is-ilanlarim', fn () => redirect('/hesap/hizmet-taleplerim', 301))->name('freelancer-jobs.my');

// Eski form POST route uyumluluğu
Route::middleware('auth')->group(function () {
    Route::post('/is-ilanlari', [FreelancerJobWebController::class, 'store'])->name('freelancer-jobs.store');
    Route::post('/is-ilanlari/{job}/teklif', [FreelancerJobWebController::class, 'storeBid'])->name('freelancer-jobs.bid');
    Route::post('/is-ilanlari/{job}/teklif/{bid}/sec', [FreelancerJobWebController::class, 'selectBid'])->name('freelancer-jobs.select-bid');
});

// Canlılık ve Hazırlık Denetimi (Readiness Probe)
Route::get('/ready', \App\Http\Controllers\ReadyCheckController::class)->name('ready');

// Auth (misafir)
Route::middleware('guest')->group(function () {
    Route::get('/giris', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/giris', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/kayit', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/kayit', [AuthController::class, 'register'])->middleware('throttle:register');
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

// Ödeme callback ve webhook (harici çağrı)
Route::post('/odeme/iyzico/callback', [IyzicoCallbackController::class, 'callback'])->name('payment.iyzico.callback')->middleware('throttle:webhook');
Route::post('/odeme/iyzico/webhook', [IyzicoCallbackController::class, 'webhook'])->name('payment.iyzico.webhook')->middleware('throttle:webhook');

Route::middleware('auth')->group(function () {
    Route::get('/sepet', [CartController::class, 'index'])->name('cart.index');
    Route::post('/sepet/urun/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::put('/sepet/kalem/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/sepet/kalem/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/odeme', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/odeme', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:checkout');
    Route::post('/odeme/hizli-al/{product}', [CheckoutController::class, 'quickBuy'])->name('checkout.quick-buy')->middleware('throttle:checkout');
    Route::get('/odeme/hizli-al-iptal', [CheckoutController::class, 'cancelQuickBuy'])->name('checkout.cancel-quick-buy');
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

    Route::get('/bildirimler', [NotificationWebController::class, 'index'])->name('notifications.index');
    Route::get('/bildirimler/{id}', [NotificationWebController::class, 'open'])->name('notifications.open');
    Route::post('/bildirimler/okundu', [NotificationWebController::class, 'markAll'])->name('notifications.read-all');
    Route::post('/urunler/{product}/soru', [CustomerQuestionController::class, 'storeProduct'])->name('products.questions.store');
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
    Route::get('/mesajlar', [CustomerMessageController::class, 'index'])->name('messages.index');
    Route::get('/mesajlar/{conversation}', [CustomerMessageController::class, 'show'])->name('messages.show');
    Route::post('/mesajlar/{conversation}', [CustomerMessageController::class, 'store'])->name('messages.store');
    Route::get('/urun-sorularim', [CustomerQuestionController::class, 'products'])->name('product-questions.index');
    Route::get('/siparis-sorularim', [CustomerQuestionController::class, 'orders'])->name('order-questions.index');
    Route::post('/siparis-sorularim/{question}', [CustomerQuestionController::class, 'replyOrder'])->name('order-questions.reply');
    Route::post('/siparisler/{order}/soru', [CustomerQuestionController::class, 'storeOrder'])->name('orders.questions.store');
    Route::get('/planlarim', [CustomerOutdoorPlanController::class, 'index'])->name('outdoor.plans.index');
    Route::post('/planlarim', [CustomerOutdoorPlanController::class, 'store'])->name('outdoor.plans.store');
    Route::get('/planlarim/{plan}', [CustomerOutdoorPlanController::class, 'show'])->name('outdoor.plans.show');
    Route::post('/planlarim/{plan}/satir/{vendorRequest}/teklif/{quote}/sec', [CustomerOutdoorPlanController::class, 'accept'])->name('outdoor.plans.accept');
});

// Satıcı paneli
Route::middleware(['auth', 'role:vendor', 'vendor.not_suspended'])->prefix('satici-panel')->name('vendor.')->group(function () {
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
    Route::post('siparisler/{order}/odeme-onayla', [VendorOrderController::class, 'confirmPayment'])->name('orders.confirm-payment');
    Route::get('odeme-talepleri', [VendorPayoutRequestWebController::class, 'index'])->name('payout-requests.index');
    Route::post('odeme-talepleri', [VendorPayoutRequestWebController::class, 'store'])->name('payout-requests.store');
    Route::get('bakiye', [VendorBalanceController::class, 'index'])->name('balance.index');
    Route::post('bakiye', [VendorBalanceController::class, 'topUp'])->name('balance.topup');
    Route::get('mesajlar', [VendorMessageController::class, 'index'])->name('messages.index');
    Route::get('mesajlar/{conversation}', [VendorMessageController::class, 'show'])->name('messages.show');
    Route::post('mesajlar/{conversation}', [VendorMessageController::class, 'store'])->name('messages.store');
    Route::get('belgeler', [VendorDocumentController::class, 'index'])->name('documents.index');
    Route::post('belgeler', [VendorDocumentController::class, 'store'])->name('documents.store')->middleware('throttle:document_upload');
    Route::get('freelancerim', [VendorFreelancerController::class, 'index'])->name('freelancer.index');
    Route::get('tabela', [VendorTabelaController::class, 'index'])->name('tabela.index');
    Route::post('tabela/gorusme', [VendorTabelaController::class, 'startMeeting'])->name('tabela.meeting');
    Route::get('ozalit', [VendorOzalitController::class, 'index'])->name('ozalit.index');
    Route::get('urun-sorulari', [VendorQuestionController::class, 'products'])->name('product-questions.index');
    Route::get('urun-sorulari/{question}', [VendorQuestionController::class, 'showProduct'])->name('product-questions.show');
    Route::post('urun-sorulari/{question}', [VendorQuestionController::class, 'answerProduct'])->name('product-questions.answer');
    Route::get('siparis-sorulari', [VendorQuestionController::class, 'orders'])->name('order-questions.index');
    Route::get('siparis-sorulari/{question}', [VendorQuestionController::class, 'showOrder'])->name('order-questions.show');
    Route::post('siparis-sorulari/{question}', [VendorQuestionController::class, 'replyOrder'])->name('order-questions.reply');
    Route::post('siparisler/{order}/soru', [VendorQuestionController::class, 'storeOrder'])->name('orders.questions.store');
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

Route::permanentRedirect('/satici-panel/outdoor', '/acik-hava-panel/envanter');
Route::permanentRedirect('/satici-panel/outdoor/yeni', '/acik-hava-panel/envanter/yeni');
Route::get('/satici-panel/outdoor/{inventory}/duzenle', fn (string $inventory) => redirect('/acik-hava-panel/envanter/'.$inventory.'/duzenle', 301));
Route::permanentRedirect('/satici-panel/outdoor-havuz', '/acik-hava-panel/panolar');
Route::permanentRedirect('/satici-panel/outdoor-talepler', '/acik-hava-panel/talepler');
Route::get('/satici-panel/outdoor-talepler/{vendorRequest}', fn (string $vendorRequest) => redirect('/acik-hava-panel/talepler/'.$vendorRequest, 301));
Route::permanentRedirect('/satici-panel/outdoor-planlar', '/acik-hava-panel/planlar');
Route::get('/satici-panel/outdoor-planlar/{plan}', fn (string $plan) => redirect('/acik-hava-panel/planlar/'.$plan, 301));
Route::permanentRedirect('/satici-panel/outdoor-ekip', '/acik-hava-panel/ekip');
Route::permanentRedirect('/satici-panel/outdoor-isler', '/acik-hava-panel/isler');
Route::permanentRedirect('/satici-panel/outdoor-raporlar', '/acik-hava-panel/raporlar');

Route::middleware(['auth', 'role:vendor', 'vendor.not_suspended'])->prefix('acik-hava-panel')->name('outdoor-panel.')->group(function () {
    Route::get('/', [OutdoorDashboardController::class, 'index'])->name('dashboard');
    Route::get('envanter', [VendorOutdoorInventoryController::class, 'index'])->name('inventories.index');
    Route::get('envanter/yeni', [VendorOutdoorInventoryController::class, 'create'])->name('inventories.create');
    Route::post('envanter', [VendorOutdoorInventoryController::class, 'store'])->name('inventories.store');
    Route::get('envanter/{inventory}/duzenle', [VendorOutdoorInventoryController::class, 'edit'])->name('inventories.edit');
    Route::put('envanter/{inventory}', [VendorOutdoorInventoryController::class, 'update'])->name('inventories.update');
    Route::post('envanter/{inventory}/inceleme', [VendorOutdoorInventoryController::class, 'submit'])->name('inventories.submit');
    Route::post('envanter/{inventory}/bloke', [VendorOutdoorInventoryController::class, 'block'])->name('inventories.block');
    Route::get('panolar', [VendorOutdoorInventoryController::class, 'pool'])->name('pool');
    Route::get('talepler', [VendorOutdoorRequestController::class, 'index'])->name('requests.index');
    Route::get('talepler/{vendorRequest}', [VendorOutdoorRequestController::class, 'show'])->name('requests.show');
    Route::post('talepler/{vendorRequest}/teklif', [VendorOutdoorRequestController::class, 'quote'])->name('requests.quote');
    Route::post('talepler/{vendorRequest}/red', [VendorOutdoorRequestController::class, 'decline'])->name('requests.decline');
    Route::get('planlar', [VendorOutdoorRequestController::class, 'myPlans'])->name('plans.index');
    Route::post('planlar', [VendorOutdoorRequestController::class, 'storePlan'])->name('plans.store');
    Route::get('planlar/{plan}', [VendorOutdoorRequestController::class, 'showPlan'])->name('plans.show');
    Route::get('ekip', [VendorOutdoorOpsController::class, 'staffIndex'])->name('staff');
    Route::post('ekip', [VendorOutdoorOpsController::class, 'staffInvite'])->name('staff.invite');
    Route::get('isler', [VendorOutdoorOpsController::class, 'jobs'])->name('jobs');
    Route::post('isler/{occupancy}/ata', [VendorOutdoorOpsController::class, 'assign'])->name('jobs.assign');
    Route::post('isler/{occupancy}/kanit', [VendorOutdoorOpsController::class, 'proof'])->name('jobs.proof');
    Route::get('raporlar', [VendorOutdoorOpsController::class, 'claims'])->name('claims');
    Route::post('raporlar', [VendorOutdoorOpsController::class, 'storeClaim'])->name('claims.store');
    Route::get('temsil', [OutdoorRepresentationController::class, 'index'])->name('representations.index');
    Route::post('temsil', [OutdoorRepresentationController::class, 'invite'])->name('representations.invite');
    Route::post('temsil/{representation}/onayla', [OutdoorRepresentationController::class, 'accept'])->name('representations.accept');
    Route::post('temsil/{representation}/iptal', [OutdoorRepresentationController::class, 'revoke'])->name('representations.revoke');
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
    Route::get('menu', [AdminMenuController::class, 'index'])->name('menu.index');
    Route::post('menu', [AdminMenuController::class, 'update'])->name('menu.update');
    Route::post('menu/reset', [AdminMenuController::class, 'reset'])->name('menu.reset');
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
    Route::post('api-yonetimi/test-iyzico', [AdminApiManagementController::class, 'testIyzico'])->name('api-management.test-iyzico');
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
    Route::get('dogrulamalar', [AdminVerificationQueueController::class, 'index'])->name('verifications.index');
    Route::post('dogrulamalar/{document}/onayla', [AdminVerificationQueueController::class, 'approve'])->name('verifications.approve');
    Route::post('dogrulamalar/{document}/reddet', [AdminVerificationQueueController::class, 'reject'])->name('verifications.reject');
    Route::post('dogrulamalar/satici/{vendor}/askiya-al', [AdminVerificationQueueController::class, 'suspendVendor'])->name('verifications.suspend');
    Route::post('dogrulamalar/satici/{vendor}/askiyi-kaldir', [AdminVerificationQueueController::class, 'unsuspendVendor'])->name('verifications.unsuspend');
    Route::get('satici-kategori-talepleri', [AdminVendorCategoryRequestController::class, 'index'])->name('vendor-category-requests.index');
    Route::post('satici-kategori-talepleri/{vendorCategoryRequest}/onayla', [AdminVendorCategoryRequestController::class, 'approve'])->name('vendor-category-requests.approve');
    Route::post('satici-kategori-talepleri/{vendorCategoryRequest}/reddet', [AdminVendorCategoryRequestController::class, 'reject'])->name('vendor-category-requests.reject');
    Route::resource('contracts', AdminContractController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::post('contracts/sablonlari-olustur', [AdminContractController::class, 'generateTemplates'])->name('contracts.generate');
    Route::get('alt-markalar', [AdminBrandController::class, 'index'])->name('brands.index');
    Route::post('alt-markalar', [AdminBrandController::class, 'store'])->name('brands.store');
    Route::put('alt-markalar/{brand}', [AdminBrandController::class, 'update'])->name('brands.update');
    Route::delete('alt-markalar/{brand}', [AdminBrandController::class, 'destroy'])->name('brands.destroy');

    Route::get('outdoor/envanter', [AdminOutdoorController::class, 'inventories'])->name('outdoor.inventories');
    Route::post('outdoor/envanter/{inventory}/yayinla', [AdminOutdoorController::class, 'publish'])->name('outdoor.inventories.publish');
    Route::post('outdoor/envanter/{inventory}/reddet', [AdminOutdoorController::class, 'reject'])->name('outdoor.inventories.reject');
    Route::get('outdoor/raporlar', [AdminOutdoorController::class, 'claims'])->name('outdoor.claims');
    Route::post('outdoor/raporlar/{claim}/karar', [AdminOutdoorController::class, 'resolveClaim'])->name('outdoor.claims.resolve');
    Route::get('outdoor/planlar', [AdminOutdoorController::class, 'plans'])->name('outdoor.plans');

    // Platform Metrikleri ve Gözlemlenebilirlik
    Route::get('metrikler', [\App\Http\Controllers\Admin\AdminMetricsController::class, 'index'])->name('metrics.index');
});

Route::middleware('auth')->group(function () {
    Route::get('satici-panel/belgeler/{document}/indir', [VendorDocumentController::class, 'download'])->name('vendor.documents.download');
    Route::get('/dogrulama', [OtpVerificationController::class, 'show'])->name('otp.show');
    Route::post('/dogrulama/gonder', [OtpVerificationController::class, 'send'])->name('otp.send');
    Route::post('/dogrulama/onayla', [OtpVerificationController::class, 'verify'])->name('otp.verify');
});
