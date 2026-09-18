@php
    $v = $v ?? auth()->user()?->vendor;
    $oohRole = $oohRole ?? null;
    $isField = $oohRole === 'field';
    $tracksEmpty = ! $v || empty($v->registration_tracks);
    $showProducts = ! $isField && ($tracksEmpty || $v->hasTrack('physical_products'));
    $showQuotes = ! $isField && $v?->hasActiveQuotesModule();
    $showFreelancer = ! $isField && $v?->hasActiveFreelancerModule() && Route::has('vendor.freelancer.index');
    $showTabela = ! $isField && $v?->hasActiveTabelaModule() && Route::has('vendor.tabela.index');
    $showOzalit = ! $isField && $v?->hasActiveOzalitModule() && Route::has('vendor.ozalit.index');
    $showOutdoor = (bool) $v?->hasActiveOutdoorModule() && Route::has('vendor.outdoor.inventories.index');
@endphp
<a href="{{ route('vendor.dashboard') }}" class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
    <span>Özet</span>
</a>

@if(! $isField)
<details class="nav-acc" @if(request()->routeIs('vendor.orders.*','vendor.order-questions.*','vendor.direct-quotes.*')) open @endif>
    <summary>{{ __('panel.nav_work') }} <span>▾</span></summary>
    <div class="nav-acc-body">
        <a href="{{ route('vendor.orders.index') }}" class="nav-link {{ request()->routeIs('vendor.orders.*') ? 'active' : '' }}"><span>Siparişler</span></a>
        <a href="{{ route('vendor.direct-quotes.index') }}" class="nav-link {{ request()->routeIs('vendor.direct-quotes.*') ? 'active' : '' }}"><span>{{ __('panel.nav_direct_quotes') }}</span></a>
        <a href="{{ route('vendor.order-questions.index') }}" class="nav-link {{ request()->routeIs('vendor.order-questions.*') ? 'active' : '' }}"><span>Sipariş soruları</span></a>
    </div>
</details>
@endif

@if($showProducts)
<details class="nav-acc" @if(request()->routeIs('vendor.products.*','vendor.product-questions.*')) open @endif>
    <summary>{{ __('panel.nav_module_products') }} <span>▾</span></summary>
    <div class="nav-acc-body">
        <a href="{{ route('vendor.products.index') }}" class="nav-link {{ request()->routeIs('vendor.products.index','vendor.products.edit') ? 'active' : '' }}"><span>Ürünlerim</span></a>
        <a href="{{ route('vendor.products.create') }}" class="nav-link {{ request()->routeIs('vendor.products.create') ? 'active' : '' }}"><span>Yeni ürün</span></a>
        <a href="{{ route('vendor.product-questions.index') }}" class="nav-link {{ request()->routeIs('vendor.product-questions.*') ? 'active' : '' }}"><span>Ürün soruları</span></a>
    </div>
</details>
@endif

@if($showQuotes)
<details class="nav-acc" @if(request()->routeIs('vendor.quote-requests.*')) open @endif>
    <summary>{{ __('panel.nav_module_quotes') }} <span>▾</span></summary>
    <div class="nav-acc-body">
        <a href="{{ route('vendor.quote-requests.index') }}" class="nav-link {{ request()->routeIs('vendor.quote-requests.*') ? 'active' : '' }}"><span>{{ __('panel.nav_bulk_production') }}</span></a>
    </div>
</details>
@endif

@if($showFreelancer)
<details class="nav-acc" @if(request()->routeIs('vendor.freelancer.*')) open @endif>
    <summary>{{ __('panel.nav_module_freelancer') }} <span>▾</span></summary>
    <div class="nav-acc-body">
        <a href="{{ route('vendor.freelancer.index') }}" class="nav-link {{ request()->routeIs('vendor.freelancer.*') ? 'active' : '' }}"><span>{{ __('panel.nav_freelancer_requests') }}</span></a>
    </div>
</details>
@endif

@if($showTabela)
<details class="nav-acc" @if(request()->routeIs('vendor.tabela.*')) open @endif>
    <summary>{{ __('panel.nav_module_tabela') }} <span>▾</span></summary>
    <div class="nav-acc-body">
        <a href="{{ route('vendor.tabela.index') }}" class="nav-link {{ request()->routeIs('vendor.tabela.*') ? 'active' : '' }}"><span>{{ __('panel.nav_tabela_meetings') }}</span></a>
    </div>
</details>
@endif

@if($showOzalit)
<details class="nav-acc" @if(request()->routeIs('vendor.ozalit.*')) open @endif>
    <summary>{{ __('panel.nav_module_ozalit') }} <span>▾</span></summary>
    <div class="nav-acc-body">
        <a href="{{ route('vendor.ozalit.index') }}" class="nav-link {{ request()->routeIs('vendor.ozalit.*') ? 'active' : '' }}"><span>{{ __('panel.nav_ozalit_requests') }}</span></a>
    </div>
</details>
@endif

@if($showOutdoor)
<details class="nav-acc" @if(request()->routeIs('vendor.outdoor.*')) open @endif>
    <summary>{{ __('panel.nav_module_outdoor') }} <span>▾</span></summary>
    <div class="nav-acc-body">
        @if(! $isField)
            <a href="{{ route('vendor.outdoor.inventories.index') }}" class="nav-link {{ request()->routeIs('vendor.outdoor.inventories.index','vendor.outdoor.inventories.edit') ? 'active' : '' }}"><span>Envanter</span></a>
            <a href="{{ route('vendor.outdoor.inventories.create') }}" class="nav-link {{ request()->routeIs('vendor.outdoor.inventories.create') ? 'active' : '' }}"><span>Yeni pano</span></a>
            <a href="{{ route('vendor.outdoor.requests.index') }}" class="nav-link {{ request()->routeIs('vendor.outdoor.requests.*') ? 'active' : '' }}"><span>Talepler</span></a>
            <a href="{{ route('vendor.outdoor.pool') }}" class="nav-link {{ request()->routeIs('vendor.outdoor.pool') ? 'active' : '' }}"><span>Envanter havuzu</span></a>
            <a href="{{ route('vendor.outdoor.plans.index') }}" class="nav-link {{ request()->routeIs('vendor.outdoor.plans.*') ? 'active' : '' }}"><span>Havuz planlarım</span></a>
        @endif
        <a href="{{ route('vendor.outdoor.jobs') }}" class="nav-link {{ request()->routeIs('vendor.outdoor.jobs*') ? 'active' : '' }}"><span>Asım işleri</span></a>
        @if($oohRole === 'owner')
            <a href="{{ route('vendor.outdoor.staff') }}" class="nav-link {{ request()->routeIs('vendor.outdoor.staff*') ? 'active' : '' }}"><span>Ekip</span></a>
            <a href="{{ route('vendor.outdoor.claims') }}" class="nav-link {{ request()->routeIs('vendor.outdoor.claims*') ? 'active' : '' }}"><span>Çift ilan raporları</span></a>
        @endif
    </div>
</details>
@endif

<details class="nav-acc" @if(request()->routeIs('vendor.documents.*','vendor.profile.*','vendor.categories.*','vendor.contracts.*','vendor.subscriptions.*')) open @endif>
    <summary>{{ __('panel.nav_account_vendor') }} <span>▾</span></summary>
    <div class="nav-acc-body">
        <a href="{{ route('vendor.profile.edit') }}" class="nav-link {{ request()->routeIs('vendor.profile.*') ? 'active' : '' }}"><span>{{ __('panel.profile') }}</span></a>
        <a href="{{ route('vendor.documents.index') }}" class="nav-link {{ request()->routeIs('vendor.documents.*') ? 'active' : '' }}"><span>Belgeler ve Doğrulama</span></a>
        @if(Route::has('vendor.categories.index'))
            <a href="{{ route('vendor.categories.index') }}" class="nav-link {{ request()->routeIs('vendor.categories.*') ? 'active' : '' }}"><span>{{ __('panel.nav_my_categories') }}</span></a>
        @endif
        @if(Route::has('vendor.contracts.index'))
            <a href="{{ route('vendor.contracts.index') }}" class="nav-link {{ request()->routeIs('vendor.contracts.*') ? 'active' : '' }}"><span>{{ __('panel.nav_contracts_vendor') }}</span></a>
        @endif
        <a href="{{ route('vendor.subscriptions.index') }}" class="nav-link {{ request()->routeIs('vendor.subscriptions.*') ? 'active' : '' }}"><span>Modüller</span></a>
    </div>
</details>

<details class="nav-acc" @if(request()->routeIs('vendor.payout-requests.*','vendor.balance.*','vendor.messages.*')) open @endif>
    <summary>{{ __('panel.nav_finance_vendor') }} <span>▾</span></summary>
    <div class="nav-acc-body">
        <a href="{{ route('vendor.payout-requests.index') }}" class="nav-link {{ request()->routeIs('vendor.payout-requests.*') ? 'active' : '' }}"><span>Hakediş</span></a>
        <a href="{{ route('vendor.balance.index') }}" class="nav-link {{ request()->routeIs('vendor.balance.*') ? 'active' : '' }}"><span>Bakiye</span></a>
        <a href="{{ route('vendor.messages.index') }}" class="nav-link {{ request()->routeIs('vendor.messages.*') ? 'active' : '' }}"><span>Mesajlar</span></a>
    </div>
</details>

@if($v)
    <a href="{{ route('vendors.show', $v->slug) }}" target="_blank" rel="noopener" class="nav-link text-muted"><span>Vitrin ↗</span></a>
@endif
