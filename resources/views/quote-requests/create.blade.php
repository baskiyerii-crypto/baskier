@extends('layouts.app')
@section('title')
    @php
        $type = $type ?? 'physical_quote';
        echo match ($type) {
            'freelancer' => __('home.path_freelancer_title'),
            'tabela' => __('home.path_tabela_title'),
            default => __('home.path_quote_title'),
        };
    @endphp
@endsection
@section('content')
@php
    $type = $type ?? 'physical_quote';
    $heading = match($type) {
        'freelancer' => __('home.path_freelancer_title'),
        'tabela' => __('home.path_tabela_title'),
        default => __('home.path_quote_title'),
    };
    $sub = match($type) {
        'freelancer' => __('home.quote_form_freelancer_help'),
        'tabela' => __('home.quote_form_tabela_help'),
        default => __('home.quote_form_print_help'),
    };
    $eyebrow = match($type) {
        'freelancer' => __('home.path_freelancer_eyebrow'),
        'tabela' => __('home.path_tabela_eyebrow'),
        default => __('home.path_quote_eyebrow'),
    };
@endphp
<div class="by-container py-8 md:py-12">
    <div class="mx-auto max-w-4xl">
        <div class="by-card p-6 md:p-8 bg-surface">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">{{ $eyebrow }}</p>
            <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="font-heading text-2xl md:text-3xl font-bold tracking-tight text-ink">{{ $heading }}</h1>
                    <p class="mt-2 text-sm text-muted">{{ $sub }}</p>
                </div>
            </div>
        </div>

        @if($categories->isEmpty())
            <div class="mt-4 by-card border-amber-200 bg-amber-50/80 p-5 text-sm text-amber-950">
                {{ __('home.quote_no_categories') }}
            </div>
        @endif

        @if(!auth()->check())
            <div class="mt-4 by-card border-sky-200 bg-sky-50/70 p-5 text-sm text-sky-900">
                {{ __('home.quote_auth_notice') }}
            </div>
        @endif

        @if(session('success'))
            <div class="mt-4 by-card border-emerald-200 bg-emerald-50/70 p-5 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="by-card p-6 md:p-8 bg-surface">
                <form method="POST" action="{{ route('quote-requests.store') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="request_type" value="{{ $type }}">
                @if($type === 'freelancer')
                    <div class="rounded-xl border border-border bg-canvas p-4 text-sm text-ink">
                        {{ __('home.quote_form_freelancer_banner') }}
                        <div class="mt-2">
                            <a href="{{ route('freelancer-jobs.create') }}" class="font-semibold text-cta underline">{{ __('home.quote_prefer_job_listing') }}</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="show_customer_profile" value="1" class="h-4 w-4 rounded border-border text-cta focus:ring-cta" id="show_profile" @checked(old('show_customer_profile'))>
                        <label for="show_profile" class="text-sm font-medium text-ink cursor-pointer">{{ __('home.quote_show_profile') }}</label>
                    </div>
                @elseif($type === 'tabela')
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
                        {{ __('home.quote_form_tabela_banner') }}
                    </div>
                @endif

                <div class="rounded-xl border border-border bg-canvas p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-ink">{{ __('home.quote_items') }}</p>
                            <p class="mt-0.5 text-xs text-muted">{{ __('home.quote_items_help') }}</p>
                        </div>
                        <button type="button" class="btn btn-secondary text-xs py-2 px-3" id="add-item" @if($categories->isEmpty()) disabled @endif>+ {{ __('home.quote_add_item') }}</button>
                    </div>

                    <div class="mt-4 space-y-4" id="items"></div>

                    <template id="item-template">
                        <div class="by-card p-5 bg-surface border-border">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-ink mb-0">{{ __('home.quote_item') }} <span data-item-number></span></p>
                                </div>
                                <button type="button" class="btn btn-secondary text-xs py-1.5 px-3 text-red-600 hover:bg-red-50 hover:border-red-200" data-remove>{{ __('home.quote_remove') }}</button>
                            </div>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-semibold text-muted mb-1">{{ __('ui.category') }}</label>
                                    <select class="form-control" data-category required></select>
                                </div>
                                @if($type === 'physical_quote')
                                <div>
                                    <label class="block text-xs font-semibold text-muted mb-1">{{ __('ui.products') }} ({{ __('home.optional') }})</label>
                                    <select class="form-control" data-product>
                                        <option value="">{{ __('home.select') }}</option>
                                    </select>
                                </div>
                                @else
                                <div class="hidden"><select data-product><option value=""></option></select></div>
                                @endif
                                <div class="md:col-span-2 grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-muted mb-1">{{ __('home.qty') }}</label>
                                        <input type="number" min="1" step="1" class="form-control" data-quantity>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-muted mb-1">{{ __('home.unit') }}</label>
                                        <input type="text" class="form-control" data-unit placeholder="adet, m², rulo...">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-muted mb-1">{{ __('home.dimension') }}</label>
                                        <input type="text" class="form-control" data-dimension placeholder="Örn: 50x70 cm">
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-muted mb-1">{{ __('home.spec') }}</label>
                                    <textarea class="form-control min-h-[80px]" data-spec placeholder="Kağıt gramajı, baskı yönü, laminasyon veya özel detaylar..."></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-muted mb-1">{{ __('home.files') }}</label>
                                    <input type="file" class="form-control" data-files multiple>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="rounded-xl border border-border bg-canvas p-5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-ink mb-1">{{ __('home.description') }}</label>
                    <textarea name="description" class="form-control min-h-[100px]" rows="4" placeholder="Talebinize dair genel açıklamalar ve özel istekler...">{{ old('description') }}</textarea>
                </div>

                <div class="rounded-xl border border-border bg-canvas p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink mb-3">{{ __('home.delivery_contact') }}</p>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.city') }}</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.district') }}</label>
                            <input type="text" name="district" class="form-control" value="{{ old('district') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.address') }}</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-muted mb-1">{{ __('panel.phone') }}</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone') }}">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pt-2">
                    <button type="submit" class="btn btn-cta" @if($categories->isEmpty()) disabled @endif>
                        {{ auth()->check() ? __('home.submit') : __('home.continue_auth') }}
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-secondary">{{ __('panel.cancel') }}</a>
                </div>
                </form>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24 h-fit">
                <div class="by-card p-5 bg-surface">
                    <p class="text-xs font-bold uppercase tracking-wider text-muted">{{ __('home.summary') }}</p>
                    <div class="mt-3 flex items-center justify-between border-t border-border pt-3">
                        <p class="text-sm font-semibold text-ink mb-0">{{ __('home.items_count') }}</p>
                        <p class="text-base font-bold text-ink mb-0" id="items-count">0</p>
                    </div>
                    <div class="mt-4 p-3 rounded-lg bg-canvas text-xs text-muted leading-relaxed">
                        Talebiniz uzman üreticilere iletilecek ve gelen teklifler arasından en uygununu seçebileceksiniz.
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
    (function () {
        const categories = @json($categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->localizedName()])->values());
        const products = @json(($products ?? collect())->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'category_id' => $p->category_id])->values());
        const productsByCategory = products.reduce((acc, p) => {
            (acc[p.category_id] ||= []).push(p);
            return acc;
        }, {});
        const selectLabel = @json(__('home.select'));

        const itemsEl = document.getElementById('items');
        const addBtn = document.getElementById('add-item');
        const tpl = document.getElementById('item-template');
        if (!itemsEl || !tpl || categories.length === 0) return;

        let idx = 0;

        const rebuildNumbers = () => {
            Array.from(itemsEl.children).forEach((row, i) => {
                const n = row.querySelector('[data-item-number]');
                if (n) n.textContent = (i + 1);
            });
        };

        const updateSummary = () => {
            const countEl = document.getElementById('items-count');
            if (countEl) countEl.textContent = String(itemsEl.children.length);
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
            if (!select) return;
            select.innerHTML = '';
            const empty = document.createElement('option');
            empty.value = '';
            empty.textContent = selectLabel;
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
            if (productSel) productSel.name = `items[${idx}][product_id]`;
            qtyInput.name = `items[${idx}][quantity]`;
            unitInput.name = `items[${idx}][unit]`;
            specInput.name = `items[${idx}][spec]`;
            filesInput.name = `items[${idx}][files][]`;

            categorySel.addEventListener('change', () => fillProducts(productSel, categorySel.value));
            dimInput?.addEventListener('input', () => {
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
        addItem();
    })();
</script>
@endsection
