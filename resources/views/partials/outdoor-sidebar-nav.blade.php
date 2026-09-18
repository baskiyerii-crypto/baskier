@php
    $v = $v ?? auth()->user()?->vendor;
    $isAgency = (bool) $v?->isOutdoorAgency();
    $isOwner = ! $isAgency;
    $oohStaff = null;
    $nav = $navBadges ?? [];
    try {
        if ($v && \Illuminate\Support\Facades\Schema::hasTable('vendor_members')) {
            $oohStaff = app(\App\Services\OutdoorStaffService::class)->roleFor(auth()->user(), $v);
        }
    } catch (\Throwable) {
        $oohStaff = null;
    }
    $isField = $oohStaff === 'field';
@endphp
<a href="{{ route('outdoor-panel.dashboard') }}" class="nav-link {{ request()->routeIs('outdoor-panel.dashboard') ? 'active' : '' }}"><span>Özet</span></a>

@if($isOwner && ! $isField)
    <a href="{{ route('outdoor-panel.inventories.index') }}" class="nav-link {{ request()->routeIs('outdoor-panel.inventories.index','outdoor-panel.inventories.edit') ? 'active' : '' }}"><span>Envanter</span></a>
    <a href="{{ route('outdoor-panel.inventories.create') }}" class="nav-link {{ request()->routeIs('outdoor-panel.inventories.create') ? 'active' : '' }}"><span>Yeni pano</span></a>
@endif

@if($isAgency)
    <a href="{{ route('outdoor-panel.pool') }}" class="nav-link {{ request()->routeIs('outdoor-panel.pool') ? 'active' : '' }}"><span>Temsil ettiğim panolar</span></a>
    @if(Route::has('outdoor-panel.directory'))
        <a href="{{ route('outdoor-panel.directory') }}" class="nav-link {{ request()->routeIs('outdoor-panel.directory') ? 'active' : '' }}"><span>Mecra sahipleri</span></a>
    @endif
@elseif(! $isField && Route::has('outdoor-panel.directory'))
    <a href="{{ route('outdoor-panel.directory') }}" class="nav-link {{ request()->routeIs('outdoor-panel.directory') ? 'active' : '' }}"><span>Ajanslar</span></a>
@endif

@if(! $isField)
    <a href="{{ route('outdoor-panel.requests.index') }}" class="nav-link {{ request()->routeIs('outdoor-panel.requests.*') ? 'active' : '' }}"><span>Talepler @include('partials.nav-count-badge', ['count' => $nav['outdoor_requests'] ?? 0])</span></a>
    <a href="{{ route('outdoor-panel.representations.index') }}" class="nav-link {{ request()->routeIs('outdoor-panel.representations.*') ? 'active' : '' }}"><span>{{ $isAgency ? 'Bağlı sahipler' : 'Ajanslarım' }} @include('partials.nav-count-badge', ['count' => $nav['outdoor_invites'] ?? 0])</span></a>
@endif

@if($isOwner)
    <a href="{{ route('outdoor-panel.jobs') }}" class="nav-link {{ request()->routeIs('outdoor-panel.jobs*') ? 'active' : '' }}"><span>Asım işleri @include('partials.nav-count-badge', ['count' => $nav['outdoor_jobs'] ?? 0])</span></a>
    @if($oohStaff === 'owner' || $oohStaff === null)
        <a href="{{ route('outdoor-panel.staff') }}" class="nav-link {{ request()->routeIs('outdoor-panel.staff*') ? 'active' : '' }}"><span>Ekip</span></a>
        <a href="{{ route('outdoor-panel.claims') }}" class="nav-link {{ request()->routeIs('outdoor-panel.claims*') ? 'active' : '' }}"><span>Çift ilan raporları @include('partials.nav-count-badge', ['count' => $nav['outdoor_claims'] ?? 0])</span></a>
    @endif
@endif

<a href="{{ route('outdoor-panel.documents.index') }}" class="nav-link {{ request()->routeIs('outdoor-panel.documents.*') ? 'active' : '' }}"><span>Belgeler @include('partials.nav-count-badge', ['count' => $nav['outdoor_documents'] ?? 0])</span></a>
<a href="{{ route('outdoor-panel.subscriptions.index') }}" class="nav-link {{ request()->routeIs('outdoor-panel.subscriptions.*') ? 'active' : '' }}"><span>Modüller @include('partials.nav-count-badge', ['count' => $nav['outdoor_modules'] ?? 0])</span></a>
@if(Route::has('outdoor-panel.payout-requests.index'))
    <a href="{{ route('outdoor-panel.payout-requests.index') }}" class="nav-link {{ request()->routeIs('outdoor-panel.payout-requests.*') ? 'active' : '' }}"><span>Hakediş</span></a>
@endif
<a href="{{ route('outdoor-panel.balance.index') }}" class="nav-link {{ request()->routeIs('outdoor-panel.balance.*') ? 'active' : '' }}"><span>Bakiye</span></a>
