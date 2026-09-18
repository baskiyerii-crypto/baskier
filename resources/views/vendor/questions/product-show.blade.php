@extends('layouts.vendor')
@section('title', 'Ürün sorusu')
@section('content')
<div class="card p-4" style="max-width:720px;">
    <a href="{{ route('vendor.product-questions.index') }}" class="small">← Liste</a>
    <h1 class="h5 mt-2">{{ $question->product?->name }}</h1>
    @if($question->product?->displayImageUrl())
        <img src="{{ $question->product->displayImageUrl() }}" alt="" class="rounded mb-2" style="max-width:96px;max-height:96px;object-fit:cover;">
    @endif
    <p class="small text-muted">Müşteri: {{ $question->customer?->publicCode() }}</p>
    <p>{{ $question->question }}</p>
    @if($question->answer)
        <div class="alert alert-success small">{{ $question->answer }}</div>
    @endif
    <form method="POST" action="{{ route('vendor.product-questions.answer', $question) }}">
        @csrf
        <textarea name="answer" class="form-control" rows="4" required>{{ old('answer', $question->answer) }}</textarea>
        <button class="btn btn-primary btn-sm mt-2">Yanıtla</button>
    </form>
</div>
@endsection
