{{-- Searchable multi-checkbox filter --}}
@php
    $name = $name ?? 'ids[]';
    $options = $options ?? collect();
    $selected = collect(old(str_replace('[]', '', $name), $selected ?? []))->map(fn ($v) => (string) $v);
    $uid = 'msf-'.md5($name.uniqid('', true));
@endphp
<div class="border rounded-3 p-2 bg-white" data-multi-search-filter id="{{ $uid }}">
    <input type="search" class="form-control form-control-sm mb-2" placeholder="{{ $placeholder ?? __('panel.search') }}" data-msf-q>
    <div class="overflow-auto" style="max-height: {{ $maxHeight ?? '220px' }};" data-msf-list>
        @foreach($options as $opt)
            @php
                $oid = (string) data_get($opt, $valueKey ?? 'id');
                $olabel = data_get($opt, $labelKey ?? 'name');
            @endphp
            <label class="d-flex align-items-center gap-2 px-2 py-1 rounded small msf-item" data-msf-label="{{ Str::lower($olabel) }}">
                <input type="checkbox" class="form-check-input" name="{{ $name }}" value="{{ $oid }}" @checked($selected->contains($oid))>
                <span>{{ $olabel }}</span>
            </label>
        @endforeach
        @if($options->isEmpty())
            <div class="text-muted small px-2">{{ __('panel.no_results') }}</div>
        @endif
    </div>
</div>
@once
@push('scripts')
<script>
document.querySelectorAll('[data-multi-search-filter]').forEach((root) => {
  const q = root.querySelector('[data-msf-q]');
  const items = root.querySelectorAll('.msf-item');
  q?.addEventListener('input', () => {
    const term = (q.value || '').toLowerCase().trim();
    items.forEach((el) => {
      const label = el.getAttribute('data-msf-label') || '';
      el.style.display = !term || label.includes(term) ? '' : 'none';
    });
  });
});
</script>
@endpush
@endonce
