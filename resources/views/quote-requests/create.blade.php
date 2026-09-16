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
<div class="by-container py-10">
    <div class="mx-auto max-w-[1200px]">
        <div class="by-card by-gradient-border p-6 md:p-8">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $eyebrow }}</p>
            <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">{{ $heading }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $sub }}</p>
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

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <div class="by-card p-6 md:p-8">
                <form method="POST" action="{{ route('quote-requests.store') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="request_type" value="{{ $type }}">
                @if($type === 'freelancer')
                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
                        {{ __('home.quote_form_freelancer_banner') }}
                        <div class="mt-2">
                            <a href="{{ route('freelancer-jobs.create') }}" class="font-semibold underline">{{ __('home.quote_prefer_job_listing') }}</a>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="show_customer_profile" value="1" class="form-check-input" id="show_profile" @checked(old('show_customer_profile'))>
                        <label for="show_profile" class="form-check-label text-sm">{{ __('home.quote_show_profile') }}</label>
                    </div>
                @elseif($type === 'tabela')
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
                        {{ __('home.quote_form_tabela_banner') }}
                    </div>
                @endif

                <div class="by-surface-indigo by-accent-bar">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-indigo-900">{{ __('home.quote_items') }}</p>
                            <p class="mt-1 text-sm text-slate-700">{{ __('home.quote_items_help') }}</p>
                        </div>
                        <button type="button" class="by-btn-primary" id="add-item" @if($categories->isEmpty()) disabled @endif>+ {{ __('home.quote_add_item') }}</button>
                    </div>

                    <div class="mt-4 space-y-4" id="items"></div>

                    <template id="item-template">
                        <div class="by-card p-5 bg-white border-indigo-200/60">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 mb-0">{{ __('home.quote_item') }} <span data-item-number></span></p>
                                </div>
                                <button type="button" class="by-btn-secondary px-4 py-2.5" data-remove>{{ __('home.quote_remove') }}</button>
                            </div>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-slate-600">{{ __('ui.category') }}</label>
                                    <select class="by-input mt-1" data-category required></select>
                                </div>
                                @if($type === 'physical_quote')
                                <div>
                                    <label class="text-xs font-semibold text-slate-600">{{ __('ui.products') }} ({{ __('home.optional') }})</label>
                                    <select class="by-input mt-1" data-product>
                                        <option value="">{{ __('home.select') }}</option>
                                    </select>
                                </div>
                                @else
                                <div class="hidden"><select data-product><option value=""></option></select></div>
                                @endif
                                <div class="md:col-span-2 grid gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600">{{ __('home.qty') }}</label>
                                        <input type="number" min="1" step="1" class="by-input mt-1" data-quantity>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600">{{ __('home.unit') }}</label>
                                        <input type="text" class="by-input mt-1" data-unit>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600">{{ __('home.dimension') }}</label>
                                        <input type="text" class="by-input mt-1" data-dimension>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs font-semibold text-slate-600">{{ __('home.spec') }}</label>
                                    <textarea class="by-input mt-1 min-h-[90px]" data-spec></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs font-semibold text-slate-600">{{ __('home.files') }}</label>
                                    <input type="file" class="by-input mt-1" data-files multiple>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="by-surface-amber by-accent-bar">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-amber-900">{{ __('home.description') }}</p>
                    <textarea name="description" class="by-input mt-1 min-h-[120px]" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="by-surface-cyan by-accent-bar">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-cyan-900">{{ __('home.delivery_contact') }}</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-xs font-semibold text-slate-600">{{ __('panel.city') }}</label>
                            <input type="text" name="city" class="by-input mt-1" value="{{ old('city') }}">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-600">{{ __('panel.district') }}</label>
                            <input type="text" name="district" class="by-input mt-1" value="{{ old('district') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-slate-600">{{ __('panel.address') }}</label>
                            <input type="text" name="address" class="by-input mt-1" value="{{ old('address') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-slate-600">{{ __('panel.phone') }}</label>
                            <input type="text" name="contact_phone" class="by-input mt-1" value="{{ old('contact_phone') }}">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <button type="submit" class="by-btn-cta" @if($categories->isEmpty()) disabled @endif>
                        {{ auth()->check() ? __('home.submit') : __('home.continue_auth') }}
                    </button>
                    <a href="{{ route('home') }}" class="by-btn-secondary">{{ __('panel.cancel') }}</a>
                </div>
                </form>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24 h-fit">
                <div class="by-card p-5 bg-white">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('home.summary') }}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-700 mb-0">{{ __('home.items_count') }}</p>
                        <p class="text-sm font-extrabold text-slate-900 mb-0" id="items-count">0</p>
                    </div>
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
