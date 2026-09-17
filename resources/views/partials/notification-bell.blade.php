@php
    $unread = $unreadNotificationsCount ?? 0;
    $recent = $recentNotifications ?? collect();
    $isBootstrap = ($variant ?? 'bootstrap') === 'bootstrap';
@endphp
@if($isBootstrap)
<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown" aria-label="Bildirimler">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        @if($unread > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end" style="min-width:280px;">
        <div class="px-3 py-2 d-flex justify-content-between align-items-center">
            <strong class="small">Bildirimler</strong>
            @if($unread > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-link btn-sm p-0">Tümünü oku</button></form>
            @endif
        </div>
        @forelse($recent as $n)
            <a class="dropdown-item small {{ $n->read_at ? '' : 'fw-semibold' }}" href="{{ route('notifications.open', $n->id) }}">
                {{ $n->data['title'] ?? 'Bildirim' }}
                <div class="text-muted" style="font-size:.75rem;">{{ \Illuminate\Support\Str::limit($n->data['body'] ?? '', 80) }}</div>
            </a>
        @empty
            <div class="px-3 py-2 text-muted small">Bildirim yok</div>
        @endforelse
        <div class="dropdown-divider"></div>
        <a class="dropdown-item small" href="{{ route('notifications.index') }}">Tümünü gör</a>
    </div>
</div>
@else
<div class="relative" x-data="{open:false}">
    <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-2 text-slate-700">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        @if($unread > 0)
            <span class="absolute -top-1 -right-1 min-w-4 rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </a>
</div>
@endif
