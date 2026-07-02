@extends('layouts.app')
@section('title', 'Teklif Talebi Oluştur')
@section('content')
<div class="by-container py-10">
    <div class="mx-auto max-w-[1200px]">
        <div class="by-card by-gradient-border p-6 md:p-8">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Özel iş / RFQ</p>
            <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">Teklif al</h1>
                    <p class="mt-2 text-sm text-slate-600">Kalem kalem yaz, satıcılar net teklif versin. Dosya ekle, ölçüyü belirt, işi hızlandır.</p>
                </div>
                <div class="hidden md:flex items-center gap-2">
                    <span class="by-badge border-indigo-200 bg-indigo-50 text-indigo-900">1) Kalem</span>
                    <span class="text-slate-400">→</span>
                    <span class="by-badge">2) Adres</span>
                    <span class="text-slate-400">→</span>
                    <span class="by-badge">3) Gönder</span>
                </div>
            </div>
        </div>

        @if(!auth()->check())
            <div class="by-card border-sky-200 bg-sky-50/70 p-5 text-sm text-sky-900">
                Formu doldurup gönderdiğinizde, talebinizi yayınlayabilmemiz için <strong>üye olmanız</strong> veya <strong>giriş yapmanız</strong> istenecek. Bilgileriniz kaydedilir.
            </div>
        @endif

        @if(session('success'))
            <div class="mt-4 by-card border-emerald-200 bg-emerald-50/70 p-5 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr,360px]">
            <div class="by-card p-6 md:p-8">
                <form method="POST" action="{{ route('quote-requests.store') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf

                <div class="by-surface-indigo by-accent-bar">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-indigo-900">İş kalemleri</p>
                            <p class="mt-1 text-sm text-slate-700">Her satır bir iş kalemi. “+ Kalem ekle” ile çoğaltın. Her kaleme dosya ekleyebilirsiniz.</p>
                        </div>
                        <button type="button" class="by-btn-primary" id="add-item">+ Kalem ekle</button>
                    </div>

                    <div class="mt-4 space-y-4" id="items"></div>

                    <template id="item-template">
                        <div class="by-card p-5 bg-white border-indigo-200/60">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 mb-0">Kalem <span data-item-number></span></p>
                                    <p class="mt-1 text-xs text-slate-500 mb-0">Kategori + (opsiyonel) ürün + adet/ölçü + dosya</p>
                                </div>
                                <button type="button" class="by-btn-secondary px-4 py-2.5" data-remove>Sil</button>
                            </div>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-slate-600">Kategori</label>
                                    <select class="by-input mt-1" data-category required></select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-600">Ürün (opsiyonel)</label>
                                    <select class="by-input mt-1" data-product>
                                        <option value="">Seçiniz</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2 grid gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600">Adet</label>
                                        <input type="number" min="1" step="1" class="by-input mt-1" data-quantity placeholder="Örn: 100">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600">Birim</label>
                                        <input type="text" class="by-input mt-1" data-unit placeholder="adet / m2 / cm ...">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600">Ölçü (opsiyonel)</label>
                                        <input type="text" class="by-input mt-1" data-dimension placeholder="Örn: 3x2">
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs font-semibold text-slate-600">Detay / Özellik</label>
                                    <textarea class="by-input mt-1 min-h-[90px]" data-spec placeholder="Örn: Kompozit, kutu harf, renk, gramaj, teslim notu..."></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs font-semibold text-slate-600">Dosyalar (opsiyonel)</label>
                                    <input type="file" class="by-input mt-1" data-files multiple>
                                    <p class="mt-2 text-xs text-slate-500">PDF / görsel / zip ekleyebilirsiniz. (Kalem başı 10 dosyaya kadar)</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="by-surface-amber by-accent-bar">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-amber-900">Talep notu</p>
                    <label class="text-xs font-semibold text-slate-600">Açıklama</label>
                    <textarea name="description" class="by-input mt-1 min-h-[120px]" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="by-surface-cyan by-accent-bar">
                    <div class="flex items-end justify-between gap-3 flex-wrap">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-cyan-900">Teslimat & İletişim</p>
                            <p class="mt-1 text-sm text-slate-700">Satıcılar bölge/teslim bilgisine göre daha doğru teklif verir.</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold text-slate-600">İl</label>
                            <input type="text" name="city" class="by-input mt-1" value="{{ old('city') }}">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-600">İlçe</label>
                            <input type="text" name="district" class="by-input mt-1" value="{{ old('district') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-slate-600">Adres</label>
                            <input type="text" name="address" class="by-input mt-1" value="{{ old('address') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-slate-600">İletişim telefonu</label>
                            <input type="text" name="contact_phone" class="by-input mt-1" value="{{ old('contact_phone') }}">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <button type="submit" class="by-btn-cta">
                        {{ auth()->check() ? 'Gönder' : 'Devam et (üye ol / giriş yap)' }}
                    </button>
                    @auth
                        <a href="{{ route('quote-requests.index') }}" class="by-btn-secondary">İptal</a>
                    @else
                        <a href="{{ route('home') }}" class="by-btn-secondary">İptal</a>
                    @endauth
                </div>
                </form>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24 h-fit">
                <div class="by-card p-5 bg-white">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Özet</p>
                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-700 mb-0">Kalem sayısı</p>
                        <p class="text-sm font-extrabold text-slate-900 mb-0" id="items-count">1</p>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-700 mb-0">Dosya sayısı</p>
                        <p class="text-sm font-extrabold text-slate-900 mb-0" id="files-count">0</p>
                    </div>
                    <div class="mt-4 h-px bg-slate-200/70"></div>
                    <p class="mt-4 text-sm text-slate-600">
                        En iyi sonuç için: <span class="font-semibold text-slate-900">ölçü</span>, <span class="font-semibold text-slate-900">malzeme</span>, <span class="font-semibold text-slate-900">teslim tarihi</span> yaz.
                    </p>
                </div>
                <div class="by-card p-5 bg-white">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Örnek</p>
                    <p class="mt-2 text-sm text-slate-600">
                        Kartvizit 100 adet + Broşür 200 adet + Tabela 3x2 kompozit kutu harf.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
    (function () {
        const categories = @json($categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values());
        const products = @json(($products ?? collect())->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'category_id' => $p->category_id])->values());
        const productsByCategory = products.reduce((acc, p) => {
            (acc[p.category_id] ||= []).push(p);
            return acc;
        }, {});

        const itemsEl = document.getElementById('items');
        const addBtn = document.getElementById('add-item');
        const tpl = document.getElementById('item-template');

        let idx = 0;

        const rebuildNumbers = () => {
            Array.from(itemsEl.children).forEach((row, i) => {
                const n = row.querySelector('[data-item-number]');
                if (n) n.textContent = (i + 1);
            });
        };

        const updateSummary = () => {
            const itemsCount = itemsEl.children.length;
            const countEl = document.getElementById('items-count');
            if (countEl) countEl.textContent = String(itemsCount);

            let fileCount = 0;
            Array.from(itemsEl.querySelectorAll('input[type="file"]')).forEach((inp) => {
                fileCount += (inp.files ? inp.files.length : 0);
            });
            const fileEl = document.getElementById('files-count');
            if (fileEl) fileEl.textContent = String(fileCount);
        };

        const fillCategories = (select) => {
            select.innerHTML = '';
            for (const c of categories) {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                select.appendChild(opt);
            }
        };

        const fillProducts = (select, categoryId) => {
            select.innerHTML = '';
            const empty = document.createElement('option');
            empty.value = '';
            empty.textContent = 'Seçiniz';
            select.appendChild(empty);

            const list = productsByCategory[Number(categoryId)] || [];
            for (const p of list) {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                select.appendChild(opt);
            }
        };

        const addItem = () => {
            const frag = tpl.content.cloneNode(true);
            const root = frag.firstElementChild;

            const categorySel = root.querySelector('[data-category]');
            const productSel = root.querySelector('[data-product]');
            const qtyInput = root.querySelector('[data-quantity]');
            const unitInput = root.querySelector('[data-unit]');
            const dimInput = root.querySelector('[data-dimension]');
            const specInput = root.querySelector('[data-spec]');
            const filesInput = root.querySelector('[data-files]');

            fillCategories(categorySel);
            fillProducts(productSel, categorySel.value);

            categorySel.name = `items[${idx}][category_id]`;
            productSel.name = `items[${idx}][product_id]`;
            qtyInput.name = `items[${idx}][quantity]`;
            unitInput.name = `items[${idx}][unit]`;
            specInput.name = `items[${idx}][spec]`;
            filesInput.name = `items[${idx}][files][]`;

            categorySel.addEventListener('change', () => fillProducts(productSel, categorySel.value));
            dimInput.addEventListener('input', () => {
                const base = (specInput.value || '').replace(/\n?\s*Ölçü:\s*.*$/m, '').trim();
                const dim = dimInput.value.trim();
                specInput.value = dim ? `${base}${base ? '\n' : ''}Ölçü: ${dim}` : base;
            });

            root.querySelector('[data-remove]').addEventListener('click', () => {
                root.remove();
                rebuildNumbers();
                updateSummary();
            });

            itemsEl.appendChild(root);
            idx += 1;
            rebuildNumbers();
            updateSummary();
        };

        addBtn?.addEventListener('click', addItem);

        // Always start with one item.
        addItem();
    })();
</script>
@endsection
