@php
    $addr = $address ?? null;
    $oldDid = old('turkiye_district_id');
    $initDistrict = $oldDid ?: $addr?->turkiye_district_id;
    $oldNid = old('turkiye_neighborhood_id');
    $initNeighborhood = $oldNid !== null && $oldNid !== '' ? (int) $oldNid : ($addr?->turkiye_neighborhood_id ? (int) $addr->turkiye_neighborhood_id : null);
    $initProvince = null;
    if ($oldDid) {
        $initProvince = \App\Models\TurkiyeIlce::query()->whereKey($oldDid)->value('province_id');
    } elseif ($addr?->turkiye_district_id) {
        $initProvince = $addr->turkiyeIlce?->province_id;
    }
@endphp

<div class="border rounded-3 p-3 mb-3 bg-light">
    <div class="small fw-semibold text-muted mb-2">Konum (yalnızca listeden)</div>
    <div class="row g-2">
        <div class="col-md-6 mb-2">
            <label class="form-label">İl</label>
            <select id="geo-il" class="form-select">
                <option value="">Seçiniz…</option>
            </select>
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label">İlçe</label>
            <select id="geo-ilce" class="form-select" disabled>
                <option value="">Önce il seçin</option>
            </select>
        </div>
    </div>
    <div class="mb-2">
        <label class="form-label">Mahalle</label>
        <select id="geo-mahalle" class="form-select" disabled>
            <option value="">Önce ilçe seçin</option>
        </select>
    </div>
    <div class="row g-2 mb-2">
        <div class="col-md-6">
            <label class="form-label">Cadde</label>
            <input type="text" name="cadde" class="form-control" maxlength="120" value="{{ old('cadde', $addr?->cadde) }}" placeholder="İsteğe bağlı" autocomplete="address-line1">
        </div>
        <div class="col-md-6">
            <label class="form-label">Sokak</label>
            <input type="text" name="sokak" class="form-control" maxlength="120" value="{{ old('sokak', $addr?->sokak) }}" placeholder="İsteğe bağlı" autocomplete="address-line2">
        </div>
    </div>
    <div class="row g-2 mb-2">
        <div class="col-md-6">
            <label class="form-label">Bina No</label>
            <input type="text" name="bina_no" class="form-control" maxlength="60" value="{{ old('bina_no', $addr?->bina_no) }}" placeholder="Örn. 12/A">
        </div>
        <div class="col-md-6">
            <label class="form-label">İç Kapı No</label>
            <input type="text" name="ic_kapi_no" class="form-control" maxlength="60" value="{{ old('ic_kapi_no', $addr?->ic_kapi_no) }}" placeholder="Örn. 5">
        </div>
    </div>
    <p class="small text-muted mb-2">Cadde ve sokak ayrı kolonlarda saklanır (BI / dışa aktarım). Ülke çapında hazır cadde/sokak listesi API’de olmadığı için burada metin girişi kullanılır.</p>
    <div class="mb-2">
        <label class="form-label">Posta kodu</label>
        <div class="input-group">
            <input type="text" id="geo-postal" class="form-control" maxlength="5" inputmode="numeric" pattern="[0-9]{5}" placeholder="5 hane" autocomplete="postal-code">
            <button type="button" class="btn btn-outline-secondary" id="geo-postal-btn" tabindex="-1">İl / ilçe bul</button>
        </div>
        <p class="small text-muted mb-0 mt-1">İlçe seçildiğinde posta kodu otomatik dolar. Posta kodunu yazınca il/ilçe otomatik seçilir.</p>
    </div>
    <div id="geo-ambiguous" class="alert alert-warning py-2 small d-none"></div>
    <input type="hidden" name="turkiye_district_id" id="geo-district-id" value="{{ $initDistrict }}">
    <input type="hidden" name="turkiye_neighborhood_id" id="geo-neighborhood-id" value="{{ $initNeighborhood }}">
</div>

@once
@push('scripts')
<script>
(function () {
    const API = @json(url('/api/v1'));
    const initProvince = @json($initProvince ? (int) $initProvince : null);
    const initDistrict = @json($initDistrict ? (int) $initDistrict : null);
    const initNeighborhood = @json($initNeighborhood ? (int) $initNeighborhood : null);

    const elIl = document.getElementById('geo-il');
    const elIlce = document.getElementById('geo-ilce');
    const elMahalle = document.getElementById('geo-mahalle');
    const elPostal = document.getElementById('geo-postal');
    const elHidden = document.getElementById('geo-district-id');
    const elNh = document.getElementById('geo-neighborhood-id');
    const elAmb = document.getElementById('geo-ambiguous');
    if (!elIl || !elHidden || !elMahalle || !elNh) return;

    async function unwrap(path) {
        const r = await fetch(API + path, { headers: { Accept: 'application/json' } });
        const j = await r.json();
        if (!j.success) throw new Error(j.message || 'İstek başarısız');
        return j.data;
    }

    function setAmbiguous(msg, html) {
        if (!msg) {
            elAmb.classList.add('d-none');
            elAmb.innerHTML = '';
            return;
        }
        elAmb.classList.remove('d-none');
        elAmb.innerHTML = '<div class="fw-semibold mb-1">' + msg + '</div>' + (html || '');
    }

    async function loadProvinces() {
        const rows = await unwrap('/geography/provinces');
        elIl.innerHTML = '<option value="">Seçiniz…</option>';
        rows.forEach(function (p) {
            const o = document.createElement('option');
            o.value = p.id;
            o.textContent = p.name;
            elIl.appendChild(o);
        });
        if (initProvince) {
            elIl.value = String(initProvince);
            await loadDistricts(initProvince, initDistrict, initNeighborhood);
        }
    }

    let pendingRequests = 0;
    function setBusy(isBusy) {
        pendingRequests += isBusy ? 1 : -1;
        if (pendingRequests < 0) pendingRequests = 0;
        const postalBtn = document.getElementById('geo-postal-btn');
        if (postalBtn) postalBtn.disabled = pendingRequests > 0;
    }

    async function loadNeighborhoods(districtId, selectNeighborhoodId) {
        elMahalle.disabled = !districtId;
        if (!districtId) {
            elMahalle.innerHTML = '<option value="">Önce ilçe seçin</option>';
            elNh.value = '';
            return;
        }
        elMahalle.innerHTML = '<option value="">Yükleniyor…</option>';
        try {
            setBusy(true);
            const rows = await unwrap('/geography/districts/' + districtId + '/neighborhoods');
            elMahalle.innerHTML = '<option value="">Mahalle seçiniz…</option>';
            rows.forEach(function (n) {
                const o = document.createElement('option');
                o.value = n.id;
                o.textContent = n.name;
                elMahalle.appendChild(o);
            });
            if (selectNeighborhoodId) {
                elMahalle.value = String(selectNeighborhoodId);
                elNh.value = elMahalle.value ? String(selectNeighborhoodId) : '';
            } else if (rows.length === 1) {
                elMahalle.value = String(rows[0].id);
                elNh.value = String(rows[0].id);
            } else {
                elNh.value = '';
            }
        } catch (e) {
            elMahalle.innerHTML = '<option value="">Mahalleler yüklenemedi</option>';
            if (!selectNeighborhoodId) {
                elNh.value = '';
            }
        } finally {
            setBusy(false);
        }
    }

    async function loadDistricts(provinceId, selectDistrictId, selectNeighborhoodId) {
        elIlce.disabled = !provinceId;
        elIlce.innerHTML = provinceId ? '<option value="">Yükleniyor…</option>' : '<option value="">Önce il seçin</option>';
        if (!provinceId) {
            await loadNeighborhoods(0, null);
            return;
        }
        try {
            setBusy(true);
            const rows = await unwrap('/geography/provinces/' + provinceId + '/districts');
            elIlce.innerHTML = '<option value="">İlçe seçiniz…</option>';
            rows.forEach(function (d) {
                const o = document.createElement('option');
                o.value = d.id;
                o.textContent = d.name;
                o.dataset.postal = d.postal_code;
                elIlce.appendChild(o);
            });
            if (selectDistrictId) {
                elIlce.value = String(selectDistrictId);
                syncFromIlceSelect();
                await loadNeighborhoods(selectDistrictId, selectNeighborhoodId);
            } else {
                elHidden.value = '';
                elPostal.value = '';
                await loadNeighborhoods(0, null);
            }
        } catch (e) {
            elIlce.innerHTML = '<option value="">İlçeler yüklenemedi</option>';
            elIlce.disabled = true;
            elHidden.value = '';
            await loadNeighborhoods(0, null);
        } finally {
            setBusy(false);
        }
    }

    function syncFromIlceSelect() {
        const opt = elIlce.selectedOptions[0];
        if (!opt || !opt.value) {
            elHidden.value = '';
            return;
        }
        elHidden.value = opt.value;
        if (opt.dataset.postal) elPostal.value = opt.dataset.postal;
        setAmbiguous('');
    }

    async function lookupPostal() {
        const code = (elPostal.value || '').replace(/\D/g, '');
        if (code.length !== 5) {
            setAmbiguous('Posta kodu 5 rakam olmalıdır.', '');
            return;
        }
        setAmbiguous('');
        try {
            setBusy(true);
            const data = await unwrap('/geography/postal-lookup?code=' + encodeURIComponent(code));
            const m = data.matches || [];
            const fallback = !!data.fallback;
            if (m.length === 0) {
                setAmbiguous('Bu posta kodu listede bulunamadı.', '');
                return;
            }
            if (m.length === 1) {
                elIl.value = String(m[0].province_id);
                await loadDistricts(m[0].province_id, m[0].district_id, null);
                return;
            }
            let html = '<div class="d-grid gap-1">';
            m.forEach(function (row) {
                html += '<button type="button" class="btn btn-sm btn-outline-dark text-start geo-pick" data-pid="' + row.province_id + '" data-did="' + row.district_id + '">' +
                    row.province_name + ' — ' + row.district_name + '</button>';
            });
            html += '</div>';
            const msg = fallback
                ? 'Bu posta kodu ilçe bazlı listede birebir bulunamadı. İl koduna göre olası ilçeleri gösteriyoruz, lütfen seçin:'
                : 'Bu posta kodu birden fazla ilçe ile eşleşiyor. Birini seçin:';
            setAmbiguous(msg, html);
            elAmb.querySelectorAll('.geo-pick').forEach(function (btn) {
                btn.addEventListener('click', async function () {
                    const pid = parseInt(btn.getAttribute('data-pid'), 10);
                    const did = parseInt(btn.getAttribute('data-did'), 10);
                    elIl.value = String(pid);
                    await loadDistricts(pid, did, null);
                    setAmbiguous('');
                });
            });
        } catch (e) {
            setAmbiguous(e.message || 'Arama başarısız', '');
        } finally {
            setBusy(false);
        }
    }

    elIl.addEventListener('change', function () {
        const v = parseInt(elIl.value, 10) || 0;
        elHidden.value = '';
        elPostal.value = '';
        elNh.value = '';
        loadDistricts(v, null, null);
    });
    elIlce.addEventListener('change', async function () {
        syncFromIlceSelect();
        const did = parseInt(elIlce.value, 10) || 0;
        await loadNeighborhoods(did, null);
    });
    elMahalle.addEventListener('change', function () {
        elNh.value = elMahalle.value || '';
    });
    const postalBtn = document.getElementById('geo-postal-btn');
    if (postalBtn) {
        postalBtn.addEventListener('click', lookupPostal);
    }
    let lookupTimer = null;
    elPostal.addEventListener('input', function () {
        const code = (elPostal.value || '').replace(/\D/g, '');
        if (code.length !== 5) {
            return;
        }
        if (lookupTimer) {
            clearTimeout(lookupTimer);
        }
        lookupTimer = setTimeout(function () {
            lookupPostal();
        }, 250);
    });

    const form = elHidden.closest('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (pendingRequests > 0) {
                e.preventDefault();
                setAmbiguous('Konum bilgileri yükleniyor, lütfen bir an bekleyin.', '');
                return;
            }
            if (!elHidden.value) {
                e.preventDefault();
                setAmbiguous('İl ve ilçe seçimi zorunludur.', '');
                return;
            }
            if (!elNh.value) {
                e.preventDefault();
                setAmbiguous('Mahalle seçimi zorunludur.', '');
            }
        });
    }

    loadProvinces().catch(function () {
        elIl.innerHTML = '<option value="">İller yüklenemedi (ağ / veri).</option>';
    });
})();
</script>
@endpush
@endonce
