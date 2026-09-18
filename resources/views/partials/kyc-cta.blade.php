@php
    $kyc = $kycStatus ?? null;
    $docsRoute = ($isOutdoorPanel ?? false) ? 'outdoor-panel.documents.index' : 'vendor.documents.index';
@endphp
@if($kyc && ($kyc['cta'] ?? 'none') !== 'none')
    <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            @if($kyc['cta'] === 'start')
                <strong>Doğrulama henüz başlamadı.</strong>
                <div class="small mb-0">Platformda satışa devam etmek için resmi evraklarınızı yükleyin.</div>
            @else
                <strong>Eksik evrak var.</strong>
                <div class="small mb-0">
                    @foreach($kyc['missing'] as $m)
                        {{ $m['label'] }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </div>
            @endif
        </div>
        <a href="{{ route($docsRoute) }}" class="btn btn-sm btn-warning text-dark fw-semibold">
            {{ $kyc['cta'] === 'start' ? 'Doğrulamaya başla' : 'Eksikleri tamamla' }}
        </a>
    </div>
@endif
