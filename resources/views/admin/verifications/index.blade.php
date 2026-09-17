@extends('layouts.admin')
@section('title', 'Doğrulamalar Kuyruğu - Yönetim Paneli')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif
    @if($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Doğrulamalar Kuyruğu</h1>
            <p class="text-xs text-muted mt-0.5">Satıcı evrakları, vergi levhaları ve faaliyet belgelerinin denetim merkezi</p>
        </div>
    </div>

    {{-- Kuyruk Başlığı ve Durum Sekmeleri --}}
    <div class="by-card bg-surface border border-border overflow-hidden">
        <div class="p-4 border-b border-border bg-canvas/40 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-wrap gap-1.5">
                <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status === 'pending' ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('admin.verifications.index', ['status' => 'pending']) }}">
                    Bekleyenler <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] {{ $status === 'pending' ? 'bg-white/20 text-white' : 'bg-canvas text-muted border border-border' }}">{{ $counts['pending'] }}</span>
                </a>
                <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status === 'approved' ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('admin.verifications.index', ['status' => 'approved']) }}">
                    Onaylananlar <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] {{ $status === 'approved' ? 'bg-white/20 text-white' : 'bg-canvas text-muted border border-border' }}">{{ $counts['approved'] }}</span>
                </a>
                <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status === 'rejected' ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('admin.verifications.index', ['status' => 'rejected']) }}">
                    Reddedilenler <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] {{ $status === 'rejected' ? 'bg-white/20 text-white' : 'bg-canvas text-muted border border-border' }}">{{ $counts['rejected'] }}</span>
                </a>
                <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status === 'expired' ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('admin.verifications.index', ['status' => 'expired']) }}">
                    Süresi Dolanlar <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] {{ $status === 'expired' ? 'bg-white/20 text-white' : 'bg-canvas text-muted border border-border' }}">{{ $counts['expired'] }}</span>
                </a>
                <a class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $status === 'all' ? 'bg-ink text-white' : 'text-muted hover:text-ink hover:bg-canvas' }}" href="{{ route('admin.verifications.index', ['status' => 'all']) }}">
                    Tümü <span class="ml-1 px-1.5 py-0.2 rounded text-[10px] {{ $status === 'all' ? 'bg-white/20 text-white' : 'bg-canvas text-muted border border-border' }}">{{ $counts['all'] }}</span>
                </a>
            </div>

            <form method="get" action="{{ route('admin.verifications.index') }}" class="flex flex-wrap gap-2 items-center">
                <input type="hidden" name="status" value="{{ $status }}">
                <select name="type" class="form-control text-xs max-w-[160px]" onchange="this.form.submit()">
                    <option value="">Tüm Belge Türleri</option>
                    @foreach(\App\Support\UiLabels::documentTypes() as $key => $lbl)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control text-xs min-w-[160px]" placeholder="Satıcı / Belge No Ara...">
                <button class="btn btn-secondary text-xs">Ara</button>
            </form>
        </div>

        {{-- Tablo --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border bg-canvas/60 text-xs font-semibold uppercase tracking-wider text-muted">
                        <th class="px-5 py-3">Satıcı &amp; İletişim</th>
                        <th class="px-5 py-3">Belge Bilgileri</th>
                        <th class="px-5 py-3">Tarihler</th>
                        <th class="px-5 py-3">Durum</th>
                        <th class="px-5 py-3">İnceleme Notu</th>
                        <th class="px-5 py-3 text-right">Aksiyonlar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($documents as $doc)
                        @php
                            $v = $doc->vendor;
                            $u = $v?->user;
                        @endphp
                        <tr class="hover:bg-canvas/30 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-bold text-xs text-ink">{{ $v?->name ?: '—' }}</div>
                                <div class="text-[11px] text-muted">{{ $v?->company_name }}</div>
                                <div class="mt-1.5 flex flex-wrap gap-1 items-center">
                                    <x-trust-badge :vendor="$v" size="sm" />
                                    @if($u?->email_verified_at)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">E-Posta ✓</span>
                                    @endif
                                    @if($u?->phone_verified_at)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">Telefon ✓</span>
                                    @endif
                                    @if($v?->is_suspended)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-red-600 text-white font-bold">Askıda</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-xs text-cta">
                                    {{ \App\Support\UiLabels::documentType($doc->document_type) }}
                                </div>
                                @if($doc->issuing_institution)
                                    <div class="text-[11px] text-muted">Kurum: {{ $doc->issuing_institution }}</div>
                                @endif
                                @if($doc->document_number)
                                    <div class="text-[11px] text-muted">No: <code class="font-mono text-ink">{{ $doc->document_number }}</code></div>
                                @endif
                                <div class="mt-1">
                                    <a href="{{ $doc->downloadUrl(15) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[11px] text-cta hover:underline font-semibold">
                                        Belgeyi Aç (İmzalı URL) ↗
                                    </a>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs text-muted">
                                <div>Yükleme: {{ $doc->created_at?->format('d.m.Y H:i') }}</div>
                                @if($doc->issued_at)
                                    <div>Düzenlenme: {{ $doc->issued_at->format('d.m.Y') }}</div>
                                @endif
                                <div>
                                    Geçerlilik:
                                    @if($doc->expires_at)
                                        <span class="{{ $doc->isExpired() ? 'text-red-600 font-bold' : 'text-muted' }}">
                                            {{ $doc->expires_at->format('d.m.Y') }}
                                            @if($doc->isExpired()) (Süresi Doldu) @endif
                                        </span>
                                    @else
                                        <span>Süresiz</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($doc->status === 'approved')
                                    <x-badge :variant="$doc->isExpired() ? 'warning' : 'success'">
                                        {{ $doc->isExpired() ? 'Süresi Doldu' : 'Onaylandı' }}
                                    </x-badge>
                                @elseif($doc->status === 'rejected')
                                    <x-badge variant="danger">Reddedildi</x-badge>
                                @else
                                    <x-badge variant="neutral">Beklemede</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs max-w-[220px]">
                                @if($doc->reviewed_by && $doc->reviewer)
                                    <div class="font-semibold text-ink">{{ $doc->reviewer->name }}</div>
                                    <div class="text-[10px] text-muted">{{ $doc->reviewed_at?->format('d.m.Y H:i') }}</div>
                                @endif
                                @if($doc->rejection_reason)
                                    <div class="text-red-700 text-[11px] mt-1 p-1.5 rounded bg-red-50 border border-red-200">
                                        <strong>Ret:</strong> {{ $doc->rejection_reason }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex gap-1.5 items-center">
                                    @if($doc->status === 'pending')
                                        <form method="post" action="{{ route('admin.verifications.approve', $doc) }}" class="inline">
                                            @csrf
                                            <button class="btn btn-secondary text-xs py-1 px-2.5 text-emerald-700 hover:bg-emerald-50">Onayla</button>
                                        </form>

                                        <button type="button" class="btn btn-secondary text-xs py-1 px-2.5 text-red-600 hover:bg-red-50" onclick="document.getElementById('reject-form-{{ $doc->id }}').classList.toggle('hidden');">
                                            Reddet
                                        </button>
                                    @endif

                                    @if($v)
                                        @if(! $v->is_suspended)
                                            <button type="button" class="btn btn-secondary text-xs py-1 px-2.5 text-amber-700 hover:bg-amber-50" onclick="document.getElementById('suspend-form-{{ $v->id }}').classList.toggle('hidden');">
                                                Askıya Al
                                            </button>
                                        @else
                                            <form method="post" action="{{ route('admin.verifications.unsuspend', $v) }}" class="inline">
                                                @csrf
                                                <button class="btn btn-secondary text-xs py-1 px-2.5 text-sky-700 hover:bg-sky-50">Askıyı Kaldır</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>

                                {{-- Inline Reject Form --}}
                                <div id="reject-form-{{ $doc->id }}" class="hidden mt-2 p-3 rounded-xl bg-canvas border border-border text-left">
                                    <form method="post" action="{{ route('admin.verifications.reject', $doc) }}" class="space-y-2">
                                        @csrf
                                        <label class="block text-[11px] font-semibold text-ink">Ret Nedeni <span class="text-red-500">*</span></label>
                                        <textarea name="rejection_reason" class="form-control text-xs" rows="2" required placeholder="Gerekçe zorunludur..."></textarea>
                                        <div class="flex gap-2">
                                            <button type="submit" class="btn btn-cta text-[11px] py-1 px-3">Ret Onayla</button>
                                            <button type="button" class="btn btn-secondary text-[11px] py-1 px-3" onclick="document.getElementById('reject-form-{{ $doc->id }}').classList.add('hidden');">Vazgeç</button>
                                        </div>
                                    </form>
                                </div>

                                {{-- Inline Suspend Form --}}
                                @if($v && ! $v->is_suspended)
                                    <div id="suspend-form-{{ $v->id }}" class="hidden mt-2 p-3 rounded-xl bg-amber-50 border border-amber-200 text-left">
                                        <form method="post" action="{{ route('admin.verifications.suspend', $v) }}" class="space-y-2">
                                            @csrf
                                            <label class="block text-[11px] font-semibold text-amber-950">Askıya Alma Nedeni <span class="text-red-500">*</span></label>
                                            <textarea name="suspension_reason" class="form-control text-xs" rows="2" required placeholder="Askıya alma gerekçesi..."></textarea>
                                            <div class="flex gap-2">
                                                <button type="submit" class="btn btn-cta text-[11px] py-1 px-3">Askıya Al</button>
                                                <button type="button" class="btn btn-secondary text-[11px] py-1 px-3" onclick="document.getElementById('suspend-form-{{ $v->id }}').classList.add('hidden');">Vazgeç</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted text-xs py-8">Bu filtreye uygun doğrulama kaydı bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-border">
            {{ $documents->links() }}
        </div>
    </div>
</div>
@endsection
