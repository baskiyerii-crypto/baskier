@extends('layouts.vendor')
@section('title', 'Sipariş sorusu')
@section('content')
<div class="card p-4" style="max-width:720px;">
    <a href="{{ route('vendor.order-questions.index') }}" class="small">← Liste</a>
    <h1 class="h5 mt-2">#{{ $question->order?->order_number }}</h1>
    <p class="fw-semibold mb-1">{{ $question->subject }}</p>
    @foreach($question->replies as $r)
        <div class="border rounded p-2 mb-2 small {{ $r->is_from_vendor ? 'bg-light' : '' }}">{{ $r->body }}</div>
    @endforeach
    <form method="POST" action="{{ route('vendor.order-questions.reply', $question) }}">
        @csrf
        <textarea name="body" class="form-control" rows="3" required></textarea>
        <button class="btn btn-primary btn-sm mt-2">Yanıtla</button>
    </form>
</div>
@endsection
