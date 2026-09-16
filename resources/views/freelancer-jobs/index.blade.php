@extends('layouts.app')

@section('title', __('ui.jobs').' - BaskiYeri')

@section('content')
    <div class="content-shell py-4">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <h1 class="h4 mb-0">
            @if(!empty($currentCategory))
                {{ \App\Support\FreelancerCategories::label($currentCategory) }}
            @else
                {{ __('ui.jobs') }}
            @endif
        </h1>
            <div class="flex flex-wrap gap-3 items-center">
                @auth
                    <a href="{{ route('freelancer-jobs.create') }}" class="btn btn-warning btn-sm rounded-pill">+ {{ __('home.path_freelancer_cta') }}</a>
                    <a href="{{ route('freelancer-jobs.my') }}" class="small text-decoration-none">{{ __('ui.jobs') }}</a>
                @endauth
                <a href="{{ route('home') }}" class="small text-decoration-none text-muted">← {{ __('ui.home') }}</a>
            </div>
        </div>

        @if($jobs->isEmpty())
            <div class="alert alert-light border rounded-4">
                {{ __('home.no_jobs_open') }}
            </div>
        @else
            <div class="row g-3 g-md-4">
                @foreach($jobs as $job)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="bg-white border rounded-4 overflow-hidden h-100 shadow-sm d-flex flex-column" style="transition: transform .2s, box-shadow .2s;">
                            <a href="{{ route('freelancer-jobs.show', $job) }}" class="text-decoration-none text-dark flex-grow-1 d-flex flex-column">
                                <div class="ratio ratio-16x10 bg-light overflow-hidden">
                                    <div class="bg-indigo-50 text-indigo-700 p-6 font-semibold">{{ \App\Support\FreelancerCategories::label($job->category) }}</div>
                                </div>
                                <div class="p-3">
                                    <span class="badge bg-light text-dark small">{{ \App\Support\FreelancerCategories::label($job->category) }}</span>
                                    <h2 class="h6 mt-2 mb-2">{{ Str::limit($job->title, 60) }}</h2>
                                    <p class="small text-muted mb-2">{{ Str::limit($job->description, 100) }}</p>
                                    @if($job->budget_min || $job->budget_max)
                                        <p class="small mb-0">
                                            <strong>{{ __('home.budget') }}:</strong>
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
