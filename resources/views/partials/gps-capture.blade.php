@php
    $gpsMode = $gpsMode ?? 'button';
    $gpsUseOld = $gpsUseOld ?? true;
    $gpsLat = $gpsUseOld ? old('lat', $gpsLat ?? '') : ($gpsLat ?? '');
    $gpsLng = $gpsUseOld ? old('lng', $gpsLng ?? '') : ($gpsLng ?? '');
    $gpsButton = $gpsButton ?? 'Konumu al';
    $gpsHint = $gpsHint ?? 'Panonun yanında durun; konum telefon GPS’inden alınır.';
    $gpsHasCoords = $gpsLat !== '' && $gpsLng !== '';
    $gpsLatF = $gpsHasCoords ? (float) $gpsLat : null;
    $gpsLngF = $gpsHasCoords ? (float) $gpsLng : null;
    $gpsDelta = 0.008;
    $gpsBbox = $gpsHasCoords
        ? ($gpsLngF - $gpsDelta).','.($gpsLatF - $gpsDelta).','.($gpsLngF + $gpsDelta).','.($gpsLatF + $gpsDelta)
        : null;
@endphp
<div class="mb-3" data-gps-capture data-gps-mode="{{ $gpsMode }}">
    <input type="hidden" name="lat" value="{{ $gpsLat }}" data-gps-lat>
    <input type="hidden" name="lng" value="{{ $gpsLng }}" data-gps-lng>
    <label class="form-label fw-semibold small">Gerçek konum (GPS)</label>
    <p class="small text-muted mb-2">{{ $gpsHint }}</p>
    <button type="button" class="btn btn-outline-primary btn-sm" data-gps-btn>{{ $gpsButton }}</button>
    <p class="small mt-2 mb-0 {{ $gpsHasCoords ? 'text-success' : 'text-muted' }}" data-gps-status>
        @if($gpsHasCoords)
            Kayıtlı konum: {{ number_format($gpsLatF, 5, '.', '') }}, {{ number_format($gpsLngF, 5, '.', '') }}
        @else
            Henüz konum alınmadı.
        @endif
    </p>
    <div class="mt-2 overflow-hidden rounded border {{ $gpsHasCoords ? '' : 'd-none' }}" data-gps-map-wrap>
        <iframe
            title="Pano konumu"
            class="w-100"
            style="height:220px;border:0;"
            data-gps-map
            @if($gpsHasCoords)
                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $gpsBbox }}&amp;layer=mapnik&amp;marker={{ $gpsLatF }}%2C{{ $gpsLngF }}"
            @endif
        ></iframe>
    </div>
</div>
@once
@push('scripts')
<script>
(() => {
    const errorMessage = (code) => {
        if (code === 1) {
            return 'Konum izni kapalı. Tarayıcıdan izin verin; telefonla panonun yanında durun.';
        }
        if (code === 3) {
            return 'Konum zaman aşımına uğradı. Açık alanda tekrar deneyin.';
        }
        return 'Konum alınamadı. GPS açık olsun ve tekrar deneyin.';
    };

    const showMap = (wrap, iframe, lat, lng) => {
        if (!iframe || !wrap) return;
        const d = 0.008;
        const bbox = (lng - d) + ',' + (lat - d) + ',' + (lng + d) + ',' + (lat + d);
        iframe.src = 'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox
            + '&layer=mapnik&marker=' + lat + '%2C' + lng;
        wrap.classList.remove('d-none');
    };

    const fillGeoFromReverse = (gpsRoot, lat, lng) => {
        const form = gpsRoot.closest('form');
        if (!form) return;
        const geoRoot = form.querySelector('[data-geo-root]');
        if (!geoRoot) return;
        fetch('/api/v1/geography/reverse?lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng))
            .then((r) => r.json())
            .then((json) => {
                const data = json.data || {};
                const country = geoRoot.querySelector('[data-geo-country]');
                const city = geoRoot.querySelector('[data-geo-city]');
                const district = geoRoot.querySelector('[data-geo-district]');
                const il = geoRoot.querySelector('[data-geo-il]');
                const ilce = geoRoot.querySelector('[data-geo-ilce]');
                if (!data.country_code || !country) return;
                const applyPlaces = () => {
                    const keepCity = data.city || '';
                    const keepDistrict = data.district || '';
                    const keepIl = data.turkiye_il_id ? String(data.turkiye_il_id) : '';
                    const keepIlce = data.turkiye_ilce_id ? String(data.turkiye_ilce_id) : '';
                    return fetch('/api/v1/geography/places?country=' + encodeURIComponent(data.country_code))
                        .then((r) => r.json())
                        .then((placeJson) => {
                            const cities = placeJson.data?.cities || [];
                            if (city) {
                                city.innerHTML = '';
                                const first = document.createElement('option');
                                first.value = '';
                                first.textContent = geoRoot.getAttribute('data-empty-country') === '1' ? 'Tüm şehirler' : 'Şehir seçin';
                                city.appendChild(first);
                                let matched = false;
                                cities.forEach((row) => {
                                    const name = row.name || row;
                                    const id = row.id || '';
                                    const opt = document.createElement('option');
                                    opt.value = name;
                                    opt.textContent = name;
                                    if (id) opt.dataset.id = id;
                                    if ((keepCity && name === keepCity) || (keepIl && String(id) === keepIl)) {
                                        opt.selected = true;
                                        matched = true;
                                    }
                                    city.appendChild(opt);
                                });
                                if (!matched && keepCity) {
                                    const opt = document.createElement('option');
                                    opt.value = keepCity;
                                    opt.textContent = keepCity;
                                    if (keepIl) opt.dataset.id = keepIl;
                                    opt.selected = true;
                                    city.appendChild(opt);
                                }
                                city.disabled = false;
                            }
                            if (il) il.value = keepIl || (city?.options[city.selectedIndex]?.dataset?.id || '');
                            const cityName = city ? city.value : keepCity;
                            if (!cityName) return;
                            const params = new URLSearchParams({ country: data.country_code, city: cityName });
                            if (il && il.value) params.set('province_id', il.value);
                            return fetch('/api/v1/geography/places?' + params.toString())
                                .then((r) => r.json())
                                .then((distJson) => {
                                    const districts = distJson.data?.districts || [];
                                    if (!district) return;
                                    district.innerHTML = '';
                                    const first = document.createElement('option');
                                    first.value = '';
                                    first.textContent = geoRoot.getAttribute('data-empty-country') === '1' ? 'Tüm ilçeler' : 'İlçe seçin';
                                    district.appendChild(first);
                                    let matched = false;
                                    districts.forEach((row) => {
                                        const name = row.name || row;
                                        const id = row.id || '';
                                        const opt = document.createElement('option');
                                        opt.value = name;
                                        opt.textContent = name;
                                        if (id) opt.dataset.id = id;
                                        if ((keepDistrict && name === keepDistrict) || (keepIlce && String(id) === keepIlce)) {
                                            opt.selected = true;
                                            matched = true;
                                        }
                                        district.appendChild(opt);
                                    });
                                    if (!matched && keepDistrict) {
                                        const opt = document.createElement('option');
                                        opt.value = keepDistrict;
                                        opt.textContent = keepDistrict;
                                        if (keepIlce) opt.dataset.id = keepIlce;
                                        opt.selected = true;
                                        district.appendChild(opt);
                                    }
                                    district.disabled = false;
                                    if (ilce) ilce.value = keepIlce || (district.options[district.selectedIndex]?.dataset?.id || '');
                                });
                        });
                };
                country.value = data.country_code;
                applyPlaces();
            })
            .catch(() => {});
    };

    const capture = (root) => new Promise((resolve, reject) => {
        const latInput = root.querySelector('[data-gps-lat]');
        const lngInput = root.querySelector('[data-gps-lng]');
        const status = root.querySelector('[data-gps-status]');
        const btn = root.querySelector('[data-gps-btn]');
        const setStatus = (msg, ok) => {
            if (!status) return;
            status.textContent = msg;
            status.classList.remove('text-muted', 'text-success', 'text-danger');
            status.classList.add(ok ? 'text-success' : 'text-danger');
        };

        if (!navigator.geolocation) {
            setStatus('Bu cihazda konum alınamıyor. Telefon tarayıcısı kullanın.', false);
            reject(new Error('unsupported'));
            return;
        }

        if (btn) btn.disabled = true;
        setStatus('Konum alınıyor… panonun yanındaysanız bekleyin.', true);

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                if (latInput) latInput.value = lat.toFixed(7);
                if (lngInput) lngInput.value = lng.toFixed(7);
                const acc = Math.round(pos.coords.accuracy || 0);
                setStatus('Konum alındı' + (acc ? ' (±' + acc + ' m)' : '') + '.', true);
                showMap(root.querySelector('[data-gps-map-wrap]'), root.querySelector('[data-gps-map]'), lat, lng);
                if (btn) btn.disabled = false;
                fillGeoFromReverse(root, lat, lng);
                resolve({ lat, lng });
            },
            (err) => {
                setStatus(errorMessage(err.code), false);
                if (btn) btn.disabled = false;
                reject(err);
            },
            { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
        );
    });

    const init = (root) => {
        const form = root.closest('form');
        const mode = root.getAttribute('data-gps-mode') || 'button';
        const btn = root.querySelector('[data-gps-btn]');
        const latInput = root.querySelector('[data-gps-lat]');
        const lngInput = root.querySelector('[data-gps-lng]');

        btn && btn.addEventListener('click', () => {
            capture(root).catch(() => {});
        });

        if (!form) return;

        let locking = false;
        form.addEventListener('submit', (e) => {
            const hasCoords = !!(latInput && lngInput && latInput.value && lngInput.value);
            const mustCapture = mode === 'submit' || !hasCoords;
            if (!mustCapture) return;
            e.preventDefault();
            if (locking) return;
            locking = true;
            capture(root).then(() => {
                HTMLFormElement.prototype.submit.call(form);
            }).catch(() => {
                locking = false;
            });
        });
    };

    document.querySelectorAll('[data-gps-capture]').forEach(init);
})();
</script>
@endpush
@endonce
