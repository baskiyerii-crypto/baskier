@php
    $nameCountry = $nameCountry ?? 'country_code';
    $nameCity = $nameCity ?? 'city';
    $nameDistrict = $nameDistrict ?? 'district';
    $nameIl = $nameIl ?? 'turkiye_il_id';
    $nameIlce = $nameIlce ?? 'turkiye_ilce_id';
    $countryValue = old($nameCountry, $countryValue ?? 'TR');
    $cityValue = old($nameCity, $cityValue ?? '');
    $districtValue = old($nameDistrict, $districtValue ?? '');
    $ilValue = old($nameIl, $ilValue ?? '');
    $ilceValue = old($nameIlce, $ilceValue ?? '');
    $countries = $countries ?? collect();
    $provinces = $provinces ?? collect();
    $trDistricts = $trDistricts ?? collect();
    $citySuggestions = $citySuggestions ?? [];
    $districtSuggestions = $districtSuggestions ?? [];
    $idPrefix = $idPrefix ?? 'geo';
    $emptyCountry = $emptyCountry ?? false;
    $selectClass = $selectClass ?? 'form-select';
    $isTr = strtoupper((string) $countryValue) === 'TR';
    $hasCountry = $countryValue !== '' && strtoupper((string) $countryValue) !== 'ALL';
    $cityEmptyLabel = $emptyCountry ? __('panel.all_cities') : __('panel.select_city');
    $districtEmptyLabel = $emptyCountry ? __('panel.all_districts') : __('panel.select_district');
    $cityRows = [];
    if ($isTr) {
        foreach ($provinces as $p) {
            $cityRows[] = ['id' => $p->id, 'name' => $p->name];
        }
    } else {
        foreach ($citySuggestions as $suggestion) {
            $cityRows[] = ['id' => null, 'name' => $suggestion];
        }
    }
    $districtRows = [];
    if ($isTr) {
        foreach ($trDistricts as $d) {
            $districtRows[] = ['id' => $d->id, 'name' => $d->name];
        }
    } else {
        foreach ($districtSuggestions as $suggestion) {
            $districtRows[] = ['id' => null, 'name' => $suggestion];
        }
    }
@endphp
<div data-geo-root data-id-prefix="{{ $idPrefix }}" data-empty-country="{{ $emptyCountry ? '1' : '0' }}">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="{{ $idPrefix }}-country">{{ __('panel.country') }}</label>
            <select name="{{ $nameCountry }}" id="{{ $idPrefix }}-country" class="{{ $selectClass }}" data-geo-country>
                @if($emptyCountry)
                    <option value="all" @selected($countryValue === '' || strtoupper((string) $countryValue) === 'ALL')>{{ __('panel.all_countries') }}</option>
                @endif
                @foreach($countries as $c)
                    <option value="{{ $c->code }}" @selected(strtoupper((string) $countryValue) === $c->code)>{{ $c->localizedName() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="{{ $idPrefix }}-city">{{ __('panel.city') }}</label>
            <select name="{{ $nameCity }}" id="{{ $idPrefix }}-city" class="{{ $selectClass }}" data-geo-city @disabled(! $hasCountry)>
                <option value="">{{ $cityEmptyLabel }}</option>
                @foreach($cityRows as $row)
                    @php
                        $selectedCity = $cityValue !== '' && $row['name'] === $cityValue;
                        $selectedCity = $selectedCity || ($ilValue !== '' && (string) $row['id'] === (string) $ilValue);
                    @endphp
                    <option value="{{ $row['name'] }}" data-id="{{ $row['id'] }}" @selected($selectedCity)>{{ $row['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="{{ $idPrefix }}-district">{{ __('panel.district') }}</label>
            <select name="{{ $nameDistrict }}" id="{{ $idPrefix }}-district" class="{{ $selectClass }}" data-geo-district @disabled(! $hasCountry)>
                <option value="">{{ $districtEmptyLabel }}</option>
                @foreach($districtRows as $row)
                    @php
                        $selectedDistrict = $districtValue !== '' && $row['name'] === $districtValue;
                        $selectedDistrict = $selectedDistrict || ($ilceValue !== '' && (string) $row['id'] === (string) $ilceValue);
                    @endphp
                    <option value="{{ $row['name'] }}" data-id="{{ $row['id'] }}" @selected($selectedDistrict)>{{ $row['name'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <input type="hidden" name="{{ $nameIl }}" value="{{ $ilValue }}" data-geo-il>
    <input type="hidden" name="{{ $nameIlce }}" value="{{ $ilceValue }}" data-geo-ilce>
</div>
@once
@push('scripts')
<script>
(() => {
    const emptyCity = (root) => root.getAttribute('data-empty-country') === '1'
        ? @json(__('panel.all_cities'))
        : @json(__('panel.select_city'));
    const emptyDistrict = (root) => root.getAttribute('data-empty-country') === '1'
        ? @json(__('panel.all_districts'))
        : @json(__('panel.select_district'));
    const fillSelect = (select, rows, emptyLabel, selectedName, selectedId) => {
        if (!select) return;
        select.innerHTML = '';
        const first = document.createElement('option');
        first.value = '';
        first.textContent = emptyLabel;
        select.appendChild(first);
        (rows || []).forEach((row) => {
            const name = row.name || row;
            const id = row.id || '';
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            if (id) opt.dataset.id = id;
            if ((selectedName && name === selectedName) || (selectedId && String(id) === String(selectedId))) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    };
    const selectedId = (select) => select?.options[select.selectedIndex]?.dataset?.id || '';
    const init = (root) => {
        const country = root.querySelector('[data-geo-country]');
        const city = root.querySelector('[data-geo-city]');
        const district = root.querySelector('[data-geo-district]');
        const il = root.querySelector('[data-geo-il]');
        const ilce = root.querySelector('[data-geo-ilce]');
        const syncHidden = () => {
            if (il) il.value = country && country.value === 'TR' ? selectedId(city) : '';
            if (ilce) ilce.value = country && country.value === 'TR' ? selectedId(district) : '';
        };
        const setEnabled = () => {
            const on = !!(country && country.value && country.value !== 'all');
            if (city) city.disabled = !on;
            if (district) district.disabled = !on;
        };
        const loadCities = async () => {
            const keepCity = city ? city.value : '';
            const keepIl = il ? il.value : '';
            fillSelect(city, [], emptyCity(root), '', '');
            fillSelect(district, [], emptyDistrict(root), '', '');
            if (!country || !country.value || country.value === 'all') {
                syncHidden();
                setEnabled();
                return;
            }
            try {
                const res = await fetch('/api/v1/geography/places?country=' + encodeURIComponent(country.value));
                const json = await res.json();
                fillSelect(city, json.data?.cities || [], emptyCity(root), keepCity, keepIl);
            } catch (e) {}
            setEnabled();
            syncHidden();
            await loadDistricts();
        };
        const loadDistricts = async () => {
            const keepDistrict = district ? district.value : '';
            const keepIlce = ilce ? ilce.value : '';
            fillSelect(district, [], emptyDistrict(root), '', '');
            if (!country || !country.value || !city || !city.value) {
                syncHidden();
                return;
            }
            const params = new URLSearchParams({ country: country.value, city: city.value });
            const pid = selectedId(city);
            if (pid) params.set('province_id', pid);
            try {
                const res = await fetch('/api/v1/geography/places?' + params.toString());
                const json = await res.json();
                fillSelect(district, json.data?.districts || [], emptyDistrict(root), keepDistrict, keepIlce);
            } catch (e) {}
            syncHidden();
        };
        country && country.addEventListener('change', () => { loadCities(); });
        city && city.addEventListener('change', () => { loadDistricts(); });
        district && district.addEventListener('change', syncHidden);
        setEnabled();
        syncHidden();
    };
    document.querySelectorAll('[data-geo-root]').forEach(init);
})();
</script>
@endpush
@endonce
