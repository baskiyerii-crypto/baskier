@props([
    'id',
    'title' => null,
    'side' => 'left', // 'left' or 'right'
    'width' => 'max-w-md',
])

@php
    $translateClosed = $side === 'left' ? '-translate-x-full' : 'translate-x-full';
    $sidePosition = $side === 'left' ? 'left-0' : 'right-0';
    $borderSide = $side === 'left' ? 'border-r' : 'border-l';
@endphp

<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 opacity-0 pointer-events-none transition-opacity duration-200 ease-out drawer-container"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}_title"
    tabindex="-1"
    data-drawer
    data-side="{{ $side }}"
>
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-[#182023]/50 backdrop-blur-xs transition-opacity drawer-backdrop" data-drawer-close></div>

    {{-- Panel --}}
    <div class="fixed inset-y-0 {{ $sidePosition }} flex w-full {{ $width }} flex-col bg-white {{ $borderSide }} border-[#DEDAD2] shadow-2xl transition-transform duration-200 ease-out {{ $translateClosed }} drawer-panel">
        <div class="flex items-center justify-between border-b border-[#DEDAD2] px-6 py-4">
            <h3 id="{{ $id }}_title" class="text-base font-bold text-[#182023] tracking-tight">
                {{ $title }}
            </h3>
            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-[#596166] hover:bg-[#F7F5F0] hover:text-[#182023] transition" data-drawer-close aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-4">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="border-t border-[#DEDAD2] px-6 py-4 bg-[#F7F5F0]/50">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
