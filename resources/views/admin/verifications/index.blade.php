@extends('layouts.admin')
@section('title', 'Doğrulamalar Kuyruğu')

@section('content')
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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    @endif

    {{-- Kuyruk Başlığı ve Durum Sekmeleri --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <ul class="nav nav-pills gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('admin.verifications.index', ['status' => 'pending']) }}">
                            Bekleyenler <span class="badge {{ $status === 'pending' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1">{{ $counts['pending'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}" href="{{ route('admin.verifications.index', ['status' => 'approved']) }}">
                            Onaylananlar <span class="badge {{ $status === 'approved' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1">{{ $counts['approved'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" href="{{ route('admin.verifications.index', ['status' => 'rejected']) }}">
                            Reddedilenler <span class="badge {{ $status === 'rejected' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1">{{ $counts['rejected'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'expired' ? 'active' : '' }}" href="{{ route('admin.verifications.index', ['status' => 'expired']) }}">
                            Süresi Dolanlar <span class="badge {{ $status === 'expired' ? 'bg-white text-primary' : 'bg-warning text-dark' }} ms-1">{{ $counts['expired'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="{{ route('admin.verifications.index', ['status' => 'all']) }}">
                            Tümü <span class="badge {{ $status === 'all' ? 'bg-white text-primary' : 'bg-light text-dark' }} ms-1">{{ $counts['all'] }}</span>
                        </a>
                    </li>
                </ul>

                <form method="get" action="{{ route('admin.verifications.index') }}" class="d-flex gap-2 w-auto">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tüm Belge Türleri</option>
                        @foreach(\App\Support\UiLabels::documentTypes() as $key => $lbl)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Satıcı veya Belge No Ara...">
                    <button class="btn btn-sm btn-outline-secondary">Ara</button>
                </form>
            </div>
        </div>

        {{-- Tablo --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="small">Satıcı & İletişim</th>
                        <th class="small">Belge Bilgileri</th>
                        <th class="small">Tarihler</th>
                        <th class="small">Durum</th>
                        <th class="small">İnceleme & Denetim Notu</th>
                        <th class="small text-end">Aksiyonlar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        @php
                            $v = $doc->vendor;
                            $u = $v?->user;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $v?->name ?: '—' }} <code class="small">{{ $u?->publicCode() }}</code> <span class="text-muted">#{{ $v?->id }}</span></div>
                                <div class="small text-muted">{{ $v?->company_name }}</div>
                                <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                    <x-trust-badge :vendor="$v" size="sm" />
                                    @if($u?->email_verified_at)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" title="E-posta Doğrulandı">E-Posta ✓</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary" title="E-posta Doğrulanmadı">E-Posta ✗</span>
                                    @endif
                                    @if($u?->phone_verified_at)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" title="Telefon Doğrulandı">Telefon ✓</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary" title="Telefon Doğrulanmadı">Telefon ✗</span>
                                    @endif
                                    @if($v?->is_suspended)
                                        <span class="badge bg-danger text-white">Askıda</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-primary">
                                    {{ \App\Support\UiLabels::documentType($doc->document_type) }}
                                </div>
                                @if($doc->issuing_institution)
                                    <div class="small text-muted">Kurum: {{ $doc->issuing_institution }}</div>
                                @endif
                                @if($doc->document_number)
                                    <div class="small text-muted">No: <code>{{ $doc->document_number }}</code></div>
                                @endif
                                <div class="mt-1">
                                    <a href="{{ $doc->downloadUrl(15) }}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                        Belgeyi Aç (İmzalı URL) ↗
                                    </a>
                                </div>
                            </td>
                            <td class="small">
                                <div>Yükleme: {{ $doc->created_at?->format('d.m.Y H:i') }}</div>
                                @if($doc->issued_at)
                                    <div>Düzenlenme: {{ $doc->issued_at->format('d.m.Y') }}</div>
                                @endif
                                <div>
                                    Geçerlilik:
                                    @if($doc->expires_at)
                                        <span class="{{ $doc->isExpired() ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ $doc->expires_at->format('d.m.Y') }}
                                            @if($doc->isExpired()) (Süresi Doldu) @endif
                                        </span>
                                    @else
                                        <span class="text-muted">Süresiz</span>
                                    @endif
                                </div>
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
                                    <span class="badge bg-secondary">Beklemede</span>
                                @endif
                            </td>
                            <td class="small" style="max-width: 250px;">
                                @if($doc->reviewed_by && $doc->reviewer)
                                    <div class="fw-semibold">{{ $doc->reviewer->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $doc->reviewed_at?->format('d.m.Y H:i') }}</div>
                                @endif
                                @if($doc->rejection_reason)
                                    <div class="text-danger mt-1">
                                        <strong>Ret Gerekçesi:</strong> {{ $doc->rejection_reason }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex gap-1">
                                    @if($doc->status === 'pending')
                                        {{-- Onayla Formu --}}
                                        <form method="post" action="{{ route('admin.verifications.approve', $doc) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success" title="Belgeyi Onayla">Onayla</button>
                                        </form>

                                        {{-- Reddet Modal Butonu --}}
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $doc->id }}" title="Gerekçeli Reddet">
                                            Reddet
                                        </button>
                                    @endif

                                    {{-- Satıcı Askı Durumu Toggling --}}
                                    @if($v)
                                        @if(! $v->is_suspended)
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#suspendModal{{ $v->id }}" title="Satıcıyı Askıya Al">
                                                Askıya Al
                                            </button>
                                        @else
                                            <form method="post" action="{{ route('admin.verifications.unsuspend', $v) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-info text-white" title="Askıyı Kaldır">Askıyı Kaldır</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>

                                {{-- Ret Modalı --}}
                                <div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $doc->id }}" aria-hidden="true">
                                    <div class="modal-dialog text-start">
                                        <form method="post" action="{{ route('admin.verifications.reject', $doc) }}">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title h6" id="rejectModalLabel{{ $doc->id }}">Belgeyi Reddet (Gerekçe Zorunlu)</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="small text-muted">
                                                        Reddedilen belgenin gerekçesi satıcıya gösterilecek ve denetim günlüğüne (audit log) kaydedilecektir.
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Ret Nedeni <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Örn. Belge okunaklı değil, güncel vergi levhası yüklenmelidir."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                                                    <button type="submit" class="btn btn-danger btn-sm">Gerekçeli Reddet</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Askıya Alma Modalı --}}
                                @if($v && ! $v->is_suspended)
                                    <div class="modal fade" id="suspendModal{{ $v->id }}" tabindex="-1" aria-labelledby="suspendModalLabel{{ $v->id }}" aria-hidden="true">
                                        <div class="modal-dialog text-start">
                                            <form method="post" action="{{ route('admin.verifications.suspend', $v) }}">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title h6" id="suspendModalLabel{{ $v->id }}">Satıcıyı Askıya Al (#{{ $v->id }} {{ $v->name }})</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning small">
                                                            Askıya alınan satıcının tüm güven rozetleri derhal 0'a çekilir ve teklif/ürün listelemeleri dondurulur.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Askıya Alma Nedeni <span class="text-danger">*</span></label>
                                                            <textarea name="suspension_reason" class="form-control" rows="3" required placeholder="Örn. Sahte evrak şüphesi, müşteri şikayetleri..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                                                        <button type="submit" class="btn btn-warning btn-sm">Gerekçeli Askıya Al</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Bu filtreye uygun doğrulama kaydı bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top">
            {{ $documents->links() }}
        </div>
    </div>
</div>
@endsection
