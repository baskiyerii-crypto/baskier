<div class="site-menu-head">
    @include('partials.platform-brand', ['compact' => true, 'brandHref' => route('home')])
    <label for="site-menu-toggle" class="site-menu-close" aria-label="{{ __('ui.close_menu') }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </label>
</div>

<nav class="site-menu-nav">
    <p class="site-menu-kicker">{{ __('ui.menu') }}</p>
    <div class="site-menu-list">
        @foreach(\App\Support\SiteMenu::forPlacement('drawer') as $item)
            @if(($item['type'] ?? '') === 'categories_accordion')
                <details class="site-menu-acc">
                    <summary>{{ \App\Support\SiteMenu::displayLabel($item) }}</summary>
                    <div class="site-menu-acc-body">
                        @if(!empty($headerCategories) && $headerCategories->isNotEmpty())
                            @foreach($headerCategories as $parentCategory)
                                <a href="{{ route('products.index', ['category_id' => $parentCategory->id]) }}">{{ $parentCategory->localizedName() }}</a>
                                @foreach($parentCategory->children as $childCategory)
                                    <a class="is-child" href="{{ route('products.index', ['category_id' => $childCategory->id]) }}">{{ $childCategory->localizedName() }}</a>
                                @endforeach
                            @endforeach
                        @else
                            <p class="site-menu-empty">{{ __('ui.no_categories') }}</p>
                        @endif
                    </div>
                </details>
            @else
                @php
                    $menuHref = \App\Support\SiteMenu::href($item);
                    $children = collect($item['children'] ?? [])->filter(fn ($c) => ($c['is_active'] ?? true));
                @endphp
                @if($children->isNotEmpty())
                    <details class="site-menu-acc">
                        <summary>{{ \App\Support\SiteMenu::displayLabel($item) }}</summary>
                        <div class="site-menu-acc-body">
                            @if($menuHref)
                                <a href="{{ $menuHref }}">{{ \App\Support\SiteMenu::displayLabel($item) }}</a>
                            @endif
                            @foreach($children as $child)
                                @php $childHref = \App\Support\SiteMenu::href($child); @endphp
                                @if($childHref)
                                    <a class="is-child" href="{{ $childHref }}">{{ \App\Support\SiteMenu::displayLabel($child) }}</a>
                                @endif
                            @endforeach
                        </div>
                    </details>
                @elseif($menuHref)
                    <a class="site-menu-link {{ request()->url() === $menuHref ? 'is-active' : '' }}" href="{{ $menuHref }}">
                        <span>{{ \App\Support\SiteMenu::displayLabel($item) }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                @endif
            @endif
        @endforeach
    </div>
</nav>

<nav class="site-menu-nav">
    <p class="site-menu-kicker">{{ __('ui.account') }}</p>
    <div class="site-menu-list">
        @auth
            <a class="site-menu-link" href="{{ route('cart.index') }}"><span>{{ __('ui.cart') }}</span></a>
            <a class="site-menu-link" href="{{ route('account.orders.index') }}"><span>{{ __('ui.orders') }}</span></a>
            @if(auth()->user()->isAdmin())
                <a class="site-menu-link" href="{{ route('admin.dashboard') }}"><span>{{ __('ui.admin') }}</span></a>
            @endif
            @if(auth()->user()->isVendor())
                <a class="site-menu-link" href="{{ route('vendor.dashboard') }}"><span>{{ __('ui.vendor_panel') }}</span></a>
            @endif
            @if(auth()->user()->isCustomer())
                <a class="site-menu-link" href="{{ route('customer.dashboard') }}"><span>{{ __('ui.account') }}</span></a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="site-menu-logout">{{ __('ui.logout') }}</button>
            </form>
        @else
            <a class="site-menu-link" href="{{ route('login') }}"><span>{{ __('ui.login_full') }}</span></a>
            <a class="site-menu-cta" href="{{ route('register') }}">{{ __('ui.register') }}</a>
        @endauth
    </div>
</nav>

<div class="site-menu-foot">
    <p class="site-menu-kicker">{{ __('ui.language') }}</p>
    @include('partials.locale-switcher')
</div>
