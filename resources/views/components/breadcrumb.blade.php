@props([
    'items' => [], // array of ['title' => '...', 'url' => '...']
])

@if(!empty($items))
<nav aria-label="Breadcrumb" class="py-2.5">
    <ol class="flex flex-wrap items-center gap-1.5 text-xs text-[#596166]">
        <li>
            <a href="{{ route('home') }}" class="hover:text-[#182023] transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Ana Sayfa</span>
            </a>
        </li>
        @foreach($items as $item)
            <li class="flex items-center gap-1.5" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
            </li>
            @if($loop->last || empty($item['url']))
                <li aria-current="page">
                    <span class="font-semibold text-[#182023] line-clamp-1">{{ $item['title'] }}</span>
                </li>
            @else
                <li>
                    <a href="{{ $item['url'] }}" class="hover:text-[#182023] transition line-clamp-1">
                        {{ $item['title'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
