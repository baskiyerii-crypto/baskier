@extends('layouts.account')

@section('title', 'Baskı İşi Fiyat Hesaplama - BaskıYeri')

@section('content')
    <div class="mb-5">
        <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Hesap Özetine Dön
        </a>
    </div>

    <div class="mb-6">
        <h1 class="font-heading text-2xl font-bold tracking-tight text-ink">Baskı İşi Fiyat Hesaplama</h1>
        <p class="text-xs text-muted mt-1 max-w-2xl leading-relaxed">
            Kelime veya kategori ile ürün arayın; kartvizit, broşür, branda gibi kalemleri listeye ekleyip adet verin. Her kalem için geçmiş verilerden tahmini aralık hesaplanır ve anlık toplam sunulur.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <div class="by-card p-6 bg-surface border border-border">
                <h2 class="text-xs font-bold uppercase tracking-wider text-muted mb-4">Ürün Ara &amp; Ekle</h2>
                <div class="space-y-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Arama Kelimesi</label>
                        <input type="text" id="pe-q" class="form-control text-xs" placeholder="Örn: kartvizit, broşür, branda..." autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1">Kategori</label>
                        <select id="pe-cat" class="form-control text-xs">
                            <option value="">Tüm Kategoriler</option>
                            @foreach($categoryOptions as $opt)
                                <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2 pt-1">
                        <button type="button" id="pe-search" class="btn btn-cta text-xs py-2 px-4">Ara</button>
                        <button type="button" id="pe-more" class="btn btn-secondary text-xs py-2 px-4 d-none">Daha Fazla</button>
                    </div>
                </div>

                <p id="pe-search-err" class="text-xs text-red-600 mb-2 d-none"></p>
                <div id="pe-results" class="divide-y divide-border rounded-xl border border-border overflow-y-auto max-h-[380px] bg-canvas/30"></div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="by-card p-6 bg-surface border border-border">
                <h2 class="text-xs font-bold uppercase tracking-wider text-muted mb-1">Hesaplanacak Kalemler</h2>
                <p class="text-[11px] text-muted mb-4">Aynı ürünü tekrar eklerseniz adetler birleşir.</p>

                <div id="pe-cart-empty" class="text-muted text-xs py-8 text-center border border-dashed border-border rounded-xl bg-canvas/40">
                    Henüz kalem eklenmedi. Sol panelden ürün arayıp ekleyin.
                </div>
                
                <div id="pe-cart" class="d-none overflow-x-auto"></div>
                <button type="button" id="pe-calc" class="btn btn-cta w-full text-xs py-2.5 mt-4 d-none">
                    Toplam Tahmini Hesapla →
                </button>
            </div>

            <div id="pe-out-wrap" class="by-card p-6 bg-surface border border-cta/40 d-none">
                <h2 class="text-xs font-bold uppercase tracking-wider text-cta mb-3">Hesaplama Sonucu</h2>
                <p id="pe-out-err" class="text-xs text-red-600 d-none mb-2"></p>
                <div id="pe-out-body"></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const API = @json($apiBase);
        const cart = new Map();
        const cartMeta = new Map();
        let page = 1;
        let lastPage = 1;
        let lastParams = '';

        function money(n) {
            return '₺' + Number(n).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function confTr(c) {
            return ({ high: 'Yüksek', medium: 'Orta', low: 'Düşük', none: '—' })[c] || c;
        }

        function cartLines() {
            const lines = [];
            cart.forEach(function (qty, id) {
                lines.push({ product_id: id, quantity: qty });
            });
            return lines;
        }

        function renderCart() {
            const wrap = document.getElementById('pe-cart');
            const empty = document.getElementById('pe-cart-empty');
            const btn = document.getElementById('pe-calc');
            if (cart.size === 0) {
                wrap.classList.add('d-none');
                empty.classList.remove('d-none');
                btn.classList.add('d-none');
                return;
            }
            empty.classList.add('d-none');
            wrap.classList.remove('d-none');
            btn.classList.remove('d-none');
            let html = '<table class="w-full text-left text-xs"><thead><tr class="border-b border-border text-muted pb-2"><th class="pb-2">Ürün</th><th class="pb-2 w-28">Adet</th><th class="pb-2 text-right"></th></tr></thead><tbody class="divide-y divide-border">';
            cart.forEach(function (qty, pid) {
                const meta = cartMeta.get(pid) || {};
                const name = meta.name ? meta.name : ('#' + pid);
                html += '<tr data-pid="' + pid + '"><td class="py-2.5"><div class="font-medium text-ink">' + escapeHtml(name) + '</div><div class="text-muted text-[11px]">' + escapeHtml(meta.sub || '') + '</div></td>';
                html += '<td class="py-2.5"><input type="number" min="1" max="99999" class="form-control text-xs py-1 px-2 w-24 pe-qty" value="' + qty + '"></td>';
                html += '<td class="py-2.5 text-right"><button type="button" class="text-red-600 hover:text-red-700 text-xs pe-remove">Kaldır</button></td></tr>';
            });
            html += '</tbody></table>';
            wrap.innerHTML = html;
            wrap.querySelectorAll('.pe-qty').forEach(function (inp) {
                const tr = inp.closest('tr');
                const pid = parseInt(tr.getAttribute('data-pid'), 10);
                inp.addEventListener('change', function () {
                    let v = parseInt(inp.value, 10);
                    if (!v || v < 1) v = 1;
                    if (v > 99999) v = 99999;
                    inp.value = v;
                    cart.set(pid, v);
                });
            });
            wrap.querySelectorAll('.pe-remove').forEach(function (b) {
                b.addEventListener('click', function () {
                    const tr = b.closest('tr');
                    const pid = parseInt(tr.getAttribute('data-pid'), 10);
                    cart.delete(pid);
                    cartMeta.delete(pid);
                    renderCart();
                });
            });
        }

        function escapeHtml(s) {
            if (!s) return '';
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function addProduct(p) {
            const id = p.id;
            const prev = cart.get(id) || 0;
            cart.set(id, prev + 1);
            const sub = (p.vendor && p.vendor.name ? p.vendor.name + ' · ' : '') + (p.category && p.category.name ? p.category.name : '');
            cartMeta.set(id, { name: p.name, sub: sub });
            renderCart();
        }

        async function search(reset) {
            const err = document.getElementById('pe-search-err');
            const box = document.getElementById('pe-results');
            const more = document.getElementById('pe-more');
            err.classList.add('d-none');
            if (reset) { page = 1; box.innerHTML = '<div class="p-3 text-muted text-xs">Yükleniyor…</div>'; }
            const q = document.getElementById('pe-q').value.trim();
            const cat = document.getElementById('pe-cat').value;
            const params = new URLSearchParams();
            if (q) params.set('q', q);
            if (cat) params.set('category_id', cat);
            params.set('page', String(page));
            lastParams = params.toString();
            try {
                const res = await fetch(API + '/products?' + lastParams, { headers: { Accept: 'application/json' } });
                const json = await res.json();
                if (!json.success) throw new Error(json.message || 'Liste alınamadı');
                const rows = json.data || [];
                const meta = json.meta || {};
                lastPage = meta.last_page || 1;
                if (reset) box.innerHTML = '';
                if (rows.length === 0 && page === 1) {
                    box.innerHTML = '<div class="p-3 text-muted text-xs">Sonuç bulunamadı; kelimeyi değiştirin veya filtreyi daraltın.</div>';
                } else {
                    rows.forEach(function (p) {
                        const row = document.createElement('div');
                        row.className = 'p-3 flex justify-between items-center gap-2 hover:bg-canvas/50 transition-colors';
                        row.innerHTML = '<div><div class="font-medium text-xs text-ink">' + escapeHtml(p.name) + '</div><div class="text-[11px] text-muted">' + escapeHtml((p.category && p.category.name) || '') + '</div></div><button type="button" class="btn btn-secondary text-xs py-1 px-3 shrink-0">+ Ekle</button>';
                        row.querySelector('button').addEventListener('click', function () { addProduct(p); });
                        box.appendChild(row);
                    });
                }
                more.classList.toggle('d-none', page >= lastPage);
            } catch (e) {
                err.textContent = e.message || 'Hata oluştu';
                err.classList.remove('d-none');
                if (reset) box.innerHTML = '';
            }
        }

        document.getElementById('pe-search').addEventListener('click', function () { search(true); });
        document.getElementById('pe-more').addEventListener('click', function () {
            if (page < lastPage) { page++; search(false); }
        });

        document.getElementById('pe-calc').addEventListener('click', async function () {
            const outWrap = document.getElementById('pe-out-wrap');
            const outBody = document.getElementById('pe-out-body');
            const outErr = document.getElementById('pe-out-err');
            outErr.classList.add('d-none');
            outBody.innerHTML = '<p class="text-xs text-muted mb-0">Hesaplanıyor…</p>';
            outWrap.classList.remove('d-none');
            const lines = cartLines();
            try {
                const res = await fetch(API + '/price-estimate/bundle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({ lines }),
                });
                const json = await res.json();
                if (!json.success) throw new Error(json.message || 'Hesaplanamadı');
                const d = json.data;
                let html = '';
                if (d.has_quote_only_lines) {
                    html += '<div class="p-3 mb-3 rounded-lg bg-sky-50 border border-sky-200 text-xs text-sky-900">Bazı kalemler teklif esasındadır; bunlar toplama dahil edilmez. Teklif için <a href="{{ route('quote-requests.create') }}" class="font-bold underline">teklif talebi</a> oluşturabilirsiniz.</div>';
                }
                html += '<p class="text-xs text-muted mb-1">Tahmini Toplam Aralık</p>';
                html += '<p class="text-2xl font-extrabold text-cta mb-2">' + money(d.totals.estimated_min) + ' — ' + money(d.totals.estimated_max) + '</p>';
                html += '<p class="text-xs text-ink mb-2">Genel Güven Derecesi: <span class="font-bold">' + confTr(d.overall_confidence) + '</span></p>';
                html += '<p class="text-[11px] text-muted mb-3 leading-relaxed">' + escapeHtml(d.disclaimer) + '</p>';
                html += '<h3 class="text-xs font-bold uppercase tracking-wider text-ink mb-2">Kalem Detayı</h3><ul class="divide-y divide-border text-xs mb-0">';
                (d.lines || []).forEach(function (ln) {
                    html += '<li class="py-2">';
                    html += '<div class="font-semibold text-ink">' + escapeHtml(ln.name) + ' × ' + ln.quantity + '</div>';
                    if (ln.is_quote_only) {
                        html += ' <span class="text-muted text-[11px]">(Özel Teklif)</span>';
                    } else {
                        html += '<div class="text-muted text-[11px]">' + money(ln.estimated_min) + ' — ' + money(ln.estimated_max) + ' · ' + confTr(ln.confidence) + '</div>';
                    }
                    html += '</li>';
                });
                html += '</ul>';
                outBody.innerHTML = html;
            } catch (e) {
                outErr.textContent = e.message || 'Hata oluştu';
                outErr.classList.remove('d-none');
                outBody.innerHTML = '';
            }
        });

        renderCart();
    })();
    </script>
    @endpush
@endsection
