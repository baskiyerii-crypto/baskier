@extends('layouts.app')

@section('title', 'Hizmet Talebi Oluştur - BaskıYeri')

@section('content')
<div class="by-container py-8 md:py-12">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('service-requests.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Hizmet Taleplerine Dön
            </a>
            <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink">Hizmet Talebi Oluştur</h1>
            <p class="text-sm text-muted mt-1">Grafik tasarım, vektör çizim, mizanpaj veya baskı hazırlık işiniz için uzmanlardan teklif toplayın.</p>
        </div>

        <div class="by-card p-6 md:p-8 bg-surface border border-border">
            <form action="{{ route('service-requests.store') }}" method="post" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">{{ __('ui.category') }} <span class="text-red-500">*</span></label>
                    <select name="category" class="form-control" required>
                        @foreach(\App\Support\FreelancerCategories::keys() as $key)
                            <option value="{{ $key }}" @selected(old('category', $selectedCategory ?? '') === $key)>{{ \App\Support\FreelancerCategories::label($key) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">{{ __('home.job_title') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255" placeholder="Örn: Kurumsal kimlik ve kartvizit tasarımı">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">{{ __('home.description') }}</label>
                    <textarea name="description" class="form-control min-h-[120px]" rows="5" placeholder="İhtiyacınız olan hizmeti, beklentilerinizi, kullanılacak renk veya referans tarzları detaylandırın...">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">{{ __('home.budget') }} Min (₺)</label>
                        <input type="number" step="0.01" name="budget_min" class="form-control" value="{{ old('budget_min') }}" placeholder="Örn: 500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-muted mb-1.5">{{ __('home.budget') }} Max (₺)</label>
                        <input type="number" step="0.01" name="budget_max" class="form-control" value="{{ old('budget_max') }}" placeholder="Örn: 2000">
                    </div>
                </div>

                <div class="pt-3 border-t border-border flex items-center justify-between">
                    <button type="submit" class="btn btn-cta">
                        {{ __('home.publish') }}
                    </button>
                    <a href="{{ route('service-requests.index') }}" class="btn btn-secondary">
                        {{ __('panel.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
