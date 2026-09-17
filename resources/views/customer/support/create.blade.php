@extends('layouts.account')

@section('title', 'Yeni Destek Talebi - BaskıYeri')

@section('content')
    <div class="mb-5">
        <a href="{{ route('account.support.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Taleplerime Dön
        </a>
    </div>

    <div class="max-w-xl">
        <h1 class="font-heading text-2xl font-bold tracking-tight text-ink mb-1">Yeni Destek Talebi</h1>
        <p class="text-xs text-muted mb-6">Sipariş, ödeme veya diğer konulardaki sorularınızı ekibimize iletin.</p>

        <div class="by-card p-6 bg-surface border border-border">
            <form method="post" action="{{ route('account.support.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Konu <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="form-control text-xs @error('subject') border-red-500 @enderror" required maxlength="255" placeholder="Kısa ve açıklayıcı konu başlığı">
                    @error('subject')<div class="text-red-500 text-[11px] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Mesajınız <span class="text-red-500">*</span></label>
                    <textarea name="body" rows="6" class="form-control text-xs @error('body') border-red-500 @enderror" required maxlength="5000" placeholder="Sorununuzu veya talebinizi detaylı olarak açıklayın...">{{ old('body') }}</textarea>
                    @error('body')<div class="text-red-500 text-[11px] mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="pt-2 flex items-center justify-between">
                    <button type="submit" class="btn btn-cta text-xs py-2 px-6">Talebi Gönder</button>
                    <a href="{{ route('account.support.index') }}" class="btn btn-secondary text-xs">Vazgeç</a>
                </div>
            </form>
        </div>
    </div>
@endsection
