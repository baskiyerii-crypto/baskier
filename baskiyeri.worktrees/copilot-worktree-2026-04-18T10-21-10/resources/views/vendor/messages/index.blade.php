@extends('layouts.vendor')

@section('title', 'Mesajlar')

@section('content')
<div class="card p-4">
    @if($conversations->isEmpty())
        <p class="text-muted small mb-0">Henüz mesajlaşma yok.</p>
    @else
        <ul class="list-group list-group-flush">
            @foreach($conversations as $c)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('vendor.messages.show', $c) }}" class="text-decoration-none text-dark">
                        {{ $c->user?->name }} — {{ $c->messages_count ?? 0 }} mesaj
                    </a>
                    <span class="small text-muted">{{ $c->last_message_at?->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-3">{{ $conversations->links() }}</div>
    @endif
</div>
@endsection
