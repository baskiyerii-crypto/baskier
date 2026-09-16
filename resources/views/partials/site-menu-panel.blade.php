<div class="p-4 border-b border-slate-200/70 flex items-center justify-between gap-2">
    @include('partials.platform-brand', ['compact' => true, 'brandHref' => route('home')])
    <label for="site-menu-toggle" class="by-btn-secondary px-3 py-2 cursor-pointer" aria-label="{{ __('ui.close_menu') }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </label>
</div>

<div class="px-4 pb-4 pt-2">
    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('ui.menu') }}</p>
    <div class="mt-2 grid gap-2">
        @foreach(\App\Support\SiteMenu::forPlacement('drawer') as $item)
            @if(($item['type'] ?? '') === 'categories_accordion')
                <details class="by-card overflow-hidden">
                    <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-slate-900">{{ \App\Support\SiteMenu::displayLabel($item) }}</summary>
                    <div class="border-t border-slate-100 px-2 py-2 max-h-[40vh] overflow-auto">
                        @if(!empty($headerCategories) && $headerCategories->isNotEmpty())
                            @foreach($headerCategories as $parentCategory)
                                <a class="block rounded-xl px-3 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50" href="{{ route('products.index', ['category_id' => $parentCategory->id]) }}">{{ $parentCategory->localizedName() }}</a>
                                @foreach($parentCategory->children as $childCategory)
                                    <a class="ml-3 block rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-slate-50" href="{{ route('products.index', ['category_id' => $childCategory->id]) }}">{{ $childCategory->localizedName() }}</a>
                                @endforeach
                            @endforeach
                        @else
                            <p class="px-2 py-2 text-sm text-slate-500">{{ __('ui.no_categories') }}</p>
                        @endif
                    </div>
                </details>
            @else
                @php $menuHref = \App\Support\SiteMenu::href($item); @endphp
                @if($menuHref)
                    <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ $menuHref }}">{{ \App\Support\SiteMenu::displayLabel($item) }}</a>
                @endif
            @endif
        @endforeach
    </div>
</div>

<div class="px-4 pb-4">
    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('ui.account') }}</p>
    <div class="mt-2 grid gap-2">
        @auth
            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('cart.index') }}">{{ __('ui.cart') }}</a>
            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('account.orders.index') }}">{{ __('ui.orders') }}</a>
            @if(auth()->user()->isAdmin())
                <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('admin.dashboard') }}">{{ __('ui.admin') }}</a>
            @endif
            @if(auth()->user()->isVendor())
                <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('vendor.dashboard') }}">{{ __('ui.vendor_panel') }}</a>
            @endif
            @if(auth()->user()->isCustomer())
                <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('customer.dashboard') }}">{{ __('ui.account') }}</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full by-btn-secondary">{{ __('ui.logout') }}</button>
            </form>
        @else
            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('login') }}">{{ __('ui.login_full') }}</a>
            <a class="by-card by-card-hover px-4 py-3 text-sm font-semibold text-slate-900" href="{{ route('register') }}">{{ __('ui.register') }}</a>
        @endauth
    </div>
</div>

<div class="px-4 pb-6">
    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ __('ui.language') }}</p>
    <div class="mt-2">
        @include('partials.locale-switcher')
    </div>
</div>
