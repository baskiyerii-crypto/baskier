@extends('layouts.account')

@section('title', 'İş hesaplama')

@section('content')
    <nav class="mb-3"><a href="{{ route('customer.dashboard') }}" class="small text-muted text-decoration-none">← Hesap özeti</a></nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">İş hesaplama</h1>
            <p class="small text-muted mb-0">
                Kelime veya kategori ile ürün arayın; kartvizit, broşür, branda gibi kalemleri <strong>listeye ekleyip adet</strong> verin.
                Her kalem için geçmiş verilerden tahmini aralık hesaplanır; <strong>toplam</strong> aşağıda gösterilir. Kesin fiyat sipariş veya teklifle netleşir.
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="rounded-4 border-0 shadow-sm p-4 bg-white">
                <h2 class="h6 text-uppercase text-muted fw-semibold mb-3" style="letter-spacing:.06em;">Ürün ara &amp; ekle</h2>
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <label class="form-label small fw-medium mb-1">Kelime</label>
                        <input type="text" id="pe-q" class="form-control rounded-3" placeholder="ör. kartvizit, broşür, branda…" autocomplete="off">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-medium mb-1">Kategori</label>
                        <select id="pe-cat" class="form-select rounded-3">
                            <option value="">Tüm kategoriler</option>
                            @foreach($categoryOptions as $opt)
                                <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="button" id="pe-search" class="btn btn-warning rounded-pill fw-semibold px-4">Ara</button>
                        <button type="button" id="pe-more" class="btn btn-outline-secondary rounded-pill d-none">Daha fazla</button>
                    </div>
                </div>
                <p id="pe-search-err" class="small text-danger mb-2 d-none"></p>
                <div id="pe-results" class="list-group list-group-flush rounded-3 border" style="max-height: 420px; overflow-y: auto;"></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rounded-4 border-0 shadow-sm p-4 bg-white mb-3">
                <h2 class="h6 text-uppercase text-muted fw-semibold mb-3" style="letter-spacing:.06em;">İş kalemleri</h2>
                <p class="small text-muted mb-2">Aynı ürünü tekrar eklerseniz adetler birleşir.</p>
                <div id="pe-cart-empty" class="text-muted small py-4 text-center border rounded-3 bg-light">Henüz kalem yok. Soldan ürün ekleyin.</div>
                <div id="pe-cart" class="d-none"></div>
                <button type="button" id="pe-calc" class="btn btn-dark rounded-pill fw-semibold mt-3 w-100 d-none">Toplam tahmini hesapla</button>
            </div>
            <div id="pe-out-wrap" class="rounded-4 border-0 shadow-sm p-4 bg-white border-start border-4 border-warning d-none">
                <h2 class="h6 text-uppercase text-muted fw-semibold mb-3" style="letter-spacing:.06em;">Sonuç</h2>
                <p id="pe-out-err" class="small text-danger d-none"></p>
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
            let html = '<table class="table table-sm align-middle mb-0"><thead><tr><th>Ürün</th><th style="width:110px">Adet</th><th></th></tr></thead><tbody>';
            cart.forEach(function (qty, pid) {
                const meta = cartMeta.get(pid) || {};
                const name = meta.name ? meta.name : ('#' + pid);
                html += '<tr data-pid="' + pid + '"><td><div class="fw-medium small">' + escapeHtml(name) + '</div><div class="text-muted" style="font-size:.75rem">' + escapeHtml(meta.sub || '') + '</div></td>';
                html += '<td><input type="number" min="1" max="99999" class="form-control form-control-sm pe-qty" value="' + qty + '"></td>';
                html += '<td><button type="button" class="btn btn-link btn-sm text-danger p-0 pe-remove">Kaldır</button></td></tr>';
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
            if (reset) { page = 1; box.innerHTML = '<div class="p-3 text-muted small">Yükleniyor…</div>'; }
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
                    box.innerHTML = '<div class="p-3 text-muted small">Sonuç yok; kelimeyi değiştirin veya kategoriyi daraltın.</div>';
                } else {
                    rows.forEach(function (p) {
                        const row = document.createElement('div');
                        row.className = 'list-group-item d-flex justify-content-between align-items-start gap-2';
                        row.innerHTML = '<div><div class="fw-medium">' + escapeHtml(p.name) + '</div><div class="small text-muted">' + escapeHtml((p.category && p.category.name) || '') + '</div></div><button type="button" class="btn btn-sm btn-outline-warning rounded-pill shrink-0">Ekle</button>';
                        row.querySelector('button').addEventListener('click', function () { addProduct(p); });
                        box.appendChild(row);
                    });
                }
                more.classList.toggle('d-none', page >= lastPage);
            } catch (e) {
                err.textContent = e.message || 'Hata';
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
            outBody.innerHTML = '<p class="small text-muted mb-0">Hesaplanıyor…</p>';
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
                    html += '<div class="alert alert-info rounded-3 border-0 small">Bazı kalemler teklif esasındadır; bunlar toplama dahil edilmez. Teklif için <a href="{{ route('quote-requests.create') }}" class="alert-link fw-semibold">teklif talebi</a> oluşturabilirsiniz.</div>';
                }
                html += '<p class="small text-muted mb-1">Tahmini toplam aralık</p>';
                html += '<p class="fs-3 fw-bold text-warning mb-2">' + money(d.totals.estimated_min) + ' — ' + money(d.totals.estimated_max) + '</p>';
                html += '<p class="small mb-2">Genel güven: <span class="badge rounded-pill bg-secondary-subtle text-dark">' + confTr(d.overall_confidence) + '</span></p>';
                html += '<p class="small text-muted mb-3">' + escapeHtml(d.disclaimer) + '</p>';
                html += '<h3 class="h6 mb-2">Kalem detayı</h3><ul class="list-unstyled small mb-0">';
                (d.lines || []).forEach(function (ln) {
                    html += '<li class="mb-2 pb-2 border-bottom">';
                    html += '<strong>' + escapeHtml(ln.name) + '</strong> × ' + ln.quantity;
                    if (ln.is_quote_only) {
                        html += ' <span class="text-muted">(teklif)</span>';
                    } else {
                        html += '<br><span class="text-muted">' + money(ln.estimated_min) + ' — ' + money(ln.estimated_max) + ' · ' + confTr(ln.confidence) + '</span>';
                    }
                    html += '</li>';
                });
                html += '</ul>';
                outBody.innerHTML = html;
            } catch (e) {
                outErr.textContent = e.message || 'Hata';
                outErr.classList.remove('d-none');
                outBody.innerHTML = '';
            }
        });

        renderCart();
    })();
    </script>
    @endpush
@endsection
