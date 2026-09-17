@extends($layout)

@section('title', 'Bildirimler')

@section('content')
<div class="{{ str_contains($layout, 'account') ? 'by-card p-5' : 'card p-4' }}">
    <div class="d-flex justify-content-between align-items-center mb-3" style="{{ str_contains($layout, 'account') ? 'display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;' : '' }}">
        <h1 class="h5 mb-0">Bildirimler</h1>
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf
            <button class="btn btn-sm btn-outline-secondary">Tümünü okundu işaretle</button>
        </form>
    </div>
    <div class="list-group list-group-flush">
        @forelse($notifications as $n)
            <a href="{{ route('notifications.open', $n->id) }}" class="list-group-item list-group-item-action {{ $n->read_at ? '' : 'fw-semibold' }}">
                <div>{{ $n->data['title'] ?? 'Bildirim' }}</div>
                <div class="small text-muted">{{ $n->data['body'] ?? '' }} · {{ $n->created_at?->format('d.m.Y H:i') }}</div>
            </a>
        @empty
            <p class="text-muted small mb-0">Bildirim bulunmuyor.</p>
        @endforelse
    </div>
    <div class="mt-3">{{ $notifications->links() }}</div>
</div>
@endsection
