@props([
    'paginator',
])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Sayfalama" class="flex items-center justify-between border-t border-[#DEDAD2] px-4 py-3 sm:px-6 mt-6">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center rounded-lg border border-[#DEDAD2] bg-slate-50 px-4 py-2 text-sm font-medium text-[#596166]/50 cursor-not-allowed">
                    Önceki
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center rounded-lg border border-[#DEDAD2] bg-white px-4 py-2 text-sm font-medium text-[#182023] hover:bg-[#F7F5F0]">
                    Önceki
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative ml-3 inline-flex items-center rounded-lg border border-[#DEDAD2] bg-white px-4 py-2 text-sm font-medium text-[#182023] hover:bg-[#F7F5F0]">
                    Sonraki
                </a>
            @else
                <span class="relative ml-3 inline-flex items-center rounded-lg border border-[#DEDAD2] bg-slate-50 px-4 py-2 text-sm font-medium text-[#596166]/50 cursor-not-allowed">
                    Sonraki
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#596166]">
                    Toplam <span class="font-bold text-[#182023]">{{ $paginator->total() }}</span> sonuçtan 
                    <span class="font-bold text-[#182023]">{{ $paginator->firstItem() }}</span> - 
                    <span class="font-bold text-[#182023]">{{ $paginator->lastItem() }}</span> arası gösteriliyor
                </p>
            </div>
            <div>
                <ul class="inline-flex -space-x-px rounded-lg border border-[#DEDAD2] bg-white shadow-xs">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li aria-disabled="true" aria-label="Önceki">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-l-lg text-[#596166]/40 cursor-not-allowed" aria-hidden="true">&lsaquo;</span>
                        </li>
                    @else
                        <li>
                            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-9 w-9 items-center justify-center rounded-l-lg text-[#596166] hover:bg-[#F7F5F0] hover:text-[#182023]" aria-label="Önceki">&lsaquo;</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($paginator->links()->elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li aria-disabled="true"><span class="inline-flex h-9 w-9 items-center justify-center text-xs text-[#596166]">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li aria-current="page">
                                        <span class="inline-flex h-9 w-9 items-center justify-center bg-[#C2410C] text-xs font-bold text-white">{{ $page }}</span>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ $url }}" class="inline-flex h-9 w-9 items-center justify-center text-xs font-medium text-[#182023] hover:bg-[#F7F5F0]">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li>
                            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-9 w-9 items-center justify-center rounded-r-lg text-[#596166] hover:bg-[#F7F5F0] hover:text-[#182023]" aria-label="Sonraki">&rsaquo;</a>
                        </li>
                    @else
                        <li aria-disabled="true" aria-label="Sonraki">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-r-lg text-[#596166]/40 cursor-not-allowed" aria-hidden="true">&rsaquo;</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
