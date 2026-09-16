@extends('layouts.admin')
@section('title', __('panel.nav_menu'))
@section('content')
<style>
    .menu-builder { display: grid; gap: 1.25rem; }
    @media (min-width: 992px) {
        .menu-builder { grid-template-columns: 1fr 1fr; }
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
</style>

<div style="max-width:1100px;">
    <p class="text-muted small mb-3">Üst menü ve yan (drawer) menüyü sürükleyerek sıralayın. Öğeleri ekleyin, düzenleyin veya silin.</p>

    <form method="post" action="{{ route('admin.menu.update') }}" id="menu-form">
        @csrf
        <div class="menu-builder">
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
                <input type="text" class="form-control mi-target-url d-none" placeholder="https://…">
                <input type="text" class="form-control mi-target-page d-none" placeholder="slug (örn. contact)">
                <span class="type-hint mi-target-none d-none">Hedef gerekmez</span>
            </div>
        </div>
        <input type="hidden" class="mi-placement" value="top">
    </li>
</template>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    var initial = @json($items);
    var tpl = document.getElementById('menu-item-tpl');
    var listTop = document.getElementById('list-top');
    var listDrawer = document.getElementById('list-drawer');

    function syncTargetVisibility(li) {
        var type = li.querySelector('.mi-type').value;
        var route = li.querySelector('.mi-target-route');
        var cat = li.querySelector('.mi-target-category');
        var url = li.querySelector('.mi-target-url');
        var page = li.querySelector('.mi-target-page');
        var none = li.querySelector('.mi-target-none');
        [route, cat, url, page, none].forEach(function (el) { el.classList.add('d-none'); });
        if (type === 'route') route.classList.remove('d-none');
        else if (type === 'category') cat.classList.remove('d-none');
        else if (type === 'url') url.classList.remove('d-none');
        else if (type === 'page') page.classList.remove('d-none');
        else none.classList.remove('d-none');
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
        node.querySelector('.mi-target-url').value = target;
        node.querySelector('.mi-target-page').value = target;
        syncTargetVisibility(node);
        node.querySelector('.mi-type').addEventListener('change', function () { syncTargetVisibility(node); });
        node.querySelector('.mi-remove').addEventListener('click', function () { node.remove(); });
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
            var list = placement === 'top' ? listTop : listDrawer;
            list.appendChild(createItem({ label: 'Yeni öğe', type: 'route', target: 'home', placement: placement, is_active: true }));
        });
    });

    document.getElementById('btn-reset-defaults').addEventListener('click', function () {
        if (confirm('Menü varsayılanlara sıfırlansın mı?')) {
            document.getElementById('reset-form').submit();
        }
    });

    document.getElementById('menu-form').addEventListener('submit', function (e) {
        // Clear any previous dynamic inputs
        this.querySelectorAll('input[name^="items["]').forEach(function (n) { n.remove(); });

        var idx = 0;
        [listTop, listDrawer].forEach(function (list) {
            list.querySelectorAll('.menu-item').forEach(function (li) {
                var type = li.querySelector('.mi-type').value;
                var label = li.querySelector('.mi-label').value.trim();
                var labelEn = li.querySelector('.mi-label-en').value.trim();
                var placement = list.getAttribute('data-placement');
                var active = li.querySelector('.mi-active').checked;
                var target = '';
                if (type === 'route') target = li.querySelector('.mi-target-route').value;
                else if (type === 'category') target = li.querySelector('.mi-target-category').value;
                else if (type === 'url') target = li.querySelector('.mi-target-url').value;
                else if (type === 'page') target = li.querySelector('.mi-target-page').value;

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
                idx++;
            });
        });
    });
})();
</script>
@endsection
