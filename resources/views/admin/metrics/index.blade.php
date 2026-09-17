@extends('layouts.admin')

@section('title', 'Sistem Metrikleri ve Gözlemlenebilirlik')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1 fw-bold">Platform Sağlık ve Operasyon Metrikleri</h1>
            <p class="text-muted small mb-0">Ödeme başarı oranları, kuyruk derinliği, stok rezervasyonları ve SLA durumları.</p>
        </div>
        <div>
            <a href="{{ route('ready') }}" target="_blank" class="btn btn-sm btn-outline-success">
                <span class="me-1">●</span> /ready Probe Test
            </a>
        </div>
    </div>

    <!-- KPI Kartları -->
    <div class="row g-3 mb-4">
        <!-- 24 Saatlik Ödeme Başarısı -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card p-3 shadow-sm border-0 h-100">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Ödeme Başarı Oranı (24s)</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="h3 mb-0 fw-bold {{ $metrics['payments']['success_rate_24h'] >= 90 ? 'text-success' : 'text-danger' }}">
                        %{{ $metrics['payments']['success_rate_24h'] }}
                    </span>
                    <span class="text-muted small">({{ $metrics['payments']['paid_orders_24h'] }}/{{ $metrics['payments']['total_orders_24h'] }} sipariş)</span>
                </div>
                <div class="small text-muted mt-2">Toplam Ciro: ₺{{ number_format($metrics['payments']['revenue_24h'], 2, ',', '.') }}</div>
            </div>
        </div>

        <!-- 7 Günlük Ödeme Başarısı -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card p-3 shadow-sm border-0 h-100">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Ödeme Başarı Oranı (7g)</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="h3 mb-0 fw-bold {{ $metrics['payments']['success_rate_7d'] >= 90 ? 'text-success' : 'text-warning' }}">
                        %{{ $metrics['payments']['success_rate_7d'] }}
                    </span>
                    <span class="text-muted small">({{ $metrics['payments']['paid_orders_7d'] }}/{{ $metrics['payments']['total_orders_7d'] }})</span>
                </div>
                <div class="small text-muted mt-2">Haftalık Trend</div>
            </div>
        </div>

        <!-- Kuyruk Durumu -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card p-3 shadow-sm border-0 h-100">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Asenkron Kuyruk</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="h3 mb-0 fw-bold {{ $metrics['queue']['failed_jobs'] > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $metrics['queue']['pending_jobs'] }} Bekleyen
                    </span>
                </div>
                <div class="small mt-2">
                    @if($metrics['queue']['failed_jobs'] > 0)
                        <span class="text-danger fw-semibold">{{ $metrics['queue']['failed_jobs'] }} başarısız kuyruk işi</span>
                    @else
                        <span class="text-success">✓ 0 Hata / Sağlıklı</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stok Rezervasyonu -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card p-3 shadow-sm border-0 h-100">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Stok Rezervasyonları</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="h3 mb-0 fw-bold text-primary">
                        {{ $metrics['stock_reservations']['active'] }} Aktif
                    </span>
                    <span class="text-muted small">({{ $metrics['stock_reservations']['expired'] }} süresi dolmuş)</span>
                </div>
                <div class="small text-muted mt-2">Sepet kilitleri otomatik salınır</div>
            </div>
        </div>
    </div>

    <!-- Güven ve Afet Kurtarma Tabloları -->
    <div class="row g-3">
        <!-- Satıcı Güven Dağılımı -->
        <div class="col-12 col-lg-6">
            <div class="card p-4 shadow-sm border-0 h-100">
                <h5 class="fw-bold mb-3">Satıcı Güven Seviyesi (1/2/3 Tik) Dağılımı</h5>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><span class="badge bg-secondary me-2">Seviye 0</span> Doğrulanmamış Satıcılar</span>
                        <span class="fw-bold">{{ $metrics['verification']['vendors_by_trust']['level_0'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><span class="badge bg-info text-dark me-2">1 Tik</span> E-Posta + Telefon Onaylı</span>
                        <span class="fw-bold">{{ $metrics['verification']['vendors_by_trust']['level_1'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><span class="badge bg-primary me-2">2 Tik</span> Vergi / Mesleki Belge Onaylı</span>
                        <span class="fw-bold">{{ $metrics['verification']['vendors_by_trust']['level_2'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><span class="badge bg-success me-2">3 Tik</span> Kurumsal / Tam Güvenli</span>
                        <span class="fw-bold">{{ $metrics['verification']['vendors_by_trust']['level_3'] }}</span>
                    </li>
                </ul>
                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Bekleyen Evrak İncelemeleri:</span>
                    <a href="{{ route('admin.verifications.index') }}" class="badge bg-warning text-dark text-decoration-none">
                        {{ $metrics['verification']['pending_documents'] }} Evrak Bekliyor →
                    </a>
                </div>
            </div>
        </div>

        <!-- Afet Kurtarma & Yedek Doğrulama (RPO/RTO) -->
        <div class="col-12 col-lg-6">
            <div class="card p-4 shadow-sm border-0 h-100">
                <h5 class="fw-bold mb-3">Afet Kurtarma ve Sentetik Restore Durumu</h5>
                @if($metrics['disaster_recovery']['last_verified_at'])
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                        <span class="h4 mb-0">🛡</span>
                        <div>
                            <div class="fw-bold small">Son Restore Tatbikatı Başarılı</div>
                            <div class="small">{{ $metrics['disaster_recovery']['last_verified_at'] }}</div>
                        </div>
                    </div>
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="text-muted small">Hedef RPO (Veri Kaybı)</div>
                                <div class="fw-bold text-dark">{{ $metrics['disaster_recovery']['rpo_seconds'] }} saniye</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="text-muted small">Gerçekleşen RTO (Kurtarma)</div>
                                <div class="fw-bold text-dark">{{ $metrics['disaster_recovery']['rto_ms'] }} ms</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning small mb-3">
                        Henüz otomatik restore tatbikatı çalıştırılmadı. Terminalden <code>php artisan platform:restore-verify</code> komutunu çalıştırabilirsiniz.
                    </div>
                @endif
                <div class="text-muted small mt-3">
                    Günlük otomatik şifreli yedekler her gece saat 03:00'te alınmakta ve SHA-256 bütünlük kontrolü ile saklanmaktadır.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection