@php
    $kyc = $kycStatus ?? null;
    $onDocs = request()->routeIs('vendor.documents.*', 'outdoor-panel.documents.*');
    $docsRoute = ($isOutdoorPanel ?? false) ? 'outdoor-panel.documents.index' : 'vendor.documents.index';
    $ctaHref = $onDocs ? '#kyc-upload' : route($docsRoute);
    $ctaLabel = ($kyc['cta'] ?? '') === 'complete' ? 'Eksikleri tamamla' : 'Doğrulamaya başla';
@endphp
@if($kyc && ($kyc['cta'] ?? 'none') !== 'none')
    <div class="alert alert-warning border-warning d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            @if(($kyc['cta'] ?? '') === 'start')
                <strong class="d-block">Doğrulama henüz başlamadı.</strong>
                <div class="small mb-0">Satışa açılmak için resmi evrak yükleyin{{ !empty($kyc['missing']) ? ': '.collect($kyc['missing'])->pluck('label')->join(', ') : '.' }}</div>
            @else
                <strong class="d-block">Eksik evrak var.</strong>
                <div class="small mb-0">{{ collect($kyc['missing'] ?? [])->pluck('label')->join(', ') }}</div>
            @endif
        </div>
        <a href="{{ $ctaHref }}" class="btn btn-warning text-dark fw-semibold px-4">{{ $ctaLabel }}</a>
    </div>
@endif
