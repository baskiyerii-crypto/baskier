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
    $showTrSelects = $showTrSelects ?? true;
    $inputClass = $inputClass ?? 'form-control';
    $selectClass = $selectClass ?? 'form-select';
    $isTr = strtoupper((string) $countryValue) === 'TR' || $countryValue === '';
@endphp
<div data-geo-root data-id-prefix="{{ $idPrefix }}">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="{{ $idPrefix }}-country">{{ __('panel.country') }}</label>
            <select name="{{ $nameCountry }}" id="{{ $idPrefix }}-country" class="{{ $selectClass }}" data-geo-country>
                @if($emptyCountry)
                    <option value="">{{ __('panel.all_countries') }}</option>
                @endif
                @foreach($countries as $c)
                    <option value="{{ $c->code }}" @selected(strtoupper((string) $countryValue) === $c->code)>{{ $c->localizedName() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="{{ $idPrefix }}-city">{{ __('panel.city') }}</label>
            <input name="{{ $nameCity }}" id="{{ $idPrefix }}-city" class="{{ $inputClass }}" value="{{ $cityValue }}" list="{{ $idPrefix }}-cities" data-geo-city autocomplete="off" placeholder="{{ __('panel.city_placeholder') }}">
            <datalist id="{{ $idPrefix }}-cities">
                @foreach($citySuggestions as $suggestion)
                    <option value="{{ $suggestion }}"></option>
                @endforeach
            </datalist>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="{{ $idPrefix }}-district">{{ __('panel.district') }}</label>
            <input name="{{ $nameDistrict }}" id="{{ $idPrefix }}-district" class="{{ $inputClass }}" value="{{ $districtValue }}" list="{{ $idPrefix }}-districts" data-geo-district autocomplete="off" placeholder="{{ __('panel.district_placeholder') }}">
            <datalist id="{{ $idPrefix }}-districts">
                @foreach($districtSuggestions as $suggestion)
                    <option value="{{ $suggestion }}"></option>
                @endforeach
            </datalist>
        </div>
    </div>
    @if($showTrSelects && $provinces->isNotEmpty())
        <div class="row g-3 mt-1" data-geo-tr @if(! $isTr) style="display:none" @endif>
            <div class="col-md-6">
                <label class="form-label" for="{{ $idPrefix }}-il">{{ __('panel.tr_province') }}</label>
                <select name="{{ $nameIl }}" id="{{ $idPrefix }}-il" class="{{ $selectClass }}" data-geo-il>
                    <option value="">{{ __('panel.all_provinces') }}</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p->id }}" @selected((string) $ilValue === (string) $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="{{ $idPrefix }}-ilce">{{ __('panel.tr_district') }}</label>
                <select name="{{ $nameIlce }}" id="{{ $idPrefix }}-ilce" class="{{ $selectClass }}" data-geo-ilce>
                    <option value="">{{ __('panel.all_districts') }}</option>
                    @foreach($trDistricts as $d)
                        <option value="{{ $d->id }}" @selected((string) $ilceValue === (string) $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
</div>
@once
@push('scripts')
<script>
(() => {
    const fillList = (list, rows) => {
        if (!list) return;
        list.innerHTML = '';
        (rows || []).forEach((name) => {
            const opt = document.createElement('option');
            opt.value = name;
            list.appendChild(opt);
        });
    };
    const init = (root) => {
        const country = root.querySelector('[data-geo-country]');
        const city = root.querySelector('[data-geo-city]');
        const district = root.querySelector('[data-geo-district]');
        const trWrap = root.querySelector('[data-geo-tr]');
        const il = root.querySelector('[data-geo-il]');
        const ilce = root.querySelector('[data-geo-ilce]');
        const cityList = root.querySelector('datalist[id$="-cities"]');
        const districtList = root.querySelector('datalist[id$="-districts"]');
        const toggleTr = () => {
            if (!trWrap) return;
            trWrap.style.display = !country || country.value === '' || country.value === 'TR' ? '' : 'none';
        };
        const loadPlaces = async () => {
            if (!country || !country.value) {
                fillList(cityList, []);
                fillList(districtList, []);
                return;
            }
            const params = new URLSearchParams({ country: country.value });
            if (city && city.value) params.set('city', city.value);
            try {
                const res = await fetch('/api/v1/geography/places?' + params.toString());
                const json = await res.json();
                const data = json.data || {};
                fillList(cityList, data.cities || []);
                fillList(districtList, data.districts || []);
            } catch (e) {}
        };
        const loadIlce = async () => {
            if (!il || !ilce) return;
            const selected = ilce.value;
            ilce.innerHTML = '<option value="">{{ __('panel.all_districts') }}</option>';
            if (!il.value) return;
            try {
                const res = await fetch('/api/v1/geography/provinces/' + il.value + '/districts');
                const json = await res.json();
                (json.data || []).forEach((d) => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.name;
                    if (String(d.id) === String(selected)) opt.selected = true;
                    ilce.appendChild(opt);
                });
            } catch (e) {}
        };
        country && country.addEventListener('change', () => { toggleTr(); loadPlaces(); });
        city && city.addEventListener('change', loadPlaces);
        il && il.addEventListener('change', () => {
            loadIlce();
            if (il.value && city) {
                const opt = il.options[il.selectedIndex];
                if (opt && opt.textContent) city.value = opt.textContent;
            }
        });
        ilce && ilce.addEventListener('change', () => {
            if (ilce.value && district) {
                const opt = ilce.options[ilce.selectedIndex];
                if (opt && opt.textContent) district.value = opt.textContent;
            }
        });
        toggleTr();
    };
    document.querySelectorAll('[data-geo-root]').forEach(init);
})();
</script>
@endpush
@endonce
