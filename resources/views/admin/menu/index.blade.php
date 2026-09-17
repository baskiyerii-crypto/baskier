@extends('layouts.admin')
@section('title', __('panel.nav_menu'))
@section('content')
<style>
    .menu-builder { display: grid; gap: 1.25rem; }
    @media (min-width: 992px) {
        .menu-builder { grid-template-columns: 260px 1fr; }
    }
    .menu-col {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        padding: 1rem;
        min-height: 280px;
    }
    .menu-col h2 { font-size: .95rem; font-weight: 700; margin: 0 0 .75rem; }
    .menu-list { list-style: none; margin: 0; padding: 0; min-height: 48px; }
    .menu-item {
        display: grid;
        gap: .5rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: .75rem;
        margin-bottom: .5rem;
        cursor: grab;
    }
    .menu-item.sortable-ghost { opacity: .45; }
    .menu-item-top {
        display: flex; align-items: center; gap: .5rem; justify-content: space-between;
    }
    .menu-handle { color: #94a3b8; font-size: 1.1rem; user-select: none; }
    .menu-item .form-control, .menu-item .form-select { font-size: .875rem; }
    .menu-item-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
    .menu-actions { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: 1rem; }
    .type-hint { font-size: .75rem; color: #64748b; }
    .menu-children { list-style: none; margin: .5rem 0 0; padding: .5rem 0 0 .75rem; border-left: 2px solid #e2e8f0; }
    .menu-child { background: #fff; border: 1px dashed #cbd5e1; border-radius: 8px; padding: .5rem; margin-bottom: .4rem; }
    .menu-source-btn { display: block; width: 100%; text-align: left; margin-bottom: .35rem; }
</style>

<div style="max-width:1200px;">
    <p class="text-muted small mb-3">Soldan kaynak ekleyin, sağda üst/yan menü ağacını sürükleyerek sıralayın. Alt öğe ekleyerek akordeon / açılır menü oluşturun.</p>

    <form method="post" action="{{ route('admin.menu.update') }}" id="menu-form">
        @csrf
        <div class="menu-builder">
            <div class="menu-col">
                <h2>Eklenebilir kaynaklar</h2>
                <button type="button" class="btn btn-sm btn-outline-secondary menu-source-btn" data-source="route" data-target="home" data-label="Anasayfa">Sayfa: Anasayfa</button>
                <button type="button" class="btn btn-sm btn-outline-secondary menu-source-btn" data-source="products" data-label="Ürünler">Ürünler listesi</button>
                <button type="button" class="btn btn-sm btn-outline-secondary menu-source-btn" data-source="url" data-label="Özel link">Özel URL</button>
                <button type="button" class="btn btn-sm btn-outline-secondary menu-source-btn" data-source="categories_accordion" data-label="Kategoriler">Kategori akordeonu</button>
                <hr>
                <p class="small fw-semibold mb-1">Kategoriler</p>
                @foreach($categories as $cat)
                    <button type="button" class="btn btn-sm btn-outline-primary menu-source-btn" data-source="category" data-target="{{ $cat->id }}" data-label="{{ $cat->name }}">{{ $cat->name }}</button>
                @endforeach
                <hr>
                <p class="small fw-semibold mb-1">Ürünler</p>
                @foreach($products as $p)
                    <button type="button" class="btn btn-sm btn-outline-success menu-source-btn" data-source="product" data-target="{{ $p->id }}" data-label="{{ $p->name }}">{{ $p->name }}</button>
                @endforeach
            </div>
            <div>
                <div class="menu-builder" style="grid-template-columns:1fr 1fr;">
                    <div class="menu-col">
                        <h2>Üst menü (desktop)</h2>
                        <ul class="menu-list" id="list-top" data-placement="top"></ul>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add="top">+ Öğe ekle</button>
                    </div>
                    <div class="menu-col">
                        <h2>Yan menü (drawer)</h2>
                        <ul class="menu-list" id="list-drawer" data-placement="drawer"></ul>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add="drawer">+ Öğe ekle</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="menu-actions">
            <button type="submit" class="btn btn-primary">{{ __('panel.save') }}</button>
            <button type="button" class="btn btn-outline-secondary" id="btn-reset-defaults">Varsayılana dön</button>
        </div>
    </form>

    <form method="post" action="{{ route('admin.menu.reset') }}" id="reset-form" class="d-none">
        @csrf
    </form>
</div>

<template id="menu-item-tpl">
    <li class="menu-item">
        <div class="menu-item-top">
            <span class="menu-handle" title="Sürükle">☰</span>
            <label class="form-check form-switch m-0 small">
                <input type="checkbox" class="form-check-input mi-active" checked>
                <span>Aktif</span>
            </label>
            <button type="button" class="btn btn-sm btn-outline-danger mi-remove">Sil</button>
        </div>
        <input type="text" class="form-control mi-label" placeholder="Etiket (TR)" maxlength="80" required>
        <input type="text" class="form-control mi-label-en" placeholder="Label (EN)" maxlength="80">
        <div class="menu-item-grid">
            <div>
                <select class="form-select mi-type">
                    <option value="route">Sayfa (route)</option>
                    <option value="url">Özel URL</option>
                    <option value="category">Kategori</option>
                    <option value="product">Ürün</option>
                    <option value="products">Ürünler listesi</option>
                    <option value="page">Sayfa slug</option>
                    <option value="categories_accordion">Kategori akordeonu</option>
                </select>
            </div>
            <div class="mi-target-wrap">
                <select class="form-select mi-target-route">
                    @foreach($routeOptions as $opt)
                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                    @endforeach
                </select>
                <select class="form-select mi-target-category d-none">
                    <option value="">Kategori seç</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select class="form-select mi-target-product d-none">
                    <option value="">Ürün seç</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <input type="text" class="form-control mi-target-url d-none" placeholder="https://…">
                <input type="text" class="form-control mi-target-page d-none" placeholder="slug (örn. contact)">
                <span class="type-hint mi-target-none d-none">Hedef gerekmez</span>
            </div>
        </div>
        <input type="hidden" class="mi-placement" value="top">
        <ul class="menu-children"></ul>
        <button type="button" class="btn btn-sm btn-outline-secondary mi-add-child">+ Alt öğe</button>
    </li>
</template>

<template id="menu-child-tpl">
    <li class="menu-child">
        <div class="d-flex gap-2 mb-1">
            <input type="text" class="form-control form-control-sm mc-label" placeholder="Alt etiket" maxlength="80">
            <button type="button" class="btn btn-sm btn-outline-danger mc-remove">×</button>
        </div>
        <div class="menu-item-grid">
            <select class="form-select form-select-sm mc-type">
                <option value="route">Sayfa</option>
                <option value="url">URL</option>
                <option value="category">Kategori</option>
                <option value="product">Ürün</option>
                <option value="page">Slug</option>
            </select>
            <input type="text" class="form-control form-control-sm mc-target" placeholder="Hedef (id / url / slug / route)">
        </div>
    </li>
</template>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    var initial = @json($items);
    var tpl = document.getElementById('menu-item-tpl');
    var childTpl = document.getElementById('menu-child-tpl');
    var listTop = document.getElementById('list-top');
    var listDrawer = document.getElementById('list-drawer');
    var lastPlacement = 'top';

    function syncTargetVisibility(li) {
        var type = li.querySelector('.mi-type').value;
        var route = li.querySelector('.mi-target-route');
        var cat = li.querySelector('.mi-target-category');
        var product = li.querySelector('.mi-target-product');
        var url = li.querySelector('.mi-target-url');
        var page = li.querySelector('.mi-target-page');
        var none = li.querySelector('.mi-target-none');
        [route, cat, product, url, page, none].forEach(function (el) { el.classList.add('d-none'); });
        if (type === 'route') route.classList.remove('d-none');
        else if (type === 'category') cat.classList.remove('d-none');
        else if (type === 'product') product.classList.remove('d-none');
        else if (type === 'url') url.classList.remove('d-none');
        else if (type === 'page') page.classList.remove('d-none');
        else none.classList.remove('d-none');
    }

    function addChild(li, data) {
        var node = childTpl.content.firstElementChild.cloneNode(true);
        data = data || {};
        node.querySelector('.mc-label').value = data.label || '';
        node.querySelector('.mc-type').value = data.type || 'url';
        node.querySelector('.mc-target').value = data.target || '';
        node.querySelector('.mc-remove').addEventListener('click', function () { node.remove(); });
        li.querySelector('.menu-children').appendChild(node);
    }

    function createItem(data) {
        var node = tpl.content.firstElementChild.cloneNode(true);
        data = data || {};
        node.querySelector('.mi-label').value = data.label || '';
        node.querySelector('.mi-label-en').value = data.label_en || '';
        node.querySelector('.mi-type').value = data.type || 'route';
        node.querySelector('.mi-placement').value = data.placement || 'top';
        node.querySelector('.mi-active').checked = data.is_active !== false;
        var target = data.target || '';
        node.querySelector('.mi-target-route').value = target || 'home';
        node.querySelector('.mi-target-category').value = target;
        node.querySelector('.mi-target-product').value = target;
        node.querySelector('.mi-target-url').value = target;
        node.querySelector('.mi-target-page').value = target;
        syncTargetVisibility(node);
        node.querySelector('.mi-type').addEventListener('change', function () { syncTargetVisibility(node); });
        node.querySelector('.mi-remove').addEventListener('click', function () { node.remove(); });
        node.querySelector('.mi-add-child').addEventListener('click', function () { addChild(node, { type: 'url' }); });
        (data.children || []).forEach(function (c) { addChild(node, c); });
        return node;
    }

    function fill(list, placement) {
        initial.filter(function (i) { return (i.placement || 'drawer') === placement; })
            .forEach(function (i) {
                var el = createItem(Object.assign({}, i, { placement: placement }));
                list.appendChild(el);
            });
    }

    fill(listTop, 'top');
    fill(listDrawer, 'drawer');

    [listTop, listDrawer].forEach(function (el) {
        Sortable.create(el, {
            group: 'site-menu',
            animation: 150,
            handle: '.menu-handle',
            ghostClass: 'sortable-ghost',
            onAdd: function (evt) {
                evt.item.querySelector('.mi-placement').value = el.getAttribute('data-placement');
            }
        });
    });

    document.querySelectorAll('[data-add]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var placement = btn.getAttribute('data-add');
            lastPlacement = placement;
            var list = placement === 'top' ? listTop : listDrawer;
            list.appendChild(createItem({ label: 'Yeni öğe', type: 'route', target: 'home', placement: placement, is_active: true }));
        });
    });

    document.querySelectorAll('[data-source]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var list = lastPlacement === 'drawer' ? listDrawer : listTop;
            list.appendChild(createItem({
                label: btn.getAttribute('data-label') || 'Öğe',
                type: btn.getAttribute('data-source'),
                target: btn.getAttribute('data-target') || '',
                placement: lastPlacement,
                is_active: true
            }));
        });
    });

    document.getElementById('btn-reset-defaults').addEventListener('click', function () {
        if (confirm('Menü varsayılanlara sıfırlansın mı?')) {
            document.getElementById('reset-form').submit();
        }
    });

    function readTarget(li, type) {
        if (type === 'route') return li.querySelector('.mi-target-route').value;
        if (type === 'category') return li.querySelector('.mi-target-category').value;
        if (type === 'product') return li.querySelector('.mi-target-product').value;
        if (type === 'url') return li.querySelector('.mi-target-url').value;
        if (type === 'page') return li.querySelector('.mi-target-page').value;
        return '';
    }

    document.getElementById('menu-form').addEventListener('submit', function (e) {
        this.querySelectorAll('input[name^="items["]').forEach(function (n) { n.remove(); });

        var idx = 0;
        [listTop, listDrawer].forEach(function (list) {
            list.querySelectorAll(':scope > .menu-item').forEach(function (li) {
                var type = li.querySelector('.mi-type').value;
                var label = li.querySelector('.mi-label').value.trim();
                var labelEn = li.querySelector('.mi-label-en').value.trim();
                var placement = list.getAttribute('data-placement');
                var active = li.querySelector('.mi-active').checked;
                var target = readTarget(li, type);

                function add(name, val) {
                    var inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'items[' + idx + '][' + name + ']';
                    inp.value = val;
                    e.target.appendChild(inp);
                }
                add('label', label);
                add('label_en', labelEn);
                add('type', type);
                add('target', target);
                add('placement', placement);
                add('is_active', active ? '1' : '0');

                var cidx = 0;
                li.querySelectorAll('.menu-child').forEach(function (ch) {
                    var clabel = ch.querySelector('.mc-label').value.trim();
                    if (!clabel) return;
                    function addc(name, val) {
                        var inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'items[' + idx + '][children][' + cidx + '][' + name + ']';
                        inp.value = val;
                        e.target.appendChild(inp);
                    }
                    addc('label', clabel);
                    addc('type', ch.querySelector('.mc-type').value);
                    addc('target', ch.querySelector('.mc-target').value);
                    cidx++;
                });
                idx++;
            });
        });
    });
})();
</script>
@endsection
