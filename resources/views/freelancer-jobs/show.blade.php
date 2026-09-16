@extends('layouts.app')

@section('title', $job->title . ' - İş ilanı - BaskıYeri')

@section('content')
    <div class="content-shell py-4">
        <nav class="mb-3">
            <a href="{{ route('freelancer-jobs.index') }}" class="small text-decoration-none text-muted">← İş ilanları</a>
        </nav>

        <div class="rounded-4 overflow-hidden shadow-sm mb-4 p-4 p-md-5 text-white" style="background: linear-gradient(135deg, #1e293b, #334155);">
            <span class="badge bg-white/20 text-white border border-white/30 px-3 py-1.5 rounded-pill">{{ $job->category }}</span>
            <h1 class="h3 fw-bold mt-2 mb-1 text-white">{{ $job->title }}</h1>
            <p class="text-white/70 small mb-0">İlan No: #{{ $job->id }} · Yayınlanma: {{ $job->created_at->format('d.m.Y') }}</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="bg-white border rounded-4 p-4 p-md-5 shadow-sm">
                    <span class="badge bg-light text-dark">{{ $job->category }}</span>
                    <h1 class="h4 mt-3 mb-3">{{ $job->title }}</h1>
                    @if($job->user)
                        <p class="small text-muted mb-3">İlan sahibi: {{ $job->user->name }}</p>
                    @endif
                    <div class="text-muted small mb-3">
                        @if($job->budget_min || $job->budget_max)
                            <strong>Bütçe:</strong>
                            ₺{{ $job->budget_min ? number_format($job->budget_min, 0, ',', '.') : '?' }}
                            – ₺{{ $job->budget_max ? number_format($job->budget_max, 0, ',', '.') : '?' }}
                        @endif
                        @if($job->bids_count !== null)
                            · {{ $job->bids_count }} teklif
                        @endif
                    </div>
                    <hr>
                    <div class="prose">
                        {!! nl2br(e($job->description)) !!}
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="bg-white border rounded-4 p-4 shadow-sm">
                    @guest
                        <p class="small text-muted mb-0">Teklif vermek için giriş yapın.</p>
                        <a href="{{ route('login') }}" class="btn btn-warning rounded-pill mt-3 w-100">Giriş yap</a>
                    @else
                        @if(auth()->id() === $job->user_id)
                            <h3 class="h6">Gelen teklifler</h3>
                            @forelse($job->bids as $bid)
                                <div class="border rounded-3 p-2 mb-2 small">
                                    <div class="fw-semibold">{{ $bid->user?->name }} — ₺{{ number_format($bid->amount, 2, ',', '.') }}</div>
                                    @if($bid->proposal)<div class="text-muted mt-1">{{ Str::limit($bid->proposal, 120) }}</div>@endif
                                    @if($bid->status === 'pending')
                                        <form action="{{ route('freelancer-jobs.select-bid', [$job, $bid]) }}" method="post" class="mt-2" onsubmit="return confirm('Bu teklifi seçmek sipariş oluşturur. Devam?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success w-100">Bu teklifi seç</button>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary mt-1">{{ \App\Support\UiLabels::status($bid->status) }}</span>
                                    @endif
                                </div>
                            @empty
                                <p class="small text-muted mb-0">Henüz teklif yok.</p>
                            @endforelse
                        @else
                            <h3 class="h6">Teklif ver</h3>
                            <form action="{{ route('freelancer-jobs.bid', $job) }}" method="post">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">Tutar (₺)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Teslim süresi (gün)</label>
                                    <input type="number" name="delivery_days" class="form-control form-control-sm" min="1">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Öneri metni</label>
                                    <textarea name="proposal" class="form-control form-control-sm" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm w-100 rounded-pill">Teklif gönder</button>
                            </form>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
    </div>
@endsection
