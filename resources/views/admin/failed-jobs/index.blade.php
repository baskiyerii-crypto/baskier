@extends('layouts.admin')

@section('title', 'Başarısız Kuyruk İşleri')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1 fw-bold">Başarısız Kuyruk İşleri (Dead-Letter)</h1>
            <p class="text-muted small mb-0">Hata alan asenkron işler, deneme sayıları ve istisna ayrıntıları.</p>
        </div>
        @if($failedJobs->isNotEmpty())
        <div class="d-flex gap-2">
            <form action="{{ route('admin.failed-jobs.retry-all') }}" method="POST" onsubmit="return confirm('Tüm başarısız işler yeniden denensin mi?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" class="me-1" viewBox="0 0 24 24"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    Tümünü Yeniden Dene
                </button>
            </form>
            <form action="{{ route('admin.failed-jobs.destroy-all') }}" method="POST" onsubmit="return confirm('Tüm başarısız işler silinsin mi?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    Tümünü Temizle
                </button>
            </form>
        </div>
        @endif
    </div>

    @if($failedJobs->isEmpty())
        <div class="card p-5 text-center shadow-sm">
            <div class="mb-3">
                <span class="badge bg-success-subtle text-success p-3 rounded-circle" style="font-size: 1.5rem;">✓</span>
            </div>
            <h5 class="fw-bold mb-1">Harika! Başarısız iş bulunmuyor</h5>
            <p class="text-muted small mb-0">Kuyruk sağlıklı çalışıyor, bekleyen veya takılan iş yok.</p>
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>İş (Job) / Bağlantı</th>
                            <th>Kuyruk</th>
                            <th>Hata Özeti</th>
                            <th style="width: 160px;">Tarih</th>
                            <th style="width: 140px;" class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($failedJobs as $job)
                            @php
                                $payload = json_decode($job->payload, true);
                                $displayName = $payload['displayName'] ?? ($payload['data']['commandName'] ?? 'Bilinmeyen İş');
                                $shortError = Str::limit(strtok($job->exception, "\n"), 120);
                            @endphp
                            <tr>
                                <td><span class="badge bg-secondary">#{{ $job->id }}</span></td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 260px;" title="{{ $displayName }}">{{ class_basename($displayName) }}</div>
                                    <div class="small text-muted">{{ $job->connection }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $job->queue }}</span></td>
                                <td>
                                    <code class="text-danger small d-inline-block text-truncate" style="max-width: 320px;" title="{{ $job->exception }}">{{ $shortError }}</code>
                                </td>
                                <td class="small text-muted">{{ $job->failed_at }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <form action="{{ route('admin.failed-jobs.retry', $job->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Yeniden Dene">
                                                Dene
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.failed-jobs.destroy', $job->id) }}" method="POST" onsubmit="return confirm('Bu iş silinsin mi?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($failedJobs->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $failedJobs->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection