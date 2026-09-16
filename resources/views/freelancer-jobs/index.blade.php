@extends('layouts.app')

@section('title', 'İş ilanları - BaskıYeri')

@section('content')
    <div class="content-shell py-4">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <h1 class="h4 mb-0">
            @if(!empty($currentCategory))
                @php
                    $catLabels = ['logo' => 'Logo & Kurumsal Kimlik', 'brochure' => 'Broşür & Katalog', 'digital' => 'Dijital İçerik', 'wordpress' => 'Web & WordPress', 'other' => 'Tabela & Diğer'];
                @endphp
                {{ ($catLabels[$currentCategory] ?? $currentCategory) . ' ilanları' }}
            @else
                İş ilanları
            @endif
        </h1>
            <div class="flex flex-wrap gap-3 items-center">
                @auth
                    <a href="{{ route('freelancer-jobs.create') }}" class="btn btn-warning btn-sm rounded-pill">+ İlan ver</a>
                    <a href="{{ route('freelancer-jobs.my') }}" class="small text-decoration-none">İlanlarım</a>
                @endauth
                <a href="{{ route('home') }}" class="small text-decoration-none text-muted">← Anasayfa</a>
            </div>
        </div>

        @if($jobs->isEmpty())
            <div class="alert alert-light border rounded-4">
                Şu an açık iş ilanı bulunmuyor.
            </div>
        @else
            <div class="row g-3 g-md-4">
                @foreach($jobs as $job)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="bg-white border rounded-4 overflow-hidden h-100 shadow-sm d-flex flex-column" style="transition: transform .2s, box-shadow .2s;">
                            <a href="{{ route('freelancer-jobs.show', $job) }}" class="text-decoration-none text-dark flex-grow-1 d-flex flex-column">
                                <div class="ratio ratio-16x10 bg-light overflow-hidden">
                                    <div class="bg-indigo-50 text-indigo-700 p-6 font-semibold">Tasarım ve üretim fırsatı</div>
                                </div>
                                <div class="p-3">
                                    <span class="badge bg-light text-dark small">{{ ['logo' => 'Logo ve kurumsal kimlik', 'brochure' => 'Broşür ve katalog', 'digital' => 'Dijital içerik', 'wordpress' => 'Web sitesi', 'other' => 'Diğer işler'][$job->category] ?? $job->category }}</span>
                                    <h2 class="h6 mt-2 mb-2">{{ Str::limit($job->title, 60) }}</h2>
                                    <p class="small text-muted mb-2">{{ Str::limit($job->description, 100) }}</p>
                                    @if($job->budget_min || $job->budget_max)
                                        <p class="small mb-0">
                                            <strong>Bütçe:</strong>
                                            ₺{{ $job->budget_min ? number_format($job->budget_min, 0, ',', '.') : '?' }}
                                            – ₺{{ $job->budget_max ? number_format($job->budget_max, 0, ',', '.') : '?' }}
                                        </p>
                                    @endif
                                    @if($job->user)
                                        <p class="small text-muted mt-2 mb-0">{{ $job->user->name }}</p>
                                    @endif
                                </div>
                            </a>
                            <div class="p-3 pt-0">
                                <a href="{{ route('freelancer-jobs.show', $job) }}" class="btn btn-warning rounded-pill w-100 btn-sm">İlanı incele ve teklif ver</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
@endsection
