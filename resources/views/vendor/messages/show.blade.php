@extends('layouts.vendor')

@section('title', 'Mesajlar - ' . $conversation->user?->name)

@section('content')
<div class="card p-4 mb-3">
    <p class="small text-muted mb-2">Konuşma: {{ $conversation->user?->name }} ({{ $conversation->user?->email }})</p>
    <div class="border rounded p-3 bg-light" style="max-height:400px; overflow-y:auto;">
        @foreach($conversation->messages as $m)
            <div class="mb-2 {{ $m->is_from_vendor ? 'text-end' : '' }}">
                <span class="d-inline-block p-2 rounded {{ $m->is_from_vendor ? 'bg-success text-white' : 'bg-white border' }}">
                    @if($m->blocked)
                        <em class="text-muted">[Yönlendirme ihlali - mesaj gösterilmiyor]</em>
                    @else
                        {{ $m->body }}
                    @endif
                </span>
                <br><small class="text-muted">{{ $m->created_at->format('d.m.Y H:i') }}</small>
            </div>
        @endforeach
    </div>
</div>
<form method="POST" action="{{ route('vendor.messages.store', $conversation) }}">
    @csrf
    <div class="mb-2">
        <textarea name="body" class="form-control" rows="2" placeholder="Mesajınız (link veya telefon yazmayın)" required></textarea>
        <div class="form-text">Platform dışına yönlendirme (link, WhatsApp, telefon vb.) yasaktır.</div>
        @error('body')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="btn btn-success btn-sm">Gönder</button>
</form>
<a href="{{ route('vendor.messages.index') }}" class="btn btn-outline-secondary btn-sm mt-3">← Mesajlara dön</a>
@endsection
