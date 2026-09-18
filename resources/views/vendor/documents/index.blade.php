@extends($layout ?? 'layouts.vendor')
@section('title', 'Belgeler ve Doğrulama')

@section('content')
@php $isOutdoorPanel = $isOutdoorPanel ?? false; @endphp
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    @endif

    {{-- 1. Güven Seviyesi ve Doğrulama Durumu Kartı --}}
    <div class="card p-4 mb-4 border-0 shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 border-bottom pb-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="h5 mb-0 font-bold">Güven ve Doğrulama Durumu</h1>
                    <x-trust-badge :vendor="$vendor" size="md" />
                </div>
                <p class="text-muted small mb-0">
                    BaskıYeri pazarında güven seviyeniz; iletişim bilgileriniz, resmi belgeleriniz ve sipariş performansınız doğrultusunda sistem tarafından tarafsız ve otomatik hesaplanır.
                </p>
            </div>
            <div class="text-md-end">
                <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                    Mevcut Seviye: <strong>{{ \App\Domain\TrustLevel::label($vendor->trust_level) }}</strong>
                </span>
            </div>
        </div>

        @if($vendor->is_suspended)
            <div class="alert alert-danger mb-4">
                <strong>Hesabınız Askıya Alındı:</strong> {{ $vendor->suspension_reason ?: 'Yönetim tarafından incelenmektedir.' }}
            </div>
        @endif

        {{-- 3 Seviyeli Güven Kontrol Listesi --}}
        <div class="row g-3">
            {{-- Seviye 1 --}}
            <div class="col-md-4">
                <div class="p-3 rounded-3 border h-100 {{ $criteria['level_1']['passed'] ? 'bg-light-subtle border-success-subtle' : 'bg-white' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-slate-800">1 Tik — İletişim</span>
                        @if($criteria['level_1']['passed'])
                            <span class="badge bg-success text-white">Tamamlandı</span>
                        @else
                            <span class="badge bg-secondary text-white">Eksik</span>
                        @endif
                    </div>
                    <ul class="list-unstyled small mb-0 space-y-1">
                        <li>
                            {!! $criteria['level_1']['email_verified'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>' !!}
                            E-posta Doğrulaması
                        </li>
                        <li>
                            {!! $criteria['level_1']['phone_verified'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>' !!}
                            Telefon (SMS/OTP) Doğrulaması
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Seviye 2 --}}
            <div class="col-md-4">
                <div class="p-3 rounded-3 border h-100 {{ $criteria['level_2']['passed'] ? 'bg-light-subtle border-success-subtle' : 'bg-white' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-slate-800">2 Tik — Evrak & Kimlik</span>
                        @if($criteria['level_2']['passed'])
                            <span class="badge bg-success text-white">Tamamlandı</span>
                        @else
                            <span class="badge bg-secondary text-white">Eksik</span>
                        @endif
                    </div>
                    <ul class="list-unstyled small mb-0 space-y-1">
                        @if($criteria['level_2']['has_physical'])
                            <li>
                                {!! $criteria['level_2']['physical_doc_approved'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>' !!}
                                Vergi Levhası / Sicil Belgesi
                            </li>
                        @endif
                        @if($criteria['level_2']['has_freelancer'])
                            <li>
                                {!! $criteria['level_2']['freelancer_doc_approved'] ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>' !!}
                                Mesleki Diploma / Sertifika / Akreditasyon
                            </li>
                        @endif
                    </ul>
                    @if($criteria['level_2']['has_freelancer'])
                        <div class="mt-2 text-muted" style="font-size: 0.72rem;">
                            * Freelancer kolunda vergi levhası mesleki yeterlilik belgesi yerine geçmez.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Seviye 3 --}}
            <div class="col-md-4">
                <div class="p-3 rounded-3 border h-100 {{ $criteria['level_3']['passed'] ? 'bg-light-subtle border-success-subtle' : 'bg-white' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-slate-800">3 Tik — Üstün Başarı</span>
                        @if($criteria['level_3']['passed'])
                            <span class="badge bg-success text-white">Tamamlandı</span>
                        @else
                            <span class="badge bg-secondary text-white">Gereksinimler Var</span>
                        @endif
                    </div>
                    <ul class="list-unstyled small mb-0 space-y-1">
                        <li>
                            {!! $criteria['level_3']['completed_orders_count'] >= 5 ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>' !!}
                            En az 5 teslim edilmiş sipariş (Mevcut: {{ $criteria['level_3']['completed_orders_count'] }})
                        </li>
                        <li>
                            {!! $criteria['level_3']['rating_average'] >= 4.5 ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>' !!}
                            Ortalama puan ≥ 4.5 (Mevcut: {{ number_format($criteria['level_3']['rating_average'], 1) }})
                        </li>
                        <li>
                            {!! $criteria['level_3']['dispute_rate_percent'] <= 2.0 ? '<span class="text-success">✓</span>' : '<span class="text-danger">✗</span>' !!}
                            Son 12 ay iptal/uyuşmazlık ≤ %2 (Mevcut: %{{ $criteria['level_3']['dispute_rate_percent'] }})
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Birleştirilmiş Belge Yükleme Formu --}}
    <div class="card p-4 mb-4 border-0 shadow-sm">
        <h2 class="h6 fw-bold mb-3">Yeni Belge Yükle</h2>
        <p class="small text-muted mb-3">
            Tüm belgeleriniz güvenli özel depolamada saklanır ve yalnızca yetkili yönetici incelemesi için kullanılır. Yüklenen dosyalar gerçek dosya içerik kontrolünden ve güvenlik taramasından geçirilir.
        </p>

        <form method="post" action="{{ route($isOutdoorPanel ? 'outdoor-panel.documents.store' : 'vendor.documents.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Belge Türü <span class="text-danger">*</span></label>
                    <select name="document_type" class="form-select" required>
                        @if($isOutdoorPanel)
                            @if($vendor->isMunicipalityOwner())
                                <optgroup label="Belediye">
                                    <option value="municipality_authority">{{ __('panel.doc_municipality_authority') }}</option>
                                    <option value="outdoor_permit">{{ __('panel.outdoor_permit') }}</option>
                                </optgroup>
                            @else
                                <optgroup label="Kurumsal">
                                    <option value="tax_plate">{{ __('panel.doc_tax_plate') }}</option>
                                    <option value="trade_registry">{{ __('panel.doc_trade_registry') }}</option>
                                </optgroup>
                                <optgroup label="Açık hava">
                                    <option value="outdoor_permit">{{ __('panel.outdoor_permit') }}</option>
                                </optgroup>
                            @endif
                        @else
                            @if($vendor->hasPhysicalTrack() || $vendor->hasOutdoorTrack() || empty($vendor->registration_tracks))
                            <optgroup label="Fiziki Mağaza / Üretim">
                                <option value="tax_plate">{{ __('panel.doc_tax_plate') }}</option>
                                <option value="company_registration">{{ __('panel.doc_company_registration') }}</option>
                            </optgroup>
                            @endif
                            @if($vendor->hasOutdoorTrack())
                            <optgroup label="Açık hava">
                                <option value="outdoor_permit">{{ __('panel.outdoor_permit') }}</option>
                                @if($vendor->isMunicipalityOwner())
                                    <option value="municipality_authority">{{ __('panel.doc_municipality_authority') }}</option>
                                @endif
                                @if($vendor->isOutdoorAgency() || $vendor->isOutdoorOwner())
                                    <option value="trade_registry">{{ __('panel.doc_trade_registry') }}</option>
                                @endif
                            </optgroup>
                            @endif
                            @if($vendor->hasFreelancerTrack())
                            <optgroup label="Freelancer Mesleki Belgeler">
                                <option value="diploma">{{ __('panel.doc_diploma') }}</option>
                                <option value="certificate">{{ __('panel.doc_certificate') }}</option>
                                <option value="portfolio_accreditation">{{ __('panel.doc_portfolio_accreditation') }}</option>
                                <option value="course">{{ __('panel.doc_course') }}</option>
                            </optgroup>
                            @endif
                        @endif
                        <optgroup label="Diğer">
                            <option value="other">{{ __('panel.doc_other') }}</option>
                        </optgroup>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Veren Kurum / Üniversite / Kuruluş</label>
                    <input type="text" name="issuing_institution" class="form-control" placeholder="Örn. Gelir İdaresi, İTÜ, Adobe...">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Belge / Ruhsat Numarası</label>
                    <input type="text" name="document_number" class="form-control" placeholder="Varsa kayıt veya sertifika no">
                </div>

                <div class="col-md-3">
                    <label for="issued_at" class="form-label small fw-semibold" style="cursor: pointer;">Düzenlenme Tarihi 📅</label>
                    <input type="date" id="issued_at" name="issued_at" class="form-control date-picker-input" style="cursor: pointer;">
                </div>

                <div class="col-md-3">
                    <label for="expires_at" class="form-label small fw-semibold" style="cursor: pointer;">Geçerlilik Bitiş Tarihi 📅</label>
                    <input type="date" id="expires_at" name="expires_at" class="form-control date-picker-input" style="cursor: pointer;">
                    <span class="text-muted" style="font-size: 0.72rem;">Süresiz ise boş bırakabilirsiniz.</span>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Dosya (PDF, JPG, PNG, WEBP - Max 10MB) <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Yükle ve Gönder</button>
                </div>
            </div>
        </form>
    </div>

    {{-- 3. Yüklenen Belgeler Tablosu --}}
    <div class="card p-4 border-0 shadow-sm">
        <h2 class="h6 fw-bold mb-3">Yüklenen Belgeler</h2>
        @if($documents->isEmpty())
            <p class="small text-muted mb-0">Henüz yüklenmiş bir belge bulunmuyor.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Belge Türü</th>
                            <th class="small">Veren Kurum / No</th>
                            <th class="small">Yükleme Tarihi</th>
                            <th class="small">Geçerlilik</th>
                            <th class="small">Durum</th>
                            <th class="small">İnceleme Notu</th>
                            <th class="small text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ \App\Support\UiLabels::documentType($doc->document_type) }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $doc->original_filename ?: basename($doc->path) }}</div>
                                </td>
                                <td>
                                    <div>{{ $doc->issuing_institution ?: '—' }}</div>
                                    @if($doc->document_number)
                                        <code class="small text-muted">{{ $doc->document_number }}</code>
                                    @endif
                                </td>
                                <td class="small">{{ $doc->created_at?->format('d.m.Y H:i') }}</td>
                                <td class="small">
                                    @if($doc->expires_at)
                                        <span class="{{ $doc->isExpired() ? 'text-danger fw-bold' : '' }}">
                                            {{ $doc->expires_at->format('d.m.Y') }}
                                            @if($doc->isExpired()) (Süresi Doldu) @endif
                                        </span>
                                    @else
                                        <span class="text-muted">Süresiz</span>
                                    @endif
                                </td>
                                <td>
                                    @if($doc->status === 'approved')
                                        @if($doc->isExpired())
                                            <span class="badge bg-warning text-dark">Süresi Doldu</span>
                                        @else
                                            <span class="badge bg-success">Onaylandı</span>
                                        @endif
                                    @elseif($doc->status === 'rejected')
                                        <span class="badge bg-danger">Reddedildi</span>
                                    @else
                                        <span class="badge bg-secondary">İncelemede</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($doc->status === 'rejected' && $doc->rejection_reason)
                                        <span class="text-danger fw-semibold">{{ $doc->rejection_reason }}</span>
                                    @elseif($doc->reviewed_at)
                                        <span class="text-muted">{{ $doc->reviewed_at->format('d.m.Y') }} tarihinde incelendi</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ $doc->downloadUrl() }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                                        İncele / İndir ↗
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tarih alanlarına veya label'larına tıklandığında takvim açıcıyı tetikle
    document.querySelectorAll('.date-picker-input').forEach(function(input) {
        input.addEventListener('click', function() {
            try {
                if (typeof input.showPicker === 'function') {
                    input.showPicker();
                }
            } catch (err) {
                // Desteklenmeyen tarayıcılar için varsayılan davranış
            }
        });
        input.addEventListener('focus', function() {
            try {
                if (typeof input.showPicker === 'function') {
                    input.showPicker();
                }
            } catch (err) {}
        });
    });

    ['issued_at', 'expires_at'].forEach(function(id) {
        const lbl = document.querySelector('label[for="' + id + '"]');
        const inp = document.getElementById(id);
        if (lbl && inp) {
            lbl.addEventListener('click', function(e) {
                e.preventDefault();
                inp.focus();
                try {
                    if (typeof inp.showPicker === 'function') {
                        inp.showPicker();
                    }
                } catch (err) {}
            });
        }
    });
});
</script>
@endsection
